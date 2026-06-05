<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RoomType;
use Illuminate\Http\Request;

class RoomTypePriceApiController extends Controller
{
    /**
     * GET /api/room-types/prices
     *
     * Mengembalikan daftar tipe kamar dengan harga efektif dari PMS.
     * Harga dihitung dari minimal harga per night, weekday, dan weekend
     * dari semua kamar dalam satu tipe.
     */
    public function index(Request $request)
    {
        $roomTypes = RoomType::with('rooms')->orderBy('name')->get();

        $data = $roomTypes->map(function ($roomType) {
            $rooms = $roomType->rooms;

            $weekdayPrices = $rooms->pluck('price_weekday')->filter(fn ($v) => $v > 0);
            $weekendPrices = $rooms->pluck('price_weekend')->filter(fn ($v) => $v > 0);
            $regularPrices = $rooms->pluck('price_per_night')->filter(fn ($v) => $v > 0);

            return [
                'id' => $roomType->id,
                'code' => $roomType->code,
                'name' => $roomType->name,
                'description' => $roomType->description,
                'total_rooms' => $rooms->count(),
                'prices' => [
                    'min_weekday' => $weekdayPrices->min() ?? 0,
                    'min_weekend' => $weekendPrices->min() ?? 0,
                    'min_regular' => $regularPrices->min() ?? 0,
                    'effective_min' => collect([
                        $weekdayPrices->min(),
                        $weekendPrices->min(),
                        $regularPrices->min(),
                    ])->filter()->min() ?? 0,
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar tipe kamar dengan harga',
            'data' => $data,
        ]);
    }
}
