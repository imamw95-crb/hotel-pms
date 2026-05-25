<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Guest;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function create(Request $request)
    {
        return view('booking.create');
    }

    /**
     * Cek ketersediaan kamar via AJAX
     */
    public function checkAvailability(Request $request)
    {
        $checkIn = $request->get('check_in');
        $checkOut = $request->get('check_out');

        if (!$checkIn || !$checkOut) {
            return response()->json(['rooms' => []]);
        }

        // Ambil semua kamar yang aktif
        $allRooms = Room::where('status', '!=', 'maintenance')->get();

        // Filter kamar yang sudah di-booking di tanggal tersebut
        $bookedRoomIds = Reservation::whereIn('status', ['pending', 'checked_in'])
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->whereBetween('check_in', [$checkIn, $checkOut])
                  ->orWhereBetween('check_out', [$checkIn, $checkOut])
                  ->orWhere(function ($q) use ($checkIn, $checkOut) {
                      $q->where('check_in', '<=', $checkIn)
                        ->where('check_out', '>=', $checkOut);
                  });
            })
            ->pluck('room_id')
            ->toArray();

        // Kamar yang tersedia = semua kamar - kamar yang sudah di-booking
        $availableRooms = $allRooms->whereNotIn('id', $bookedRoomIds)->values();

        return response()->json([
            'rooms' => $availableRooms,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
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

        $room = Room::findOrFail($validated['room_id']);

        $guest = Guest::updateOrCreate(
            ['id_number' => $validated['id_number'] ?? null],
            [
                'guest_name' => $validated['guest_name'],
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'address' => $request->input('address') ?? null,
            ]
        );

        $pricePerNight = $validated['price_per_night'] ?? $room->price_per_night;
        $days = Carbon::parse($validated['check_in'])->diffInDays(Carbon::parse($validated['check_out']));
        $totalAmount = $pricePerNight * $days;

        $reservation = Reservation::create([
            'reservation_number' => 'RES-' . strtoupper(uniqid()),
            'room_id' => $room->id,
            'guest_id' => $guest->id,
            'check_in' => $validated['check_in'],
            'check_out' => $validated['check_out'],
            'status' => 'pending',
            'total_amount' => $totalAmount,
            'paid_amount' => 0,
            'payment_method' => $validated['payment_method'] ?? null,
            'notes' => $validated['notes'],
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('rooms.dashboard')->with('success', "Booking untuk kamar {$room->room_number} berhasil dibuat.");
    }
}