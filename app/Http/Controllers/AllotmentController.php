<?php

namespace App\Http\Controllers;

use App\Models\Allotment;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AllotmentController extends Controller
{
    /**
     * Display a listing of allotments.
     */
    public function index(Request $request)
    {
        $query = Allotment::with('roomType')
            ->orderBy('room_type_id')
            ->orderBy('date');

        // Filter by room type
        if ($request->filled('room_type_id')) {
            $query->where('room_type_id', $request->room_type_id);
        }

        // Selalu filter channel API saja
        $query->where('channel', 'api');

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        $allotments = $query->get()->groupBy(function ($item) {
            return $item->roomType->name ?? 'Tanpa Tipe';
        });

        $roomTypes = RoomType::orderBy('name')->get();

        return view('admin.allotments.index', compact('allotments', 'roomTypes'));
    }

    /**
     * Show the form for creating a new allotment.
     */
    public function create()
    {
        $roomTypes = RoomType::orderBy('name')->get();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'view' => view('admin.allotments.modal-create', compact('roomTypes'))->render(),
            ]);
        }

        return view('admin.allotments.create', compact('roomTypes'));
    }

    /**
     * Store a newly created allotment.
     * Supports batch creation via date range (date_from - date_to).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'date_from' => 'required|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'allotment' => 'required|integer|min:0',
        ]);

        $roomType = RoomType::findOrFail($validated['room_type_id']);
        $dateFrom = Carbon::parse($validated['date_from']);
        $dateTo = $validated['date_to'] ? Carbon::parse($validated['date_to']) : $dateFrom->copy();

        $created = 0;
        $updated = 0;
        $current = $dateFrom->copy();

        while ($current->lte($dateTo)) {
            $existing = Allotment::where('room_type_id', $roomType->id)
                ->where('date', $current->format('Y-m-d'))
                ->where('channel', 'api')
                ->first();

            if ($existing) {
                $existing->update(['allotment' => $validated['allotment']]);
                $updated++;
            } else {
                Allotment::create([
                    'room_type_id' => $roomType->id,
                    'date' => $current->format('Y-m-d'),
                    'allotment' => $validated['allotment'],
                    'booked' => 0,
                    'channel' => 'api',
                ]);
                $created++;
            }

            $current->addDay();
        }

        $msg = "Allotment untuk {$roomType->name}";
        if ($created > 0) {
            $msg .= ": {$created} tanggal baru dibuat";
        }
        if ($updated > 0) {
            $msg .= ($created > 0 ? ', ' : ': ') . "{$updated} tanggal diperbarui";
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg . '.',
                'redirect_url' => route('allotments.index'),
            ]);
        }

        return redirect()->route('allotments.index')->with('success', $msg . '.');
    }

    /**
     * Show the form for editing an allotment.
     */
    public function edit(Allotment $allotment)
    {
        $roomTypes = RoomType::orderBy('name')->get();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'view' => view('admin.allotments.modal-edit', compact('allotment', 'roomTypes'))->render(),
            ]);
        }

        return view('admin.allotments.edit', compact('allotment', 'roomTypes'));
    }

    /**
     * Update the specified allotment.
     */
    public function update(Request $request, Allotment $allotment)
    {
        $validated = $request->validate([
            'allotment' => 'required|integer|min:0',
            'booked' => 'nullable|integer|min:0',
        ]);

        $allotment->update([
            'allotment' => $validated['allotment'],
            'booked' => $validated['booked'] ?? $allotment->booked,
            'channel' => 'api',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Allotment berhasil diperbarui.',
                'redirect_url' => route('allotments.index'),
            ]);
        }

        return redirect()->route('allotments.index')->with('success', 'Allotment berhasil diperbarui.');
    }

    /**
     * Remove the specified allotment.
     */
    public function destroy(Request $request, Allotment $allotment)
    {
        $allotment->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Allotment berhasil dihapus.',
                'redirect_url' => route('allotments.index'),
            ]);
        }

        return redirect()->route('allotments.index')->with('success', 'Allotment berhasil dihapus.');
    }
}
