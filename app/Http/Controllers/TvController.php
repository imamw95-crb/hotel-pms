<?php

namespace App\Http\Controllers;

use App\Models\HotelSetting;
use App\Models\Reservation;
use App\Models\Room;

class TvController extends Controller
{
    /**
     * Tampilkan welcome screen untuk kamar tertentu.
     */
    public function welcome($roomNumber)
    {
        $room = Room::where('room_number', $roomNumber)->firstOrFail();

        $reservation = Reservation::with('guest')
            ->where('room_id', $room->id)
            ->where('status', 'checked_in')
            ->first();

        $hotelSetting = HotelSetting::get();

        return view('tv.welcome', compact('room', 'reservation', 'hotelSetting'));
    }

    /**
     * Endpoint JSON untuk polling real-time status kamar.
     */
    public function status($roomNumber)
    {
        $room = Room::where('room_number', $roomNumber)->first();

        if (! $room) {
            return response()->json(['error' => 'Room not found'], 404);
        }

        $reservation = Reservation::with('guest')
            ->where('room_id', $room->id)
            ->where('status', 'checked_in')
            ->first();

        $hotelSetting = HotelSetting::get();

        return response()->json([
            'room_number' => $room->room_number,
            'room_status' => $room->status,
            'has_guest' => ! is_null($reservation),
            'guest_name' => $reservation && $reservation->guest ? 'Mr/Ms. ' . $reservation->guest->guest_name : null,
            'check_in' => $reservation?->check_in,
            'check_out' => $reservation?->check_out,
            'hotel_name' => $hotelSetting->hotel_name,
        ]);
    }
}
