<?php

namespace App\Http\Controllers;

use App\Models\BookingGroupMaster;
use App\Models\BookingGroupMasterPrice;
use App\Models\RoomType;
use Illuminate\Http\Request;

class BookingGroupMasterController extends Controller
{
    /**
     * Daftar master grup beserta master harga per tipe kamar.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $masters = BookingGroupMaster::with(['prices.roomType', 'creator'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy('code')
            ->get();

        return view('admin.booking-group-masters.index', compact('masters', 'search'));
    }

    /**
     * Form input master grup baru + master harga per tipe kamar.
     */
    public function create()
    {
        $roomTypes = RoomType::orderBy('sequence')->orderBy('name')->get();
        $generatedCode = BookingGroupMaster::generateCode();

        return view('admin.booking-group-masters.create', compact('roomTypes', 'generatedCode'));
    }

    /**
     * Simpan master grup + master harga.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:30|unique:booking_group_masters,code',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'prices' => 'nullable|array',
            'prices.*.price_per_night' => 'nullable|numeric|min:0',
            'prices.*.include_breakfast' => 'nullable|boolean',
        ]);

        $master = BookingGroupMaster::create([
            'code' => ($validated['code'] ?? null) ?: BookingGroupMaster::generateCode(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'created_by' => auth()->id(),
        ]);

        $this->syncPrices($master, $request->input('prices', []));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Master grup & master harga berhasil disimpan.',
                'redirect_url' => route('booking-group-masters.index'),
            ]);
        }

        return redirect()->route('booking-group-masters.index')
            ->with('success', 'Master grup & master harga berhasil disimpan.');
    }

    /**
     * Form edit master grup + master harga.
     */
    public function edit(BookingGroupMaster $bookingGroupMaster)
    {
        $roomTypes = RoomType::orderBy('sequence')->orderBy('name')->get();
        $master = $bookingGroupMaster->load('prices');

        return view('admin.booking-group-masters.edit', compact('master', 'roomTypes'));
    }

    /**
     * Update master grup + master harga.
     */
    public function update(Request $request, BookingGroupMaster $bookingGroupMaster)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:30|unique:booking_group_masters,code,'.$bookingGroupMaster->id,
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'prices' => 'nullable|array',
            'prices.*.price_per_night' => 'nullable|numeric|min:0',
            'prices.*.include_breakfast' => 'nullable|boolean',
        ]);

        $bookingGroupMaster->update([
            'code' => ($validated['code'] ?? null) ?: $bookingGroupMaster->code,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->syncPrices($bookingGroupMaster, $request->input('prices', []));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Master grup & master harga berhasil diperbarui.',
                'redirect_url' => route('booking-group-masters.index'),
            ]);
        }

        return redirect()->route('booking-group-masters.index')
            ->with('success', 'Master grup & master harga berhasil diperbarui.');
    }

    /**
     * Hapus master grup (master harga ikut terhapus via cascade).
     */
    public function destroy(Request $request, BookingGroupMaster $bookingGroupMaster)
    {
        $bookingGroupMaster->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Master grup berhasil dihapus.',
                'redirect_url' => route('booking-group-masters.index'),
            ]);
        }

        return redirect()->route('booking-group-masters.index')
            ->with('success', 'Master grup berhasil dihapus.');
    }

    /**
     * Sinkronisasi master harga per tipe kamar (hapus lalu buat ulang).
     */
    private function syncPrices(BookingGroupMaster $master, array $prices): void
    {
        $master->prices()->delete();

        $rows = [];
        foreach ($prices as $roomTypeId => $row) {
            $price = isset($row['price_per_night']) && $row['price_per_night'] !== '' && $row['price_per_night'] !== null
                ? (float) $row['price_per_night']
                : 0;

            if ($price > 0 && RoomType::whereKey($roomTypeId)->exists()) {
                $rows[] = [
                    'booking_group_master_id' => $master->id,
                    'room_type_id' => $roomTypeId,
                    'price_per_night' => $price,
                    'include_breakfast' => ! empty($row['include_breakfast']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (! empty($rows)) {
            BookingGroupMasterPrice::insert($rows);
        }
    }
}
