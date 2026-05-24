<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Guest;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingGroupController extends Controller
{
    public function create()
    {
        $rooms = Room::where('status', 'available')->orderBy('room_number')->get();
        return view('booking.group', compact('rooms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_ids' => 'required|array|min:1',
            'room_ids.*' => 'exists:rooms,id',
            'guest_name' => 'required|string|max:100',
            'id_number' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'price_per_night' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|in:cash,bank_transfer,credit_card,debit_card',
            'notes' => 'nullable|string',
        ]);

        $rooms = Room::whereIn('id', $validated['room_ids'])->get();

        $guest = Guest::updateOrCreate(
            ['id_number' => $validated['id_number'] ?? null],
            [
                'guest_name' => $validated['guest_name'],
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
            ]
        );

        $customPrice = $validated['price_per_night'] ?? null;
        $checkIn = Carbon::parse($validated['check_in']);
        $checkOut = Carbon::parse($validated['check_out']);
        $days = $checkIn->diffInDays($checkOut);

        DB::transaction(function () use ($rooms, $guest, $days, $validated, $checkIn, $checkOut, $customPrice) {
            foreach ($rooms as $room) {
                $pricePerNight = $customPrice ?? $room->price_per_night;
                $totalAmount = $pricePerNight * $days;
                Reservation::create([
                    'reservation_number' => 'RES-' . strtoupper(uniqid()),
                    'room_id' => $room->id,
                    'guest_id' => $guest->id,
                    'check_in' => $validated['check_in'],
                    'check_out' => $validated['check_out'],
                    'status' => 'pending',
                    'total_amount' => $totalAmount,
                    'paid_amount' => 0,
                    'payment_method' => $validated['payment_method'] ?? null,
                    'notes' => $validated['notes'] ?? 'Booking grup',
                    'created_by' => auth()->id(),
                ]);
            }
        });

        $roomNumbers = $rooms->pluck('room_number')->implode(', ');
        return redirect()->route('rooms.dashboard')->with('success', "Booking grup untuk kamar: {$roomNumbers} berhasil dibuat.");
    }
}