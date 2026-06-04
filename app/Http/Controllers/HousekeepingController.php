<?php

namespace App\Http\Controllers;

use App\Models\HotelSetting;
use App\Models\HousekeepingTask;
use App\Models\HousekeepingTaskChecklist;
use App\Models\Room;
use App\Services\HousekeepingService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HousekeepingController extends Controller
{
    protected HousekeepingService $hkService;

    public function __construct(HousekeepingService $hkService)
    {
        $this->hkService = $hkService;
    }

    /**
     * Display housekeeping dashboard
     */
    public function index(Request $request)
    {
        $data = $this->hkService->getDashboardData($request->all());

        return view('housekeeping.index', $data);
    }

    /**
     * Store a new housekeeping task
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'task_type' => 'required|in:cleaning,deep_clean,maintenance,inspection,turndown',
            'priority' => 'required|in:low,normal,high,urgent',
            'description' => 'nullable|string|max:1000',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $task = $this->hkService->createTask($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tugas housekeeping berhasil dibuat',
                'task' => $task->load(['room', 'assignedTo', 'createdBy', 'checklistItems']),
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
            'notes' => 'nullable|string|max:1000',
        ]);

        $task = $this->hkService->updateStatus($task, $validated['status'], $validated['notes'] ?? null);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Status tugas berhasil diperbarui',
                'task' => $task->load(['room', 'assignedTo', 'completedBy', 'checklistItems']),
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
                'task' => $task->fresh()->load(['room', 'assignedTo']),
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
            'room_ids' => 'required|array|min:1',
            'room_ids.*' => 'exists:rooms,id',
            'task_type' => 'required|in:cleaning,deep_clean,maintenance,inspection,turndown',
            'priority' => 'required|in:low,normal,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $created = $this->hkService->bulkCreateTasks(
            $validated['room_ids'],
            $validated['task_type'],
            $validated['priority'],
            $validated['assigned_to'] ?? null
        );

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
            'tasks' => $tasks,
        ]);
    }

    /**
     * Get housekeeping stats (AJAX for dashboard, auto-polling)
     */
    public function stats()
    {
        return response()->json([
            'success' => true,
            'stats' => $this->hkService->getStats(),
        ]);
    }

    /**
     * Show task detail (with logs, checklist, lost & found)
     */
    public function show(HousekeepingTask $task)
    {
        $task->load([
            'room', 'assignedTo', 'completedBy', 'createdBy',
            'checklistItems.checkedBy', 'logs.user', 'lostFound',
        ]);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'task' => $task,
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
     * Print housekeeping report (with staff performance)
     */
    public function printReport(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::today()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::today()->format('Y-m-d'));

        $report = $this->hkService->generateReport($dateFrom, $dateTo, $request->all());

        $hotel = HotelSetting::get();

        return view('housekeeping.print', array_merge($report, [
            'hotel' => $hotel,
            'statusFilter' => $request->input('status', 'all'),
            'typeFilter' => $request->input('type', 'all'),
            'priorityFilter' => $request->input('priority', 'all'),
            'roomFilter' => $request->input('room_id', 'all'),
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]));
    }

    // ─── New Endpoints ───────────────────────────────────────────────

    /**
     * Toggle checklist item (AJAX)
     */
    public function toggleChecklist(Request $request, HousekeepingTaskChecklist $checklist)
    {
        $validated = $request->validate([
            'is_checked' => 'required|boolean',
        ]);

        $item = $this->hkService->toggleChecklist($checklist, $validated['is_checked']);

        return response()->json([
            'success' => true,
            'message' => $validated['is_checked'] ? 'Item selesai' : 'Item dibatalkan',
            'item' => $item,
        ]);
    }

    /**
     * Auto-assign task to staff with lightest workload (AJAX)
     */
    public function autoAssign(HousekeepingTask $task)
    {
        $task = $this->hkService->autoAssignTask($task);

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil ditugaskan otomatis',
            'task' => $task->load(['assignedTo']),
        ]);
    }

    /**
     * Get housekeeping distribution data (AJAX for chart)
     */
    public function distribution()
    {
        return response()->json([
            'success' => true,
            'data' => $this->hkService->getTaskTypeDistribution(),
        ]);
    }

    /**
     * Get room cleaning history (AJAX)
     */
    public function roomHistory(Room $room)
    {
        $history = $this->hkService->getRoomCleaningHistory($room->id);

        return response()->json([
            'success' => true,
            'room' => $room->room_number,
            'history' => $history,
        ]);
    }
}
