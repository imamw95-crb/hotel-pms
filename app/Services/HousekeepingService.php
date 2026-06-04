<?php

namespace App\Services;

use App\Models\HousekeepingTask;
use App\Models\HousekeepingTaskChecklist;
use App\Models\HousekeepingTaskLog;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * HousekeepingService — Business logic for the housekeeping module.
 *
 * Handles:
 * - Dashboard data aggregation
 * - Task CRUD with side effects (auto-log, checklist creation)
 * - Auto-assignment logic (balanced workload)
 * - Staff workload analysis
 * - Overdue / urgent detection
 * - Duration tracking
 * - Room cleaning history
 * - Report generation
 */
class HousekeepingService
{
    // ─── Default Checklist Items per Task Type ───────────────────────
    const DEFAULT_CHECKLISTS = [
        'cleaning' => [
            'Ganti seprai & sarung bantal',
            'Bersihkan kamar mandi',
            'Isi ulang amenities (sabun, sampo)',
            'Vacuum / sapu lantai',
            'Bersihkan kaca & cermin',
            'Rapihkan perabotan',
            'Periksa lampu & elektronik',
            'Isi minibar (jika ada)',
        ],
        'deep_clean' => [
            'Cuci tirai & gorden',
            'Bersihkan AC filter',
            'Poles lantai / keramik',
            'Bersihkan sela-sela furnitur',
            'Cuci karpet (jika ada)',
            'Bersihkan kulkas mini',
            'Sanitasi kamar mandi',
            'Periksa kebocoran pipa',
        ],
        'maintenance' => [
            'Identifikasi kerusakan',
            'Dokumentasi kerusakan (foto)',
            'Perbaiki / ganti komponen',
            'Uji coba perbaikan',
            'Bersihkan area kerja',
        ],
        'inspection' => [
            'Periksa kebersihan kamar',
            'Periksa fungsi AC & TV',
            'Periksa lampu & stop kontak',
            'Periksa pintu & kunci',
            'Periksa perlengkapan kamar',
            'Catat temuan inspeksi',
        ],
        'turndown' => [
            'Tidurkan seprai',
            'Siapkan selimut tambahan',
            'Nyalakan lampu malam',
            'Tutup tirai',
            'Siapkan slippers',
            'Tambahkan air minum',
            'Cokelat / welcome snack',
        ],
    ];

    /**
     * Get comprehensive dashboard data.
     */
    public function getDashboardData(array $filters = []): array
    {
        $statusFilter = $filters['status'] ?? 'all';
        $typeFilter = $filters['type'] ?? 'all';
        $priorityFilter = $filters['priority'] ?? 'all';
        $roomFilter = $filters['room_id'] ?? 'all';
        $dateFrom = $filters['date_from'] ?? Carbon::today()->format('Y-m-d');
        $dateTo = $filters['date_to'] ?? Carbon::today()->format('Y-m-d');

        // Tasks
        $tasks = $this->getFilteredTasks($statusFilter, $typeFilter, $priorityFilter, $roomFilter, $dateFrom, $dateTo);

        // Stats
        $stats = $this->getStats();

        // Rooms for dropdown
        $rooms = Room::orderBy('room_number')->get(['id', 'room_number']);

        // Staff users
        $staffUsers = User::where('role', 'housekeeping')->orderBy('name')->get();

        // Dirty rooms
        $dirtyRooms = $this->getDirtyRooms();

        // Staff workload
        $staffWorkload = $this->getStaffWorkload();

        // Overdue tasks
        $overdueTasksCount = HousekeepingTask::overdue()->count();

        // Average duration today
        $avgDuration = HousekeepingTask::where('status', 'completed')
            ->whereDate('completed_at', Carbon::today())
            ->whereNotNull('duration_minutes')
            ->avg('duration_minutes');

        // Completion stats for chart (7 days)
        $chartData = $this->getChartData();

        return compact(
            'tasks', 'stats', 'rooms', 'staffUsers', 'dirtyRooms',
            'staffWorkload', 'overdueTasksCount', 'avgDuration', 'chartData',
            'statusFilter', 'typeFilter', 'priorityFilter', 'roomFilter',
            'dateFrom', 'dateTo'
        );
    }

    /**
     * Get filtered tasks query.
     */
    public function getFilteredTasks(
        string $statusFilter = 'all',
        string $typeFilter = 'all',
        string $priorityFilter = 'all',
        string $roomFilter = 'all',
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): Collection {
        $dateFrom ??= Carbon::today()->format('Y-m-d');
        $dateTo ??= Carbon::today()->format('Y-m-d');

        $query = HousekeepingTask::with([
            'room', 'assignedTo', 'completedBy', 'createdBy', 'checklistItems',
        ]);

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }
        if ($typeFilter !== 'all') {
            $query->where('task_type', $typeFilter);
        }
        if ($priorityFilter !== 'all') {
            $query->where('priority', $priorityFilter);
        }
        if ($roomFilter !== 'all') {
            $query->where('room_id', $roomFilter);
        }
        $query->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo);

        return $query->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'normal' THEN 3 WHEN 'low' THEN 4 ELSE 5 END ASC")
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get real-time stats.
     */
    public function getStats(): array
    {
        return [
            'pending' => HousekeepingTask::where('status', 'pending')->count(),
            'in_progress' => HousekeepingTask::where('status', 'in_progress')->count(),
            'completed' => HousekeepingTask::whereDate('completed_at', Carbon::today())->count(),
            'total' => HousekeepingTask::count(),
            'urgent' => HousekeepingTask::urgent()->count(),
            'overdue' => HousekeepingTask::overdue()->count(),
            'avg_duration_minutes' => (int) HousekeepingTask::where('status', 'completed')
                ->whereDate('completed_at', Carbon::today())
                ->whereNotNull('duration_minutes')
                ->avg('duration_minutes'),
        ];
    }

    /**
     * Get rooms needing cleaning.
     */
    public function getDirtyRooms(): Collection
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        $checkedOutRoomIds = Reservation::whereIn('status', ['checked_out'])
            ->where(function ($q) use ($today, $yesterday) {
                $q->whereDate('check_out', $today)
                    ->orWhereDate('check_out', $yesterday);
            })
            ->pluck('room_id')
            ->unique()
            ->toArray();

        return Room::where(function ($q) use ($checkedOutRoomIds) {
            if (! empty($checkedOutRoomIds)) {
                $q->whereIn('id', $checkedOutRoomIds);
            }
            $q->orWhereIn('status', ['cleaning', 'available']);
        })
            ->whereDoesntHave('housekeepingTasks', function ($q) {
                $q->whereIn('status', ['pending', 'in_progress'])
                    ->where('task_type', 'cleaning');
            })
            ->orderBy('room_number')
            ->get();
    }

    /**
     * Get staff workload — active task counts per staff.
     */
    public function getStaffWorkload(): Collection
    {
        return User::where('role', 'housekeeping')
            ->orderBy('name')
            ->get()
            ->map(function ($user) {
                $activeTasks = HousekeepingTask::where('assigned_to', $user->id)
                    ->whereIn('status', ['pending', 'in_progress'])
                    ->count();
                $completedToday = HousekeepingTask::where('assigned_to', $user->id)
                    ->where('status', 'completed')
                    ->whereDate('completed_at', Carbon::today())
                    ->count();

                $user->active_tasks = $activeTasks;
                $user->completed_today = $completedToday;
                $user->workload_percent = min(100, ($activeTasks / 5) * 100); // 5 = capacity

                return $user;
            });
    }

    /**
     * Get chart data for last 7 days.
     */
    public function getChartData(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $data[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('D'),
                'completed' => HousekeepingTask::where('status', 'completed')
                    ->whereDate('completed_at', $date)
                    ->count(),
                'created' => HousekeepingTask::whereDate('created_at', $date)->count(),
            ];
        }

        return $data;
    }

    /**
     * Get task type distribution for pie chart.
     */
    public function getTaskTypeDistribution(): array
    {
        $distribution = [];
        foreach (HousekeepingTask::TASK_TYPES as $key => $label) {
            $distribution[] = [
                'type' => $label,
                'count' => HousekeepingTask::where('task_type', $key)->count(),
            ];
        }

        return $distribution;
    }

    // ─── Task Operations ─────────────────────────────────────────────

    /**
     * Create a new task with default checklists and audit log.
     */
    public function createTask(array $data): HousekeepingTask
    {
        $data['created_by'] ??= Auth::id();
        $data['status'] ??= 'pending';

        $task = DB::transaction(function () use ($data) {
            $task = HousekeepingTask::create($data);

            // Create default checklist items based on task_type
            $this->createDefaultChecklist($task->id, $data['task_type']);

            // Create audit log
            $this->logStatusChange($task->id, null, 'pending', 'Task created');

            // Update room status to cleaning if applicable
            if (in_array($data['task_type'] ?? '', ['cleaning', 'deep_clean'])) {
                $room = Room::find($data['room_id']);
                if ($room && in_array($room->status, ['available', 'cleaning'])) {
                    $room->update(['status' => 'cleaning']);
                }
            }

            return $task;
        });

        return $task->load(['room', 'assignedTo', 'createdBy', 'checklistItems']);
    }

    /**
     * Update task status with audit logging and duration tracking.
     */
    public function updateStatus(HousekeepingTask $task, string $newStatus, ?string $notes = null): HousekeepingTask
    {
        $oldStatus = $task->status;

        DB::transaction(function () use ($task, $newStatus, $oldStatus, $notes) {
            $updateData = ['status' => $newStatus];

            if ($newStatus === 'in_progress') {
                $updateData['started_at'] = now();
                if (! $task->assigned_to) {
                    $updateData['assigned_to'] = Auth::id();
                }
            }

            if ($newStatus === 'completed') {
                $updateData['completed_by'] = Auth::id();
                $updateData['completed_at'] = now();

                // Auto-calculate duration from started_at
                if ($task->started_at) {
                    $updateData['duration_minutes'] = (int) $task->started_at->diffInMinutes(now());
                }
            }

            if ($newStatus === 'cancelled' && $notes) {
                $updateData['notes'] = $notes;
            }

            $task->update($updateData);

            // Update room status based on task type
            if (in_array($newStatus, ['completed', 'cancelled'])) {
                $this->updateRoomStatusAfterTask($task);
            }

            // Create audit log
            $this->logStatusChange($task->id, $oldStatus, $newStatus, $notes);
        });

        return $task->fresh()->load(['room', 'assignedTo', 'completedBy', 'checklistItems']);
    }

    /**
     * Auto-assign task to staff with the lightest workload.
     */
    public function autoAssignTask(HousekeepingTask $task): HousekeepingTask
    {
        $staff = User::where('role', 'housekeeping')
            ->withCount(['assignedTasks' => function ($q) {
                $q->whereIn('status', ['pending', 'in_progress']);
            }])
            ->orderBy('assigned_tasks_count')
            ->first();

        if ($staff) {
            $task->update(['assigned_to' => $staff->id]);
            $this->logStatusChange($task->id, $task->status, $task->status,
                "Auto-assigned to {$staff->name}");
        }

        return $task->fresh()->load(['assignedTo']);
    }

    /**
     * Bulk create cleaning tasks for multiple rooms.
     */
    public function bulkCreateTasks(array $roomIds, string $taskType, string $priority, ?int $assignedTo = null): int
    {
        $created = 0;
        foreach ($roomIds as $roomId) {
            $existing = HousekeepingTask::where('room_id', $roomId)
                ->where('task_type', $taskType)
                ->whereIn('status', ['pending', 'in_progress'])
                ->exists();

            if (! $existing) {
                $this->createTask([
                    'room_id' => $roomId,
                    'task_type' => $taskType,
                    'priority' => $priority,
                    'assigned_to' => $assignedTo,
                ]);
                $created++;
            }
        }

        return $created;
    }

    /**
     * Toggle checklist item checked/unchecked.
     */
    public function toggleChecklist(HousekeepingTaskChecklist $item, bool $isChecked): HousekeepingTaskChecklist
    {
        $item->update([
            'is_checked' => $isChecked,
            'checked_by' => $isChecked ? Auth::id() : null,
            'checked_at' => $isChecked ? now() : null,
        ]);

        return $item->fresh();
    }

    /**
     * Create default checklist items for a task type.
     */
    public function createDefaultChecklist(int $taskId, string $taskType): void
    {
        $items = self::DEFAULT_CHECKLISTS[$taskType] ?? [];

        foreach ($items as $order => $itemName) {
            HousekeepingTaskChecklist::create([
                'housekeeping_task_id' => $taskId,
                'item_name' => $itemName,
                'sort_order' => $order,
            ]);
        }
    }

    // ─── Internal Helpers ────────────────────────────────────────────

    /**
     * Log a status change for audit trail.
     */
    private function logStatusChange(int $taskId, ?string $oldStatus, string $newStatus, ?string $notes = null): void
    {
        HousekeepingTaskLog::create([
            'housekeeping_task_id' => $taskId,
            'user_id' => Auth::id() ?? 1,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'notes' => $notes,
            'created_at' => now(),
        ]);
    }

    /**
     * Update room status based on completed/cancelled task.
     */
    private function updateRoomStatusAfterTask(HousekeepingTask $task): void
    {
        $room = $task->room;
        if (! $room) {
            return;
        }

        if (in_array($task->task_type, ['cleaning', 'deep_clean'])) {
            if (in_array($room->status, ['cleaning', 'available'])) {
                $room->update(['status' => 'available']);
            }
        } elseif ($task->task_type === 'maintenance') {
            if ($room->status === 'maintenance') {
                $room->update(['status' => 'available']);
            }
        }
    }

    /**
     * Get room cleaning history.
     */
    public function getRoomCleaningHistory(int $roomId, int $limit = 20): Collection
    {
        return HousekeepingTask::with(['assignedTo', 'completedBy'])
            ->where('room_id', $roomId)
            ->whereIn('task_type', ['cleaning', 'deep_clean'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Generate report data for date range.
     */
    public function generateReport(string $dateFrom, string $dateTo, array $filters = []): array
    {
        $query = HousekeepingTask::with(['room', 'assignedTo', 'completedBy']);

        $query->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo);

        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['type']) && $filters['type'] !== 'all') {
            $query->where('task_type', $filters['type']);
        }
        if (! empty($filters['priority']) && $filters['priority'] !== 'all') {
            $query->where('priority', $filters['priority']);
        }
        if (! empty($filters['room_id']) && $filters['room_id'] !== 'all') {
            $query->where('room_id', $filters['room_id']);
        }

        $tasks = $query->orderBy('created_at', 'desc')->get();
        $groupedTasks = $tasks->groupBy('status');

        // Staff performance
        $staffPerformance = User::where('role', 'housekeeping')
            ->get()
            ->map(function ($user) use ($dateFrom, $dateTo) {
                $completedTasks = HousekeepingTask::where('assigned_to', $user->id)
                    ->where('status', 'completed')
                    ->whereDate('completed_at', '>=', $dateFrom)
                    ->whereDate('completed_at', '<=', $dateTo);

                $avgDuration = (int) (clone $completedTasks)
                    ->whereNotNull('duration_minutes')
                    ->avg('duration_minutes');

                return [
                    'name' => $user->name,
                    'total_completed' => (clone $completedTasks)->count(),
                    'total_tasks' => HousekeepingTask::where('assigned_to', $user->id)
                        ->whereDate('created_at', '>=', $dateFrom)
                        ->whereDate('created_at', '<=', $dateTo)
                        ->count(),
                    'avg_duration_minutes' => $avgDuration,
                    'avg_duration_label' => $avgDuration > 0
                        ? intdiv($avgDuration, 60).'j '.($avgDuration % 60).'m'
                        : '-',
                ];
            });

        $stats = [
            'total' => $tasks->count(),
            'pending' => $tasks->where('status', 'pending')->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'completed' => $tasks->where('status', 'completed')->count(),
            'cancelled' => $tasks->where('status', 'cancelled')->count(),
            'urgent' => $tasks->where('priority', 'urgent')->whereIn('status', ['pending', 'in_progress'])->count(),
        ];

        // Task type breakdown
        $typeBreakdown = [];
        foreach (HousekeepingTask::TASK_TYPES as $key => $label) {
            $typeTasks = $tasks->where('task_type', $key);
            $typeBreakdown[] = [
                'label' => $label,
                'total' => $typeTasks->count(),
                'completed' => $typeTasks->where('status', 'completed')->count(),
                'avg_duration' => (int) $typeTasks->whereNotNull('duration_minutes')->avg('duration_minutes'),
            ];
        }

        return compact('tasks', 'groupedTasks', 'stats', 'staffPerformance', 'typeBreakdown');
    }
}
