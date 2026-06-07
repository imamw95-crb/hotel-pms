<?php

namespace App\Http\Controllers;

use App\Models\OutOfOrder;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OutOfOrderController extends Controller
{
    /**
     * Display a listing of out-of-order records.
     */
    public function index(Request $request)
    {
        $query = OutOfOrder::with(['room', 'createdBy'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by room
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('start_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('start_date', '<=', $request->date_to);
        }

        $items = $query->paginate(20);
        $rooms = Room::orderBy('room_number')->get(['id', 'room_number']);

        return view('out-of-orders.index', compact('items', 'rooms'));
    }

    /**
     * Show form for creating a new out-of-order record (modal).
     */
    public function create()
    {
        $rooms = Room::orderBy('room_number')->get(['id', 'room_number']);

        if (request()->expectsJson()) {
            $view = view('out-of-orders.create', compact('rooms'))->render();

            return response()->json(['success' => true, 'view' => $view]);
        }

        return view('out-of-orders.create', compact('rooms'));
    }

    /**
     * Store a newly created out-of-order record.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Update room status to out_of_order
        $room = Room::findOrFail($validated['room_id']);
        $room->update(['status' => 'out_of_order']);

        $validated['status'] = OutOfOrder::STATUS_ACTIVE;
        $validated['created_by'] = Auth::id();

        $item = OutOfOrder::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Kamar {$room->room_number} berhasil di-set Out of Order",
                'item' => $item->load(['room', 'createdBy']),
            ]);
        }

        return redirect()->route('out-of-orders.index')
            ->with('success', "Kamar {$room->room_number} berhasil di-set Out of Order");
    }

    /**
     * Show a single out-of-order record.
     */
    public function show(OutOfOrder $outOfOrder)
    {
        $outOfOrder->load(['room', 'createdBy']);

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'item' => $outOfOrder]);
        }

        return view('out-of-orders.show', compact('outOfOrder'));
    }

    /**
     * Show form for editing (modal).
     */
    public function edit(OutOfOrder $outOfOrder)
    {
        $rooms = Room::orderBy('room_number')->get(['id', 'room_number']);

        if (request()->expectsJson()) {
            $view = view('out-of-orders.edit', compact('outOfOrder', 'rooms'))->render();

            return response()->json(['success' => true, 'view' => $view]);
        }

        return view('out-of-orders.edit', compact('outOfOrder', 'rooms'));
    }

    /**
     * Update the specified out-of-order record.
     */
    public function update(Request $request, OutOfOrder $outOfOrder)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|in:active,completed',
        ]);

        $oldRoomId = $outOfOrder->room_id;
        $newRoomId = $validated['room_id'];

        $outOfOrder->update($validated);

        // If room changed and old room has no other active OOO, restore status
        if ($oldRoomId !== $newRoomId) {
            $activeOoo = OutOfOrder::where('room_id', $oldRoomId)
                ->where('status', OutOfOrder::STATUS_ACTIVE)
                ->where('id', '!=', $outOfOrder->id)
                ->exists();

            if (! $activeOoo) {
                Room::where('id', $oldRoomId)
                    ->where('status', 'out_of_order')
                    ->update(['status' => 'available']);
            }
        }

        // If completed, restore room status
        if ($validated['status'] === OutOfOrder::STATUS_COMPLETED) {
            Room::where('id', $newRoomId)
                ->where('status', 'out_of_order')
                ->update(['status' => 'available']);
        }

        // Update room status to out_of_order if still active
        if ($validated['status'] === OutOfOrder::STATUS_ACTIVE) {
            $room = Room::find($newRoomId);
            if ($room && $room->status !== 'out_of_order') {
                $room->update(['status' => 'out_of_order']);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data Out of Order berhasil diperbarui',
            ]);
        }

        return redirect()->route('out-of-orders.index')
            ->with('success', 'Data Out of Order berhasil diperbarui');
    }

    /**
     * Mark an out-of-order record as completed and restore room status.
     */
    public function complete(OutOfOrder $outOfOrder)
    {
        $outOfOrder->update([
            'status' => OutOfOrder::STATUS_COMPLETED,
            'end_date' => $outOfOrder->end_date ?? Carbon::today()->format('Y-m-d'),
        ]);

        // Check if room has other active OOOs
        $activeOoo = OutOfOrder::where('room_id', $outOfOrder->room_id)
            ->where('status', OutOfOrder::STATUS_ACTIVE)
            ->where('id', '!=', $outOfOrder->id)
            ->exists();

        if (! $activeOoo) {
            Room::where('id', $outOfOrder->room_id)
                ->where('status', 'out_of_order')
                ->update(['status' => 'available']);
        }

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Out of Order selesai, status kamar dikembalikan',
            ]);
        }

        return redirect()->route('out-of-orders.index')
            ->with('success', 'Out of Order selesai, status kamar dikembalikan');
    }

    /**
     * Delete the specified out-of-order record and restore room status.
     */
    public function destroy(OutOfOrder $outOfOrder)
    {
        $roomId = $outOfOrder->room_id;
        $outOfOrder->delete();

        // Check if room has other active OOOs
        $activeOoo = OutOfOrder::where('room_id', $roomId)
            ->where('status', OutOfOrder::STATUS_ACTIVE)
            ->exists();

        if (! $activeOoo) {
            Room::where('id', $roomId)
                ->where('status', 'out_of_order')
                ->update(['status' => 'available']);
        }

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data Out of Order berhasil dihapus',
            ]);
        }

        return redirect()->route('out-of-orders.index')
            ->with('success', 'Data Out of Order berhasil dihapus');
    }
}
