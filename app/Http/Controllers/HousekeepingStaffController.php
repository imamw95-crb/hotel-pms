<?php

namespace App\Http\Controllers;

use App\Models\HousekeepingTask;
use App\Models\Reservation;
use App\Models\Room;
use App\Services\HousekeepingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HousekeepingStaffController extends Controller
{
    protected HousekeepingService $hkService;

    public function __construct(HousekeepingService $hkService)
    {
        $this->hkService = $hkService;
    }

    /**
     * Mobile-friendly view for housekeeping staff — shows their assigned tasks.
     */
    public function myTasks(Request $request)
    {
        $staffId = Auth::id();

        $statusFilter = $request->input('status', 'all');

        $tasks = HousekeepingTask::with(['room', 'assignedTo', 'checklistItems'])
            ->where('assigned_to', $staffId)
            ->when($statusFilter !== 'all', function ($q) use ($statusFilter) {
                $q->where('status', $statusFilter);
            })
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'normal' THEN 3 WHEN 'low' THEN 4 ELSE 5 END ASC")
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'pending' => HousekeepingTask::where('assigned_to', $staffId)->where('status', 'pending')->count(),
            'in_progress' => HousekeepingTask::where('assigned_to', $staffId)->where('status', 'in_progress')->count(),
            'completed' => HousekeepingTask::where('assigned_to', $staffId)->where('status', 'completed')
                ->whereDate('completed_at', Carbon::today())->count(),
            'total' => HousekeepingTask::where('assigned_to', $staffId)->count(),
        ];

        return view('housekeeping.my-tasks', compact('tasks', 'stats', 'statusFilter'));
    }

    /**
     * Show rooms that need cleaning (self-assign available).
     */
    public function availableRooms()
    {
        $dirtyRooms = $this->hkService->getDirtyRooms();

        $todayCheckouts = Reservation::with('room')
            ->whereDate('check_out', Carbon::today())
            ->where('status', 'checked_in')
            ->get();

        return view('housekeeping.available-rooms', compact('dirtyRooms', 'todayCheckouts'));
    }

    /**
     * Staff self-assign a task.
     */
    public function selfAssign(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'task_type' => 'required|in:cleaning,deep_clean,maintenance,inspection,turndown',
        ]);

        // Check no existing pending/in_progress task for this room+type
        $existing = HousekeepingTask::where('room_id', $validated['room_id'])
            ->where('task_type', $validated['task_type'])
            ->whereIn('status', ['pending', 'in_progress'])
            ->exists();

        if ($existing) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sudah ada tugas aktif untuk kamar ini',
                ], 409);
            }

            return redirect()->back()->with('error', 'Sudah ada tugas aktif untuk kamar ini');
        }

        $task = $this->hkService->createTask([
            'room_id' => $validated['room_id'],
            'task_type' => $validated['task_type'],
            'priority' => 'normal',
            'assigned_to' => Auth::id(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tugas berhasil diambil',
                'task' => $task,
            ]);
        }

        return redirect()->route('housekeeping.my-tasks')->with('success', 'Tugas berhasil diambil');
    }
}
