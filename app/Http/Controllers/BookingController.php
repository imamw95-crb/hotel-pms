<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Room;
use App\Services\BookingNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

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

        // Ambil semua kamar yang aktif
        $allRooms = Room::where('status', '!=', 'maintenance')->get();

        // Filter kamar yang sudah di-booking di tanggal tersebut
        // Back-to-Booking: check-out di hari yang sama dengan check-in baru
        // TIDAK dianggap bentrok (check-out 12:00, check-in 14:00)
        $bookedRoomIds = Reservation::whereIn('status', ['pending', 'checked_in'])
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->where('check_in', '<', $checkOut)
                    ->where('check_out', '>', $checkIn);
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
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'price_per_night' => 'nullable|numeric|min:0',
            'ota_reservation_number' => 'nullable|string|max:100',
            'ota_source' => 'nullable|string|in:traveloka.com,tiket.com',
            'ota_payment_status' => 'nullable|string|in:paid_ota,partial_ota,unpaid_ota',
            'ota_paid_amount' => 'nullable|numeric|min:0',
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
        // (sudah di-set di atas sebelum validasi)
        // If custom price_per_night provided, use flat rate; otherwise use weekday/weekend dynamic pricing
        $days = $checkInDate->diffInDays($checkOutDate);
        if (! empty($validated['price_per_night'])) {
            $totalAmount = $validated['price_per_night'] * $days;
        } else {
            $totalAmount = $room->calculateTotalForRange($checkInDate, $checkOutDate);
        }

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
            'paid_amount' => 0,
            'notes' => $validated['notes'],
            'include_breakfast' => $request->boolean('include_breakfast'),
            'created_by' => auth()->id(),
        ]);

        // Trigger notification — OTA or web
        if (! empty($validated['ota_source'])) {
            app(BookingNotificationService::class)->otaBookingCreated(
                $reservation,
                [
                    'guest_name' => $validated['guest_name'],
                    'reservation_id' => $validated['ota_reservation_number'] ?? '',
                ],
                $validated['ota_source']
            );
        } else {
            app(BookingNotificationService::class)->webBookingCreated($reservation);
        }

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Booking untuk kamar {$room->room_number} berhasil dibuat.",
                'redirect_url' => route('rooms.dashboard'),
                'reservation' => $reservation,
            ]);
        }

        return redirect()->route('rooms.dashboard')->with('success', "Booking untuk kamar {$room->room_number} berhasil dibuat.");
    }
}
