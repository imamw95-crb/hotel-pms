<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Transaction;
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
            'payment_type' => 'nullable|in:full,dp',
            'dp_amount' => 'nullable|numeric|min:0',
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

        // Standard hotel time: check-in jam 12:00 siang, check-out jam 12:00 siang
        $checkIn = Carbon::parse($validated['check_in'])->setTime(12, 0, 0);
        $checkOut = Carbon::parse($validated['check_out'])->setTime(12, 0, 0);
        $customPrice = $validated['price_per_night'] ?? null;
        $roomPrices = $request->input('room_prices', []);
        $days = $checkIn->diffInDays($checkOut);
        $paymentType = $validated['payment_type'] ?? 'full';
        $dpAmount = $validated['dp_amount'] ?? 0;

        DB::transaction(function () use ($rooms, $guest, $days, $validated, $checkIn, $checkOut, $customPrice, $roomPrices, $paymentType, $dpAmount) {
            $totalAllRooms = 0;
            $reservations = [];

            foreach ($rooms as $room) {
                $pricePerNight = $roomPrices[$room->id] ?? $customPrice ?? $room->price_per_night;
                $totalAmount = $pricePerNight * $days;
                $totalAllRooms += $totalAmount;

                $reservation = Reservation::create([
                    'reservation_number' => 'RES-' . strtoupper(uniqid()),
                    'room_id' => $room->id,
                    'guest_id' => $guest->id,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'status' => 'pending',
                    'total_amount' => $totalAmount,
                    'paid_amount' => 0,
                    'payment_method' => $validated['payment_method'] ?? null,
                    'notes' => $validated['notes'] ?? 'Booking grup',
                    'created_by' => auth()->id(),
                ]);
                $reservations[] = $reservation;
            }

            // Jika DP, buat transaksi DP
            if ($paymentType === 'dp' && $dpAmount > 0) {
                $dpPerRoom = $dpAmount / count($rooms);
                foreach ($reservations as $reservation) {
                    $reservation->update(['paid_amount' => $dpPerRoom]);
                    Transaction::create([
                        'transaction_number' => 'TRX-' . strtoupper(uniqid()),
                        'reservation_id' => $reservation->id,
                        'type' => 'dp',
                        'amount' => $dpPerRoom,
                        'payment_method' => $validated['payment_method'] ?? 'cash',
                        'created_by' => auth()->id(),
                    ]);
                }
            } elseif ($paymentType === 'full') {
                // Jika lunas, set paid_amount = total_amount
                foreach ($reservations as $reservation) {
                    $reservation->update(['paid_amount' => $reservation->total_amount]);
                    Transaction::create([
                        'transaction_number' => 'TRX-' . strtoupper(uniqid()),
                        'reservation_id' => $reservation->id,
                        'type' => 'pelunasan',
                        'amount' => $reservation->total_amount,
                        'payment_method' => $validated['payment_method'] ?? 'cash',
                        'created_by' => auth()->id(),
                    ]);
                }
            }
        });

        $roomNumbers = $rooms->pluck('room_number')->implode(', ');
        $paymentLabel = $paymentType === 'dp' ? ' dengan DP Rp ' . number_format($dpAmount, 0, ',', '.') : ' (Lunas)';
        return redirect()->route('rooms.dashboard')->with('success', "Booking grup untuk kamar: {$roomNumbers}{$paymentLabel} berhasil dibuat.");
    }
}