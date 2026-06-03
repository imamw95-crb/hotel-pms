<?php

namespace App\Http\Controllers;

use App\Models\RoomType;
use App\Models\RoomTypeDatePrice;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PromoPriceController extends Controller
{
    /**
     * Display a listing of promo prices.
     */
    public function index(Request $request)
    {
        $query = RoomTypeDatePrice::with('roomType')
            ->orderBy('room_type_id')
            ->orderBy('date');

        // Filter by room type
        if ($request->filled('room_type_id')) {
            $query->where('room_type_id', $request->room_type_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        $promoPrices = $query->get()->groupBy(function ($item) {
            return $item->roomType->name ?? 'Tanpa Tipe';
        });

        $roomTypes = RoomType::orderBy('name')->get();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $promoPrices,
            ]);
        }

        return view('admin.promo-prices.index', compact('promoPrices', 'roomTypes'));
    }

    /**
     * Show the form for creating new promo prices.
     */
    public function create()
    {
        $roomTypes = RoomType::orderBy('name')->get();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'view' => view('admin.promo-prices.modal-create', compact('roomTypes'))->render(),
            ]);
        }

        return view('admin.promo-prices.create', compact('roomTypes'));
    }

    /**
     * Store newly created promo prices.
     * Supports batch creation via date range (date_from - date_to).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'date_from' => 'required|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'price' => 'required|numeric|min:0',
            'label' => 'nullable|string|max:255',
        ]);

        $dateFrom = Carbon::parse($validated['date_from']);
        $dateTo = $validated['date_to'] ? Carbon::parse($validated['date_to']) : $dateFrom->copy();

        $created = 0;
        $skipped = 0;
        $current = $dateFrom->copy();

        while ($current->lte($dateTo)) {
            try {
                RoomTypeDatePrice::updateOrCreate(
                    [
                        'room_type_id' => $validated['room_type_id'],
                        'date' => $current->format('Y-m-d'),
                    ],
                    [
                        'price' => $validated['price'],
                        'label' => $validated['label'],
                    ]
                );
                $created++;
            } catch (\Exception $e) {
                $skipped++;
            }
            $current->addDay();
        }

        $message = "Berhasil menetapkan harga promo untuk {$created} tanggal";
        if ($skipped > 0) {
            $message .= ", {$skipped} tanggal dilewati";
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect_url' => route('promo-prices.index'),
                'created' => $created,
                'skipped' => $skipped,
            ]);
        }

        return redirect()->route('promo-prices.index')->with('success', $message);
    }

    /**
     * Show the form for editing a promo price.
     */
    public function edit(RoomTypeDatePrice $roomTypeDatePrice)
    {
        $roomTypes = RoomType::orderBy('name')->get();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'view' => view('admin.promo-prices.modal-edit', compact('roomTypeDatePrice', 'roomTypes'))->render(),
            ]);
        }

        return view('admin.promo-prices.edit', compact('roomTypeDatePrice', 'roomTypes'));
    }

    /**
     * Update the specified promo price.
     */
    public function update(Request $request, RoomTypeDatePrice $roomTypeDatePrice)
    {
        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'date' => 'required|date',
            'price' => 'required|numeric|min:0',
            'label' => 'nullable|string|max:255',
        ]);

        // Check uniqueness: (room_type_id, date) except current entry
        $exists = RoomTypeDatePrice::where('room_type_id', $validated['room_type_id'])
            ->where('date', $validated['date'])
            ->where('id', '!=', $roomTypeDatePrice->id)
            ->exists();

        if ($exists) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Harga promo sudah ada untuk tipe kamar dan tanggal ini.',
                ], 422);
            }

            return back()->withErrors(['date' => 'Harga promo sudah ada untuk tipe kamar dan tanggal ini.'])->withInput();
        }

        $roomTypeDatePrice->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Harga promo berhasil diupdate',
                'redirect_url' => route('promo-prices.index'),
            ]);
        }

        return redirect()->route('promo-prices.index')->with('success', 'Harga promo berhasil diupdate');
    }

    /**
     * Remove the specified promo price.
     */
    public function destroy(RoomTypeDatePrice $roomTypeDatePrice)
    {
        $roomTypeDatePrice->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Harga promo berhasil dihapus',
                'redirect_url' => route('promo-prices.index'),
            ]);
        }

        return redirect()->route('promo-prices.index')->with('success', 'Harga promo berhasil dihapus');
    }
}
