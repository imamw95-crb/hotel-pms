<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Transaction;
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
            'payment_type' => 'nullable|in:full,dp',
            'dp_amount' => 'nullable|numeric|min:0',
            'ota_reservation_number' => 'nullable|string|max:100',
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

        // Standard hotel time: check-in jam 12:00 siang, check-out jam 12:00 siang
        $checkInDate = Carbon::parse($validated['check_in'])->setTime(12, 0, 0);
        $checkOutDate = Carbon::parse($validated['check_out'])->setTime(12, 0, 0);

        $pricePerNight = $validated['price_per_night'] ?? $room->price_per_night;
        $days = $checkInDate->diffInDays($checkOutDate);
        $totalAmount = $pricePerNight * $days;

        // Determine paid amount based on payment type
        $paidAmount = 0;
        if (($validated['payment_type'] ?? '') === 'dp' && !empty($validated['dp_amount'])) {
            $paidAmount = min($validated['dp_amount'], $totalAmount);
        } elseif (($validated['payment_type'] ?? '') === 'full') {
            $paidAmount = $totalAmount;
        }

        $reservation = Reservation::create([
            'reservation_number' => 'RES-' . strtoupper(uniqid()),
            'ota_reservation_number' => $validated['ota_reservation_number'] ?? null,
            'room_id' => $room->id,
            'guest_id' => $guest->id,
            'check_in' => $checkInDate,
            'check_out' => $checkOutDate,
            'status' => 'pending',
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'payment_method' => $validated['payment_method'] ?? null,
            'notes' => $validated['notes'],
            'created_by' => auth()->id(),
        ]);

        // Create initial transaction if DP or full payment
        if ($paidAmount > 0) {
            Transaction::create([
                'transaction_number' => 'TRX-' . strtoupper(uniqid()),
                'reservation_id' => $reservation->id,
                'type' => ($validated['payment_type'] ?? '') === 'dp' ? 'dp' : 'pelunasan',
                'amount' => $paidAmount,
                'payment_method' => $validated['payment_method'] ?? 'cash',
                'created_by' => auth()->id(),
            ]);
        }

        return redirect()->route('rooms.dashboard')->with('success', "Booking untuk kamar {$room->room_number} berhasil dibuat.");
    }
}