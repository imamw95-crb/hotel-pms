<?php

namespace App\Http\Controllers;

use App\Models\HousekeepingTask;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HousekeepingController extends Controller
{
    /**
     * Display housekeeping dashboard
     */
    public function index(Request $request)
    {
        $statusFilter = $request->input('status', 'all');
        $typeFilter = $request->input('type', 'all');
        $priorityFilter = $request->input('priority', 'all');
        $roomFilter = $request->input('room_id', 'all');
        $dateFrom = $request->input('date_from', Carbon::today()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::today()->format('Y-m-d'));

        // Query tasks
        $tasksQuery = HousekeepingTask::with(['room', 'assignedTo', 'completedBy', 'createdBy']);

        if ($statusFilter !== 'all') {
            $tasksQuery->where('status', $statusFilter);
        }

        if ($typeFilter !== 'all') {
            $tasksQuery->where('task_type', $typeFilter);
        }

        if ($priorityFilter !== 'all') {
            $tasksQuery->where('priority', $priorityFilter);
        }

        if ($roomFilter !== 'all') {
            $tasksQuery->where('room_id', $roomFilter);
        }

        // Date filter based on created_at
        $tasksQuery->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo);

        $tasks = $tasksQuery->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'low') ASC")
            ->orderBy('created_at', 'desc')
            ->get();

        // Stats
        $stats = [
            'pending'     => HousekeepingTask::where('status', 'pending')->count(),
            'in_progress' => HousekeepingTask::where('status', 'in_progress')->count(),
            'completed'   => HousekeepingTask::whereDate('completed_at', Carbon::today())->count(),
            'total'       => HousekeepingTask::count(),
            'urgent'      => HousekeepingTask::where('priority', 'urgent')->whereIn('status', ['pending', 'in_progress'])->count(),
        ];

        // Rooms for filter dropdown
        $rooms = Room::orderBy('room_number')->get(['id', 'room_number']);

        // Staff users for assignment
        $staffUsers = User::whereIn('role', ['admin', 'frontoffice'])->orderBy('name')->get();

        // Rooms needing cleaning:
        // 1. Rooms checked out today or yesterday (via reservation)
        // 2. Rooms with status cleaning/available without active cleaning task
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

        $dirtyRoomsQuery = Room::where(function ($q) use ($checkedOutRoomIds) {
                // Kamar yang baru di-checkout
                if (!empty($checkedOutRoomIds)) {
                    $q->whereIn('id', $checkedOutRoomIds);
                }
                // Kamar dengan status cleaning/available
                $q->orWhereIn('status', ['cleaning', 'available']);
            })
            ->whereDoesntHave('housekeepingTasks', function ($q) {
                $q->whereIn('status', ['pending', 'in_progress'])
                  ->where('task_type', 'cleaning');
            })
            ->orderBy('room_number')
            ->get();

        $dirtyRooms = $dirtyRoomsQuery;

        return view('housekeeping.index', compact(
            'tasks', 'stats', 'rooms', 'staffUsers', 'dirtyRooms',
            'statusFilter', 'typeFilter', 'priorityFilter', 'roomFilter',
            'dateFrom', 'dateTo'
        ));
    }

    /**
     * Store a new housekeeping task
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id'     => 'required|exists:rooms,id',
            'task_type'   => 'required|in:cleaning,deep_clean,maintenance,inspection,turndown',
            'priority'    => 'required|in:low,normal,high,urgent',
            'description' => 'nullable|string|max:1000',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['status'] = 'pending';

        $task = HousekeepingTask::create($validated);

        // If room is available/cleaning, set to cleaning when cleaning task created
        if (in_array($validated['task_type'], ['cleaning', 'deep_clean'])) {
            $room = Room::find($validated['room_id']);
            if ($room && in_array($room->status, ['available', 'cleaning'])) {
                $room->update(['status' => 'cleaning']);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tugas housekeeping berhasil dibuat',
                'task'    => $task->load(['room', 'assignedTo', 'createdBy']),
            ]);
        }

        return redirect()->route('housekeeping.index')->with('success', 'Tugas housekeeping berhasil dibuat');
    }

    /**
     * Update task status (start, complete, cancel)
     */
    public function updateStatus(Request $request, $id)
    {
        $task = HousekeepingTask::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'notes'  => 'nullable|string|max:1000',
        ]);

        $task->status = $validated['status'];

        if ($validated['status'] === 'in_progress') {
            $task->assigned_to = $task->assigned_to ?? Auth::id();
        }

        if ($validated['status'] === 'completed') {
            $task->completed_by = Auth::id();
            $task->completed_at = now();

            // Update room status based on task type
            $room = $task->room;
            if ($room) {
                if (in_array($task->task_type, ['cleaning', 'deep_clean'])) {
                    // Only set to available if room is not occupied
                    if ($room->status === 'cleaning' || $room->status === 'available') {
                        $room->update(['status' => 'available']);
                    }
                } elseif ($task->task_type === 'maintenance') {
                    // After maintenance, set to available if not occupied
                    if ($room->status === 'maintenance') {
                        $room->update(['status' => 'available']);
                    }
                }
            }
        }

        if ($validated['status'] === 'cancelled') {
            $task->notes = $validated['notes'] ?? $task->notes;
        }

        if (!empty($validated['notes'])) {
            $task->notes = $validated['notes'];
        }

        $task->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Status tugas berhasil diperbarui',
                'task'    => $task->fresh()->load(['room', 'assignedTo', 'completedBy']),
            ]);
        }

        return redirect()->route('housekeeping.index')->with('success', 'Status tugas berhasil diperbarui');
    }

    /**
     * Assign task to staff
     */
    public function assign(Request $request, HousekeepingTask $task)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $task->update(['assigned_to' => $validated['assigned_to']]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tugas berhasil ditugaskan',
                'task'    => $task->fresh()->load(['room', 'assignedTo']),
            ]);
        }

        return redirect()->route('housekeeping.index')->with('success', 'Tugas berhasil ditugaskan');
    }

    /**
     * Bulk create cleaning tasks for multiple rooms
     */
    public function bulkCreate(Request $request)
    {
        $validated = $request->validate([
            'room_ids'   => 'required|array|min:1',
            'room_ids.*' => 'exists:rooms,id',
            'task_type'  => 'required|in:cleaning,deep_clean,maintenance,inspection,turndown',
            'priority'   => 'required|in:low,normal,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $created = 0;
        foreach ($validated['room_ids'] as $roomId) {
            // Skip if there's already a pending/in_progress task for this room+type
            $existing = HousekeepingTask::where('room_id', $roomId)
                ->where('task_type', $validated['task_type'])
                ->whereIn('status', ['pending', 'in_progress'])
                ->exists();

            if (!$existing) {
                HousekeepingTask::create([
                    'room_id'     => $roomId,
                    'task_type'   => $validated['task_type'],
                    'priority'    => $validated['priority'],
                    'assigned_to' => $validated['assigned_to'],
                    'created_by'  => Auth::id(),
                    'status'      => 'pending',
                ]);
                $created++;
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "{$created} tugas housekeeping berhasil dibuat",
            ]);
        }

        return redirect()->route('housekeeping.index')->with('success', "{$created} tugas housekeeping berhasil dibuat");
    }

    /**
     * Get tasks for a specific room (AJAX)
     */
    public function roomTasks(Room $room)
    {
        $tasks = HousekeepingTask::with(['assignedTo', 'completedBy', 'createdBy'])
            ->where('room_id', $room->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'tasks'   => $tasks,
        ]);
    }

    /**
     * Get housekeeping stats (AJAX for dashboard)
     */
    public function stats()
    {
        return response()->json([
            'success' => true,
            'stats'   => [
                'pending'     => HousekeepingTask::where('status', 'pending')->count(),
                'in_progress' => HousekeepingTask::where('status', 'in_progress')->count(),
                'completed'   => HousekeepingTask::whereDate('completed_at', Carbon::today())->count(),
                'total'       => HousekeepingTask::count(),
                'urgent'      => HousekeepingTask::where('priority', 'urgent')->whereIn('status', ['pending', 'in_progress'])->count(),
            ],
        ]);
    }

    /**
     * Show task detail
     */
    public function show(HousekeepingTask $task)
    {
        $task->load(['room', 'assignedTo', 'completedBy', 'createdBy']);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'task'    => $task,
            ]);
        }

        return view('housekeeping.show', compact('task'));
    }

    /**
     * Delete a task
     */
    public function destroy(HousekeepingTask $task)
    {
        $task->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tugas housekeeping berhasil dihapus',
            ]);
        }

        return redirect()->route('housekeeping.index')->with('success', 'Tugas housekeeping berhasil dihapus');
    }

    /**
     * Print housekeeping report
     */
    public function printReport(Request $request)
    {
        $statusFilter = $request->input('status', 'all');
        $typeFilter = $request->input('type', 'all');
        $priorityFilter = $request->input('priority', 'all');
        $roomFilter = $request->input('room_id', 'all');
        $dateFrom = $request->input('date_from', Carbon::today()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::today()->format('Y-m-d'));

        $tasksQuery = HousekeepingTask::with(['room', 'assignedTo', 'completedBy', 'createdBy']);

        if ($statusFilter !== 'all') {
            $tasksQuery->where('status', $statusFilter);
        }
        if ($typeFilter !== 'all') {
            $tasksQuery->where('task_type', $typeFilter);
        }
        if ($priorityFilter !== 'all') {
            $tasksQuery->where('priority', $priorityFilter);
        }
        if ($roomFilter !== 'all') {
            $tasksQuery->where('room_id', $roomFilter);
        }

        $tasksQuery->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo);

        $tasks = $tasksQuery->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'low') ASC")
            ->orderBy('created_at', 'desc')
            ->get();

        // Group tasks by status for summary
        $groupedTasks = $tasks->groupBy('status');

        // Stats
        $stats = [
            'total'       => $tasks->count(),
            'pending'     => $tasks->where('status', 'pending')->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'completed'   => $tasks->where('status', 'completed')->count(),
            'cancelled'   => $tasks->where('status', 'cancelled')->count(),
            'urgent'      => $tasks->where('priority', 'urgent')->whereIn('status', ['pending', 'in_progress'])->count(),
        ];

        $hotel = \App\Models\HotelSetting::get();

        return view('housekeeping.print', compact(
            'tasks', 'groupedTasks', 'stats', 'hotel',
            'statusFilter', 'typeFilter', 'priorityFilter', 'roomFilter',
            'dateFrom', 'dateTo'
        ));
    }
}
