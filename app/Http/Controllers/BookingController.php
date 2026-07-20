<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\PaymentMethod;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Transaction;
use App\Services\BookingNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function create(Request $request)
    {
        $selectedRoom = null;
        if ($request->has('room_id')) {
            $selectedRoom = Room::find($request->input('room_id'));
        }

        // Set default tanggal jika tidak ada parameter
        $checkIn = $request->input('check_in', Carbon::today()->format('Y-m-d'));
        $checkOut = $request->input('check_out', Carbon::tomorrow()->format('Y-m-d'));

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'view' => view('booking.modal-create', compact('selectedRoom', 'checkIn', 'checkOut'))->render(),
            ]);
        }

        return view('booking.create', compact('selectedRoom', 'checkIn', 'checkOut'));
    }

    /**
     * Cek ketersediaan kamar via AJAX
     */
    public function checkAvailability(Request $request)
    {
        $checkIn = $request->get('check_in');
        $checkOut = $request->get('check_out');

        if (! $checkIn || ! $checkOut) {
            return response()->json(['rooms' => []]);
        }

        // Set time ke 12:00 agar match dengan jam reservasi (back-to-back aware)
        $checkInDate = Carbon::parse($checkIn)->setTime(12, 0, 0);
        $checkOutDate = Carbon::parse($checkOut)->setTime(12, 0, 0);

        // Ambil semua kamar yang aktif
        $allRooms = Room::where('status', '!=', 'maintenance')->get();

        // Filter kamar yang sudah di-booking di tanggal tersebut
        // Back-to-Booking: check-out di hari yang sama dengan check-in baru
        // TIDAK dianggap bentrok (check-out 12:00, check-in 12:00)
        $bookedRoomIds = Reservation::whereIn('status', Reservation::ACTIVE_STATUSES)
            ->where(function ($q) use ($checkInDate, $checkOutDate) {
                $q->where('check_in', '<', $checkOutDate)
                    ->where('check_out', '>', $checkInDate);
            })
            ->pluck('room_id')
            ->toArray();

        // Kamar yang tersedia = semua kamar - kamar yang sudah di-booking
        $availableRooms = $allRooms->whereNotIn('id', $bookedRoomIds)->values();

        return response()->json([
            'rooms' => $availableRooms,
        ]);
    }

    /**
     * Show OTA booking modal form.
     */
    public function otaCreate(Request $request)
    {
        $selectedRoom = null;
        if ($request->has('room_id')) {
            $selectedRoom = Room::find($request->input('room_id'));
        }

        $checkIn = $request->input('check_in', Carbon::today()->format('Y-m-d'));
        $checkOut = $request->input('check_out', Carbon::tomorrow()->format('Y-m-d'));

        $otaSources = [
            'traveloka.com' => 'Traveloka',
            'tiket.com' => 'Tiket.com',
        ];

        return response()->json([
            'success' => true,
            'view' => view('booking.modal-ota', compact('selectedRoom', 'checkIn', 'checkOut', 'otaSources'))->render(),
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
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'price_per_night' => 'nullable|numeric|min:0',
            'ota_reservation_number' => 'nullable|string|max:100',
            'ota_source' => 'nullable|string|in:traveloka.com,tiket.com',
            'ota_payment_status' => 'nullable|string|in:paid_ota,partial_ota,unpaid_ota',
            'ota_paid_amount' => 'nullable|numeric|min:0',
            'payment_type' => 'nullable|string|in:full,dp',
            'payment_method' => 'nullable|string|in:'.PaymentMethod::where('is_active', true)->pluck('slug')->implode(','),
            'dp_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'include_breakfast' => 'nullable|boolean',
        ]);

        $room = Room::findOrFail($validated['room_id']);

        // Validasi ketersediaan kamar (back-to-back aware)
        $checkInDate = Carbon::parse($validated['check_in'])->setTime(12, 0, 0);
        $checkOutDate = Carbon::parse($validated['check_out'])->setTime(12, 0, 0);
        if (! $room->isAvailable($checkInDate, $checkOutDate)) {
            return back()->with('error', "Kamar {$room->room_number} sudah dipesan untuk periode tersebut.")->withInput();
        }

        if (!empty($validated['id_number'])) {
            $guest = Guest::updateOrCreate(
                ['id_number' => $validated['id_number']],
                [
                    'guest_name' => $validated['guest_name'],
                    'phone' => $validated['phone'] ?? null,
                    'email' => $validated['email'] ?? null,
                    'address' => $request->input('address') ?? null,
                ]
            );
        } else {
            $guest = Guest::create([
                'guest_name' => $validated['guest_name'],
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'address' => $request->input('address') ?? null,
            ]);
        }

        // Standard hotel time: check-in jam 12:00 siang, check-out jam 12:00 siang
        // (sudah di-set di atas sebelum validasi)
        // If custom price_per_night provided, use flat rate; otherwise use weekday/weekend dynamic pricing
        $days = $checkInDate->diffInDays($checkOutDate);
        if (! empty($validated['price_per_night'])) {
            $totalAmount = $validated['price_per_night'] * $days;
        } else {
            $totalAmount = $room->calculateTotalForRange($checkInDate, $checkOutDate);
        }

        // Hitung DP jika ada
        $paymentType = $validated['payment_type'] ?? 'full';
        $dpAmount = ($paymentType === 'dp') ? (float) ($validated['dp_amount'] ?? 0) : 0;
        $paymentMethod = $validated['payment_method'] ?? null;

        // Jika full payment (lunas), set paid_amount = total_amount
        $initialPaid = ($paymentType === 'full') ? $totalAmount : $dpAmount;

        $reservation = Reservation::create([
            'reservation_number' => 'RES-'.strtoupper(uniqid()),
            'ota_reservation_number' => $validated['ota_reservation_number'] ?? null,
            'ota_source' => $validated['ota_source'] ?? null,
            'ota_payment_status' => $validated['ota_payment_status'] ?? null,
            'ota_paid_amount' => $validated['ota_paid_amount'] ?? 0,
            'room_id' => $room->id,
            'guest_id' => $guest->id,
            'check_in' => $checkInDate,
            'check_out' => $checkOutDate,
            'status' => 'pending',
            'total_amount' => $totalAmount,
            'paid_amount' => $initialPaid,
            'payment_method' => $paymentMethod,
            'notes' => $validated['notes'],
            'include_breakfast' => $request->boolean('include_breakfast'),
            'created_by' => auth()->id(),
        ]);

        // Buat transaksi pembayaran jika ada pembayaran awal (DP atau Lunas)
        if ($initialPaid > 0 && $paymentMethod) {
            DB::beginTransaction();
            try {
                $txnType = ($paymentType === 'full') ? 'pelunasan' : 'dp';
                Transaction::create([
                    'transaction_number' => 'TRX-'.strtoupper(uniqid()),
                    'reservation_id' => $reservation->id,
                    'type' => $txnType,
                    'amount' => $initialPaid,
                    'payment_method' => $paymentMethod,
                    'notes' => 'Pembayaran awal saat booking'.($paymentType === 'full' ? ' (Lunas)' : ' (DP)'),
                    'created_by' => auth()->id(),
                ]);

                if ($reservation->paid_amount >= $reservation->total_amount) {
                    $reservation->paid_date = now();
                    $reservation->save();
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                // Jangan gagalkan booking, hanya log error
                \Log::error('Gagal membuat transaksi awal: '.$e->getMessage());
            }
        }

        // Trigger notification — only for OTA bookings, not direct PMS bookings
        if (! empty($validated['ota_source'])) {
            app(BookingNotificationService::class)->otaBookingCreated(
                $reservation,
                [
                    'guest_name' => $validated['guest_name'],
                    'reservation_id' => $validated['ota_reservation_number'] ?? '',
                ],
                $validated['ota_source']
            );
        }

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Booking untuk kamar {$room->room_number} berhasil dibuat.",
                'reservation' => $reservation,
            ]);
        }

        return redirect()->route('rooms.dashboard')->with('success', "Booking untuk kamar {$room->room_number} berhasil dibuat.");
    }
}
