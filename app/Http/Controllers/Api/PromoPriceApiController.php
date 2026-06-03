<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\RoomTypeDatePrice;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PromoPriceApiController extends Controller
{
    /**
     * Get all promo prices with optional filters.
     *
     * Query params:
     * - room_type_id (optional) - Filter by room type
     * - date_from (optional) - Filter start date
     * - date_to (optional) - Filter end date
     * - date (optional) - Filter specific date (overrides date_from/date_to)
     */
    public function index(Request $request)
    {
        $query = RoomTypeDatePrice::with('roomType')
            ->orderBy('room_type_id')
            ->orderBy('date');

        if ($request->filled('room_type_id')) {
            $query->where('room_type_id', $request->room_type_id);
        }

        if ($request->filled('date')) {
            $query->where('date', $request->date);
        } else {
            if ($request->filled('date_from')) {
                $query->where('date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->where('date', '<=', $request->date_to);
            }
        }

        $promoPrices = $query->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'room_type_id' => $item->room_type_id,
                'room_type_name' => $item->roomType->name ?? null,
                'date' => $item->date->format('Y-m-d'),
                'price' => (float) $item->price,
                'price_formatted' => 'Rp ' . number_format($item->price, 0, ',', '.'),
                'label' => $item->label,
                'created_at' => $item->created_at->toISOString(),
                'updated_at' => $item->updated_at->toISOString(),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar harga promo',
            'data' => $promoPrices,
            'meta' => [
                'total' => $promoPrices->count(),
                'filters' => [
                    'room_type_id' => $request->room_type_id,
                    'date_from' => $request->date_from,
                    'date_to' => $request->date_to,
                    'date' => $request->date,
                ],
            ],
        ]);
    }

    /**
     * Get room types with their current promo prices.
     * Useful for OTA/channel integrations to see active promos.
     */
    public function roomTypes(Request $request)
    {
        $roomTypes = RoomType::with(['datePrices' => function ($q) use ($request) {
            if ($request->filled('date_from')) {
                $q->where('date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $q->where('date', '<=', $request->date_to);
            }
            if ($request->filled('date')) {
                $q->where('date', $request->date);
            }
            $q->orderBy('date');
        }])->orderBy('name')->get();

        $data = $roomTypes->map(function ($rt) {
            return [
                'id' => $rt->id,
                'name' => $rt->name,
                'code' => $rt->code,
                'promo_prices' => $rt->datePrices->map(function ($pp) {
                    return [
                        'id' => $pp->id,
                        'date' => $pp->date->format('Y-m-d'),
                        'price' => (float) $pp->price,
                        'price_formatted' => 'Rp ' . number_format($pp->price, 0, ',', '.'),
                        'label' => $pp->label,
                    ];
                }),
                'active_promos_count' => $rt->datePrices->count(),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar tipe kamar dengan harga promo',
            'data' => $data,
        ]);
    }

    /**
     * Check effective price for a room on a specific date or date range.
     *
     * Query params:
     * - room_id (required) - Room ID
     * - date (optional) - Specific date to check
     * - check_in (optional) - Start date for range
     * - check_out (optional) - End date for range (exclusive)
     *
     * Returns price breakdown showing promo, weekend/weekday, and default rates.
     */
    public function checkPrice(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'date' => 'nullable|date',
            'check_in' => 'nullable|date|required_with:check_out',
            'check_out' => 'nullable|date|after:check_in|required_with:check_in',
        ]);

        $room = Room::with('roomType.datePrices')->findOrFail($validated['room_id']);

        // Single date check
        if ($request->filled('date')) {
            $date = Carbon::parse($validated['date']);
            $price = $room->getPriceForDate($date);

            $promoPrice = null;
            if ($room->roomType) {
                $dateStr = $date->format('Y-m-d');
                $promo = $room->roomType->datePrices->first(function ($dp) use ($dateStr) {
                    return $dp->date->format('Y-m-d') === $dateStr;
                });
                if ($promo) {
                    $promoPrice = [
                        'price' => (float) $promo->price,
                        'label' => $promo->label,
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Harga efektif untuk tanggal ' . $date->format('Y-m-d'),
                'data' => [
                    'room_id' => $room->id,
                    'room_number' => $room->room_number,
                    'room_type_name' => $room->room_type_name,
                    'date' => $date->format('Y-m-d'),
                    'effective_price' => (float) $price,
                    'effective_price_formatted' => 'Rp ' . number_format($price, 0, ',', '.'),
                    'price_breakdown' => [
                        'promo_price' => $promoPrice,
                        'weekday_price' => (float) $room->price_weekday,
                        'weekend_price' => (float) $room->price_weekend,
                        'default_price' => (float) $room->price_per_night,
                    ],
                    'is_weekend' => Room::isWeekend($date),
                ],
            ]);
        }

        // Date range check (check_in to check_out)
        if ($request->filled('check_in')) {
            $checkIn = Carbon::parse($validated['check_in']);
            $checkOut = Carbon::parse($validated['check_out']);

            $total = $room->calculateTotalForRange($checkIn, $checkOut);
            $nights = $checkIn->diffInDays($checkOut);

            // Nightly breakdown
            $nightlyBreakdown = [];
            $current = $checkIn->copy();
            while ($current->lt($checkOut)) {
                $nightPrice = $room->getPriceForDate($current);

                $promoPrice = null;
                if ($room->roomType) {
                    $dayStr = $current->format('Y-m-d');
                    $promo = $room->roomType->datePrices->first(function ($dp) use ($dayStr) {
                        return $dp->date->format('Y-m-d') === $dayStr;
                    });
                    if ($promo) {
                        $promoPrice = [
                            'price' => (float) $promo->price,
                            'label' => $promo->label,
                        ];
                    }
                }

                $nightlyBreakdown[] = [
                    'date' => $current->format('Y-m-d'),
                    'day_name' => $current->isoFormat('dddd'),
                    'is_weekend' => Room::isWeekend($current),
                    'effective_price' => (float) $nightPrice,
                    'effective_price_formatted' => 'Rp ' . number_format($nightPrice, 0, ',', '.'),
                    'promo_applied' => $promoPrice,
                ];
                $current->addDay();
            }

            return response()->json([
                'success' => true,
                'message' => 'Perhitungan harga untuk rentang tanggal',
                'data' => [
                    'room_id' => $room->id,
                    'room_number' => $room->room_number,
                    'room_type_name' => $room->room_type_name,
                    'check_in' => $checkIn->format('Y-m-d'),
                    'check_out' => $checkOut->format('Y-m-d'),
                    'nights' => $nights,
                    'total_price' => (float) $total,
                    'total_price_formatted' => 'Rp ' . number_format($total, 0, ',', '.'),
                    'nightly_breakdown' => $nightlyBreakdown,
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Parameter tidak lengkap. Kirimkan date atau (check_in + check_out).',
        ], 422);
    }
}
