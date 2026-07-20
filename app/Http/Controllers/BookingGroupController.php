<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\PaymentMethod;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingGroupController extends Controller
{
    public function create()
    {
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'view' => view('booking.modal-group')->render(),
            ]);
        }
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
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'price_per_night' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|in:'.PaymentMethod::where('is_active', true)->pluck('slug')->implode(','),
            'payment_type' => 'nullable|in:full,dp',
            'dp_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $rooms = Room::whereIn('id', $validated['room_ids'])->get();

        // Standard hotel time: check-in jam 12:00 siang, check-out jam 12:00 siang
        $checkIn = Carbon::parse($validated['check_in'])->setTime(12, 0, 0);
        $checkOut = Carbon::parse($validated['check_out'])->setTime(12, 0, 0);

        // ── Validasi ketersediaan kamar ──
        $bookedRoomIds = Reservation::whereIn('status', Reservation::ACTIVE_STATUSES)
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->where('check_in', '<', $checkOut)
                    ->where('check_out', '>', $checkIn);
            })
            ->whereIn('room_id', $validated['room_ids'])
            ->pluck('room_id')
            ->unique()
            ->toArray();

        if (! empty($bookedRoomIds)) {
            $conflictRooms = Room::whereIn('id', $bookedRoomIds)->pluck('room_number')->implode(', ');
            $msg = 'Kamar berikut sudah dibooking untuk periode tersebut: '.$conflictRooms.'. Silakan hapus kamar tersebut dari daftar.';

            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->with('error', $msg)->withInput();
        }

        // ── Guest handling ──
        if (! empty($validated['id_number'])) {
            $guest = Guest::updateOrCreate(
                ['id_number' => $validated['id_number']],
                [
                    'guest_name' => $validated['guest_name'],
                    'phone' => $validated['phone'] ?? null,
                    'email' => $validated['email'] ?? null,
                ]
            );
        } else {
            $guest = Guest::create([
                'guest_name' => $validated['guest_name'],
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
            ]);
        }

        $customPrice = $validated['price_per_night'] ?? null;
        $roomPrices = $request->input('room_prices', []);
        $days = $checkIn->diffInDays($checkOut);
        $paymentType = $validated['payment_type'] ?? 'full';
        $dpAmount = $validated['dp_amount'] ?? 0;
        $bookingGroupId = (string) Str::uuid();

        DB::transaction(function () use ($rooms, $guest, $days, $validated, $checkIn, $checkOut, $customPrice, $roomPrices, $paymentType, $dpAmount, $bookingGroupId) {
            $totalAllRooms = 0;
            $reservations = [];

            foreach ($rooms as $room) {
                // If custom price provided, use flat rate; otherwise use weekday/weekend dynamic pricing
                $roomCustomPrice = $roomPrices[$room->id] ?? $customPrice;
                if ($roomCustomPrice) {
                    $totalAmount = $roomCustomPrice * $days;
                } else {
                    $totalAmount = $room->calculateTotalForRange($checkIn, $checkOut);
                }
                $totalAllRooms += $totalAmount;

                $reservation = Reservation::create([
                    'booking_group_id' => $bookingGroupId,
                    'reservation_number' => 'RES-'.strtoupper(uniqid()),
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
                        'transaction_number' => 'TRX-'.strtoupper(uniqid()),
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
                        'transaction_number' => 'TRX-'.strtoupper(uniqid()),
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
        $paymentLabel = $paymentType === 'dp' ? ' dengan DP Rp '.number_format($dpAmount, 0, ',', '.') : ' (Lunas)';
        $message = "Booking grup untuk kamar: {$roomNumbers}{$paymentLabel} berhasil dibuat.";

        // Check if request is AJAX
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect_url' => route('rooms.dashboard'),
            ]);
        }

        return redirect()->route('rooms.dashboard')->with('success', $message);
    }
}
