<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Allotment;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AllotmentApiController extends Controller
{
    /**
     * GET /api/allotments
     * List allotment dengan filter.
     *
     * Query params: room_type_id, date_from, date_to, channel, per_page
     */
    public function index(Request $request)
    {
        $query = Allotment::with('roomType');

        if ($request->get('room_type_id')) {
            $query->where('room_type_id', $request->get('room_type_id'));
        }

        if ($request->get('date_from')) {
            $query->whereDate('date', '>=', $request->get('date_from'));
        }

        if ($request->get('date_to')) {
            $query->whereDate('date', '<=', $request->get('date_to'));
        }

        if ($request->get('channel')) {
            $query->where('channel', $request->get('channel'));
        }

        $perPage = $request->get('per_page', 50);
        $data = $query->orderBy('date')->orderBy('room_type_id')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * POST /api/allotments
     * Buat atau update allotment.
     *
     * Jika sudah ada record dengan room_type_id + date + channel yang sama, akan di-update.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_type_id' => 'required|exists:room_types,id',
            'date' => 'required|date_format:Y-m-d',
            'allotment' => 'required|integer|min:0',
            'booked' => 'nullable|integer|min:0',
            'channel' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $roomType = RoomType::findOrFail($request->room_type_id);
        $channel = $request->channel ?? '__all_channels';

        // Cek apakah sudah ada
        $existing = Allotment::where('room_type_id', $request->room_type_id)
            ->where('date', $request->date)
            ->where(function ($q) use ($channel) {
                if ($channel === '__all_channels') {
                    $q->whereNull('channel');
                } else {
                    $q->where('channel', $channel);
                }
            })
            ->first();

        if ($existing) {
            $existing->update([
                'allotment' => $request->allotment,
                'booked' => $request->booked ?? $existing->booked,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Allotment untuk {$roomType->name} tanggal {$request->date} berhasil diperbarui.",
                'data' => $existing->fresh()->load('roomType'),
            ]);
        }

        $allotment = Allotment::create([
            'room_type_id' => $request->room_type_id,
            'date' => $request->date,
            'allotment' => $request->allotment,
            'booked' => $request->booked ?? 0,
            'channel' => $request->channel,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Allotment untuk {$roomType->name} tanggal {$request->date} berhasil dibuat.",
            'data' => $allotment->load('roomType'),
        ], 201);
    }

    /**
     * GET /api/allotments/check
     * Cek ketersediaan allotment untuk room type pada range tanggal.
     *
     * Query params: room_type_id, check_in, check_out, channel
     */
    public function check(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_type_id' => 'required|exists:room_types,id',
            'check_in' => 'required|date_format:Y-m-d',
            'check_out' => 'required|date_format:Y-m-d|after:check_in',
            'channel' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $checkIn = Carbon::parse($request->check_in);
        $checkOut = Carbon::parse($request->check_out);
        $roomType = RoomType::findOrFail($request->room_type_id);
        $channel = $request->channel;

        $unavailableDates = Allotment::checkAvailabilityInRange(
            $roomType->id,
            $checkIn,
            $checkOut,
            $channel
        );

        // Ambil detail allotment untuk range tanggal
        $allotments = Allotment::with('roomType')
            ->where('room_type_id', $roomType->id)
            ->whereBetween('date', [$checkIn->format('Y-m-d'), $checkOut->copy()->subDay()->format('Y-m-d')])
            ->when($channel, function ($q) use ($channel) {
                $q->where('channel', $channel);
            })
            ->orderBy('date')
            ->get();

        // Hitung jumlah total kamar untuk room type ini
        $totalRooms = $roomType->rooms()->count();

        return response()->json([
            'success' => true,
            'data' => [
                'room_type' => $roomType,
                'total_rooms' => $totalRooms,
                'check_in' => $request->check_in,
                'check_out' => $request->check_out,
                'channel' => $channel,
                'available' => empty($unavailableDates),
                'unavailable_dates' => $unavailableDates,
                'allotments' => $allotments,
            ],
        ]);
    }

    /**
     * GET /api/allotments/summary
     * Ringkasan allotment per room type untuk range tanggal tertentu.
     *
     * Query params: date (opsional, default today), days (opsional, default 7)
     */
    public function summary(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $days = (int) ($request->get('days', 7));
        $channel = $request->get('channel');

        $startDate = Carbon::parse($date);
        $endDate = $startDate->copy()->addDays($days - 1);

        $roomTypes = RoomType::withCount('rooms')->orderBy('sequence')->get();

        $result = [];
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            $dayData = [
                'date' => $current->format('Y-m-d'),
                'room_types' => [],
            ];

            foreach ($roomTypes as $rt) {
                $query = Allotment::where('room_type_id', $rt->id)
                    ->where('date', $current->format('Y-m-d'));

                if ($channel) {
                    $query->where(function ($q) use ($channel) {
                        $q->where('channel', $channel)
                            ->orWhereNull('channel');
                    });
                }

                $allotment = $query->orderBy('channel', 'desc')->first();

                $dayData['room_types'][] = [
                    'room_type_id' => $rt->id,
                    'room_type_name' => $rt->name,
                    'total_rooms' => $rt->rooms_count,
                    'allotment' => $allotment ? $allotment->allotment : null,
                    'booked' => $allotment ? $allotment->booked : 0,
                    'available' => $allotment ? ($allotment->allotment - $allotment->booked) : null,
                    'has_allotment' => $allotment !== null,
                ];
            }

            $result[] = $dayData;
            $current->addDay();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'channel' => $channel,
                'summary' => $result,
            ],
        ]);
    }

    /**
     * PUT /api/allotments/{allotment}
     * Update allotment.
     */
    public function update(Request $request, Allotment $allotment)
    {
        $validator = Validator::make($request->all(), [
            'allotment' => 'required|integer|min:0',
            'booked' => 'nullable|integer|min:0',
            'channel' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $allotment->update([
            'allotment' => $request->allotment,
            'booked' => $request->booked ?? $allotment->booked,
            'channel' => $request->channel ?? $allotment->channel,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Allotment berhasil diperbarui.',
            'data' => $allotment->fresh()->load('roomType'),
        ]);
    }

    /**
     * DELETE /api/allotments/{allotment}
     * Hapus allotment.
     */
    public function destroy(Allotment $allotment)
    {
        $allotment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Allotment berhasil dihapus.',
        ]);
    }
}
