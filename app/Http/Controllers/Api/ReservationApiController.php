<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\MHSLog;
use App\Models\OutOfOrder;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Transaction;
use App\Services\AvailabilityService;
use App\Services\BookingNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReservationApiController extends Controller
{
    // ========== RESERVATIONS ==========

    /**
     * GET /api/reservations
     * List semua reservasi dengan filter & pagination
     *
     * Query params: search, status, date_from, date_to, per_page, page
     */
    public function index(Request $request)
    {
        $query = Reservation::with(['guest', 'room', 'createdBy']);

        // Search
        $search = $request->get('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reservation_number', 'like', "%{$search}%")
                    ->orWhereHas('guest', function ($q) use ($search) {
                        $q->where('guest_name', 'like', "%{$search}%")
                            ->orWhere('id_number', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })
                    ->orWhereHas('room', function ($q) use ($search) {
                        $q->where('room_number', 'like', "%{$search}%");
                    });
            });
        }

        // Filter status
        if ($request->get('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter tanggal
        if ($request->get('date_from')) {
            $query->whereDate('check_in', '>=', $request->get('date_from'));
        }
        if ($request->get('date_to')) {
            $query->whereDate('check_out', '<=', $request->get('date_to'));
        }

        $perPage = $request->get('per_page', 15);
        $reservations = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $reservations,
        ]);
    }

    /**
     * GET /api/reservations/{reservation}
     * Detail reservasi
     */
    public function show(Reservation $reservation)
    {
        $reservation->load(['guest', 'room', 'createdBy']);
        $transactions = Transaction::where('reservation_id', $reservation->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'reservation' => $reservation,
                'transactions' => $transactions,
                'remaining_payment' => $reservation->remaining_payment,
                'nights' => $reservation->nights,
            ],
        ]);
    }

    /**
     * POST /api/reservations
     * Buat reservasi baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'guest_name' => 'required|string|max:255',
            'guest_phone' => 'nullable|string|max:20',
            'guest_email' => 'nullable|email|max:255',
            'guest_id_number' => 'nullable|string|max:50',
            'room_id' => 'required|exists:rooms,id',
            'check_in' => 'required|date_format:Y-m-d',
            'check_out' => 'required|date_format:Y-m-d|after:check_in',
            'guest_count' => 'nullable|integer|min:1|max:10',
            'total_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
            'ota_source' => 'nullable|string|max:50',
            'ota_reservation_number' => 'nullable|string|max:100',
        ]);

        $checkIn = Carbon::parse($validated['check_in'])->setTime(14, 0);
        $checkOut = Carbon::parse($validated['check_out'])->setTime(12, 0);

        // Cek kamar available (back-to-back allowed)
        $room = Room::findOrFail($validated['room_id']);
        if (! $room->isAvailable($checkIn->format('Y-m-d H:i:s'), $checkOut->format('Y-m-d H:i:s'))) {
            return response()->json([
                'success' => false,
                'message' => "Kamar {$room->room_number} tidak tersedia untuk periode tersebut.",
            ], 422);
        }

        try {
            $reservation = DB::transaction(function () use ($validated, $checkIn, $checkOut, $room) {
                // Find or create guest
                $guest = Guest::firstOrCreate(
                    ['guest_name' => $validated['guest_name']],
                    [
                        'phone' => $validated['guest_phone'] ?? null,
                        'email' => $validated['guest_email'] ?? null,
                        'id_number' => $validated['guest_id_number'] ?? null,
                    ]
                );

                // Calculate total if not provided
                $totalAmount = $validated['total_amount'] ?? 0;
                if ($totalAmount <= 0) {
                    $totalAmount = $room->calculateTotalForRange($checkIn, $checkOut);
                }

                $otaSource = $validated['ota_source'] ?? 'api';
                $isWebsite = $otaSource === 'website';

                $reservation = Reservation::create([
                    'guest_id' => $guest->id,
                    'room_id' => $room->id,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'number_of_cards' => $validated['guest_count'] ?? 1,
                    'total_amount' => $totalAmount,
                    'payment_method' => $validated['payment_method'] ?? ($isWebsite ? 'transfer_bca' : 'cash'),
                    'paid_amount' => 0,
                    'status' => $isWebsite ? 'menunggu_pembayaran' : 'pending',
                    'notes' => $validated['notes'] ?? null,
                    'ota_source' => $otaSource,
                    'ota_payment_status' => $isWebsite ? 'pending' : null,
                    'ota_reservation_number' => $validated['ota_reservation_number'] ?? null,
                    'room_type_name' => $room->room_type_name,
                    'created_by' => auth()->id(),
                ]);

                return $reservation;
            });

            $reservation->load(['guest', 'room']);

            // Trigger notification
            if (! empty($validated['ota_source']) && ! in_array($validated['ota_source'], ['website', 'api'])) {
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

            return response()->json([
                'success' => true,
                'message' => "Reservasi {$reservation->reservation_number} berhasil dibuat.",
                'data' => $reservation,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat reservasi: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * PUT /api/reservations/{reservation}
     * Update reservasi
     */
    public function update(Request $request, Reservation $reservation)
    {
        if ($reservation->status === 'checked_out' || $reservation->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa mengubah reservasi yang sudah checkout / dibatalkan.',
            ], 422);
        }

        $validated = $request->validate([
            'guest_name' => 'sometimes|string|max:255',
            'guest_phone' => 'nullable|string|max:20',
            'guest_email' => 'nullable|email|max:255',
            'check_in' => 'sometimes|date_format:Y-m-d',
            'check_out' => 'sometimes|date_format:Y-m-d|after:check_in',
            'guest_count' => 'nullable|integer|min:1|max:10',
            'notes' => 'nullable|string|max:1000',
            'payment_method' => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            // Update guest info jika ada
            if (isset($validated['guest_name']) || isset($validated['guest_phone']) || isset($validated['guest_email'])) {
                $guest = $reservation->guest;
                $guest->update(array_filter([
                    'guest_name' => $validated['guest_name'] ?? null,
                    'phone' => $validated['guest_phone'] ?? null,
                    'email' => $validated['guest_email'] ?? null,
                ], fn ($v) => $v !== null));
            }

            // Update check_in/check_out jika ada
            if (isset($validated['check_in'])) {
                $reservation->check_in = Carbon::parse($validated['check_in'])->setTime(14, 0);
            }
            if (isset($validated['check_out'])) {
                $reservation->check_out = Carbon::parse($validated['check_out'])->setTime(12, 0);
            }

            // Recalculate total jika tanggal berubah
            if (isset($validated['check_in']) || isset($validated['check_out'])) {
                $room = $reservation->room;
                $reservation->total_amount = $room->calculateTotalForRange($reservation->check_in, $reservation->check_out);
            }

            // Update field lain
            $fillable = array_intersect_key($validated, array_flip([
                'number_of_cards', 'notes', 'payment_method',
            ]));
            $reservation->fill($fillable);
            $reservation->save();

            DB::commit();

            $reservation->load(['guest', 'room']);

            return response()->json([
                'success' => true,
                'message' => 'Reservasi berhasil diperbarui.',
                'data' => $reservation,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal update reservasi: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/reservations/{reservation}/cancel
     */
    public function cancel(Reservation $reservation)
    {
        if ($reservation->status === 'checked_in') {
            return response()->json([
                'success' => false,
                'message' => 'Reservasi yang sudah check-in tidak bisa dibatalkan.',
            ], 422);
        }

        if ($reservation->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Reservasi sudah dibatalkan.',
            ], 422);
        }

        $reservation->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => "Reservasi {$reservation->reservation_number} berhasil dibatalkan.",
            'data' => $reservation,
        ]);
    }

    /**
     * POST /api/reservations/{reservation}/checkin
     */
    public function checkin(Reservation $reservation)
    {
        if ($reservation->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya reservasi dengan status pending yang bisa di-check-in.',
            ], 422);
        }

        $reservation->update(['status' => 'checked_in']);
        $reservation->room->update(['status' => 'occupied']);

        return response()->json([
            'success' => true,
            'message' => "Check-in berhasil untuk kamar {$reservation->room->room_number}.",
            'data' => $reservation,
        ]);
    }

    /**
     * POST /api/reservations/{reservation}/checkout
     */
    public function checkout(Reservation $reservation)
    {
        if ($reservation->status !== 'checked_in') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya reservasi yang sudah check-in yang bisa di-check-out.',
            ], 422);
        }

        $checkoutTime = Carbon::today()->setTime(12, 0, 0);

        $reservation->update([
            'status' => 'checked_out',
            'check_out' => $checkoutTime,
        ]);
        $reservation->room->update(['status' => 'available']);

        return response()->json([
            'success' => true,
            'message' => "Check-out berhasil untuk kamar {$reservation->room->room_number}.",
            'data' => $reservation,
        ]);
    }

    /**
     * POST /api/reservations/{reservation}/change-room
     */
    public function changeRoom(Request $request, Reservation $reservation)
    {
        if (! in_array($reservation->status, ['pending', 'checked_in'])) {
            return response()->json([
                'success' => false,
                'message' => 'Pindah kamar hanya bisa dilakukan untuk reservasi dengan status pending atau check-in.',
            ], 422);
        }

        $validated = $request->validate([
            'new_room_id' => 'required|exists:rooms,id',
            'reason' => 'nullable|string|max:255',
        ]);

        $newRoom = Room::findOrFail($validated['new_room_id']);

        $checkIn = $reservation->check_in->format('Y-m-d H:i:s');
        $checkOut = $reservation->check_out->format('Y-m-d H:i:s');

        if (! $newRoom->isAvailable($checkIn, $checkOut, $reservation->id)) {
            return response()->json([
                'success' => false,
                'message' => "Kamar {$newRoom->room_number} tidak tersedia untuk periode tersebut.",
            ], 422);
        }

        if ($newRoom->status !== 'available') {
            return response()->json([
                'success' => false,
                'message' => "Kamar {$newRoom->room_number} tidak dalam status available.",
            ], 422);
        }

        $oldRoom = $reservation->room;
        $oldRoomNumber = $oldRoom->room_number;
        $newRoomNumber = $newRoom->room_number;
        $oldTotalAmount = $reservation->total_amount;
        $newTotalAmount = $newRoom->calculateTotalForRange($reservation->check_in, $reservation->check_out);

        $reservation->room_id = $newRoom->id;
        $reservation->total_amount = $newTotalAmount;
        if ($validated['reason']) {
            $reservation->notes = ($reservation->notes ? $reservation->notes."\n" : '').'['.now()->format('d/m/Y H:i').'] Pindah kamar dari '.$oldRoomNumber.' ke '.$newRoomNumber.': '.$validated['reason'];
        }
        $reservation->save();

        // Update status kamar berdasarkan status reservasi
        if ($reservation->status === 'checked_in') {
            $oldRoom->update(['status' => 'cleaning']);
            $newRoom->update(['status' => 'occupied']);
        } else {
            $oldRoom->update(['status' => 'available']);
        }

        MHSLog::create([
            'command' => 'room_change',
            'reservation_id' => $reservation->id,
            'request_data' => [
                'old_room_id' => $oldRoom->id,
                'old_room_number' => $oldRoomNumber,
                'new_room_id' => $newRoom->id,
                'new_room_number' => $newRoomNumber,
                'reason' => $validated['reason'] ?? null,
                'old_total_amount' => $oldTotalAmount,
                'new_total_amount' => $newTotalAmount,
            ],
            'response_data' => [
                'success' => true,
                'message' => "Pindah kamar dari {$oldRoomNumber} ke {$newRoomNumber} berhasil.",
            ],
            'success' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Pindah kamar dari {$oldRoomNumber} ke {$newRoomNumber} berhasil.",
            'data' => $reservation,
        ]);
    }

    /**
     * POST /api/reservations/{reservation}/payments
     */
    public function addPayment(Request $request, Reservation $reservation)
    {
        if ($reservation->status === 'cancelled' || $reservation->status === 'checked_out') {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa menambah pembayaran untuk reservasi ini.',
            ], 422);
        }

        $validated = $request->validate([
            'payment_type' => 'required|in:dp,pelunasan,tambahan',
            'payment_method' => 'required|string|max:50',
            'amount' => 'required|numeric|min:0',
            'ota_payment_status' => 'nullable|in:paid_ota,partial_ota,unpaid_ota',
            'ota_paid_amount' => 'nullable|numeric|min:0',
        ]);

        $hotelAmount = $validated['amount'];
        $otaPaymentStatus = $validated['ota_payment_status'] ?? null;
        $otaPaidAmount = $validated['ota_paid_amount'] ?? 0;

        $totalInput = $hotelAmount + $otaPaidAmount;
        if ($totalInput > $reservation->total_amount) {
            return response()->json([
                'success' => false,
                'message' => 'Total pembayaran (OTA + Hotel) melebihi total tagihan.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            if ($otaPaymentStatus) {
                $reservation->ota_payment_status = $otaPaymentStatus;
                $reservation->ota_paid_amount = $otaPaidAmount;
            }

            if ($otaPaidAmount > 0) {
                $otaTxnType = ($otaPaidAmount >= $reservation->total_amount) ? 'pelunasan' : 'dp';
                Transaction::create([
                    'transaction_number' => 'TRX-'.strtoupper(uniqid()),
                    'reservation_id' => $reservation->id,
                    'type' => $otaTxnType,
                    'amount' => $otaPaidAmount,
                    'payment_method' => $validated['payment_method'],
                    'notes' => 'OTA '.$validated['payment_method'].' — '.str_replace('_', ' ', $otaPaymentStatus),
                    'created_by' => auth()->id(),
                ]);
            }

            if ($hotelAmount > 0) {
                Transaction::create([
                    'transaction_number' => 'TRX-'.strtoupper(uniqid()),
                    'reservation_id' => $reservation->id,
                    'type' => $validated['payment_type'],
                    'amount' => $hotelAmount,
                    'payment_method' => $validated['payment_method'],
                    'created_by' => auth()->id(),
                ]);
            }

            $reservation->paid_amount += ($otaPaidAmount + $hotelAmount);

            if ($reservation->paid_amount >= $reservation->total_amount) {
                $reservation->paid_date = now();
            }

            $reservation->payment_method = $validated['payment_method'];
            $reservation->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran sebesar Rp '.number_format($validated['amount'], 0, ',', '.').' berhasil ditambahkan.',
                'data' => $reservation,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan pembayaran: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * PATCH /api/reservations/{reservation}/total
     */
    public function updateTotal(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'total_amount' => 'required|numeric|min:0',
        ]);

        $oldAmount = $reservation->total_amount;
        $reservation->total_amount = $validated['total_amount'];
        $reservation->save();

        return response()->json([
            'success' => true,
            'message' => 'Total reservasi berhasil diperbarui.',
            'data' => $reservation,
        ]);
    }

    /**
     * PATCH /api/reservations/{reservation}/room-rate
     */
    public function updateRoomRate(Request $request, Reservation $reservation)
    {
        if ($reservation->status === 'checked_out' || $reservation->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa mengubah harga kamar untuk reservasi yang sudah checkout / dibatalkan.',
            ], 422);
        }

        $validated = $request->validate([
            'custom_room_rate' => 'nullable|numeric|min:0',
        ]);

        $nights = $reservation->nights;

        if ($validated['custom_room_rate'] === null) {
            $reservation->custom_room_rate = null;
            $newTotal = ($reservation->room->price_per_night ?? 0) * $nights;
        } else {
            $reservation->custom_room_rate = $validated['custom_room_rate'];
            $newTotal = $validated['custom_room_rate'] * $nights;
        }

        $reservation->total_amount = $newTotal;
        $reservation->save();

        return response()->json([
            'success' => true,
            'message' => 'Harga kamar berhasil diperbarui.',
            'data' => $reservation,
        ]);
    }

    // ========== ROOMS ==========

    /**
     * GET /api/rooms
     * List semua kamar with room type info
     */
    public function roomsIndex(Request $request)
    {
        $query = Room::with('roomType');

        if ($request->get('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->get('room_type')) {
            $query->where('room_type_name', $request->get('room_type'));
        }

        $rooms = $query->orderBy('room_number')->get();

        // Attach room type description and other metadata
        $data = $rooms->map(function ($room) {
            return array_merge($room->toArray(), [
                'description' => $room->roomType?->description ?? '',
                'room_type_code' => $room->roomType?->code ?? '',
            ]);
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * GET /api/rooms/available
     * Cek kamar available untuk periode tertentu
     *
     * Query params: check_in (Y-m-d), check_out (Y-m-d)
     */
    public function availableRooms(Request $request)
    {
        $validated = $request->validate([
            'check_in' => 'required|date_format:Y-m-d',
            'check_out' => 'required|date_format:Y-m-d|after:check_in',
        ]);

        $checkIn = Carbon::parse($validated['check_in'])->setTime(14, 0)->format('Y-m-d H:i:s');
        $checkOut = Carbon::parse($validated['check_out'])->setTime(12, 0)->format('Y-m-d H:i:s');

        // Exclude rooms with active Out of Order for the requested period
        $oooRoomIds = OutOfOrder::where('status', OutOfOrder::STATUS_ACTIVE)
            ->where('start_date', '<=', $checkOut)
            ->where(function ($q) use ($checkIn) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $checkIn);
            })
            ->pluck('room_id')
            ->unique();

        $availableRooms = Room::with('roomType')
            ->whereNotIn('status', ['maintenance', 'out_of_order'])
            ->whereNotIn('id', $oooRoomIds)
            ->whereNotIn('id', function ($q) use ($checkIn, $checkOut) {
                $q->select('room_id')
                    ->from('reservations')
                    ->whereIn('status', ['pending', 'checked_in'])
                    ->where('check_in', '<', $checkOut)
                    ->where('check_out', '>', $checkIn);
            })
            ->orderBy('room_number')
            ->get();

        // Apply 25% limit per room type (floor, min 1) for public display
        $limitedRooms = app(AvailabilityService::class)->limitAvailablePerType($availableRooms);

        // Attach room type description
        $data = $limitedRooms->map(function ($room) {
            $roomType = $room->roomType;

            return array_merge($room->toArray(), [
                'description' => $roomType?->description ?? '',
                'room_type_code' => $roomType?->code ?? '',
            ]);
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'check_in' => $validated['check_in'],
                'check_out' => $validated['check_out'],
                'total_available' => $availableRooms->count(),
                'total_displayed' => $limitedRooms->count(),
            ],
        ]);
    }

    // ========== GUESTS ==========

    /**
     * GET /api/guests
     * List guests
     */
    public function guestsIndex(Request $request)
    {
        $query = Guest::query();

        if ($request->get('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('guest_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('id_number', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 15);
        $guests = $query->orderBy('guest_name')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $guests,
        ]);
    }

    /**
     * GET /api/reservations/checked-in
     * List reservations that are already checked-in.
     * Optional filters: room_number, guest_name, search
     */
    public function checkedIn(Request $request)
    {
        $query = Reservation::with(['guest', 'room'])->where('status', 'checked_in');

        if ($request->get('room_number')) {
            $roomNumber = $request->get('room_number');
            $query->whereHas('room', function ($q) use ($roomNumber) {
                $q->where('room_number', 'like', "%{$roomNumber}%");
            });
        }

        if ($request->get('guest_name')) {
            $guestName = $request->get('guest_name');
            $query->whereHas('guest', function ($q) use ($guestName) {
                $q->where('guest_name', 'like', "%{$guestName}%");
            });
        }

        if ($request->get('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('guest', function ($q) use ($search) {
                    $q->where('guest_name', 'like', "%{$search}%")
                        ->orWhere('id_number', 'like', "%{$search}%");
                })
                    ->orWhereHas('room', function ($q) use ($search) {
                        $q->where('room_number', 'like', "%{$search}%");
                    });
            });
        }

        $reservations = $query->orderBy('check_in', 'asc')->get()->map(function ($reservation) {
            return [
                'reservation_number' => $reservation->reservation_number,
                'guest_name' => $reservation->guest->guest_name ?? null,
                'room_number' => $reservation->room->room_number ?? null,
                'room_type_name' => $reservation->room->room_type_name ?? null,
                'check_in' => optional($reservation->check_in)->format('Y-m-d H:i:s'),
                'check_out' => optional($reservation->check_out)->format('Y-m-d H:i:s'),
                'status' => $reservation->status,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $reservations,
        ]);
    }

    // ========== STATS ==========

    /**
     * GET /api/stats
     * Dashboard statistics
     */
    public function stats()
    {
        $today = Carbon::today();

        $stats = [
            'reservations' => [
                'pending' => Reservation::where('status', 'pending')->count(),
                'checked_in' => Reservation::where('status', 'checked_in')->count(),
                'checked_out' => Reservation::where('status', 'checked_out')->count(),
                'cancelled' => Reservation::where('status', 'cancelled')->count(),
            ],
            'rooms' => [
                'total' => Room::whereNotIn('status', ['out_of_order'])->count(),
                'available' => Room::where('status', 'available')->count(),
                'occupied' => Room::where('status', 'occupied')->count(),
                'cleaning' => Room::where('status', 'cleaning')->count(),
                'maintenance' => Room::where('status', 'maintenance')->count(),
            ],
            'today' => [
                'checkins' => Reservation::where('status', 'pending')->whereDate('check_in', $today)->count(),
                'checkouts' => Reservation::where('status', 'checked_in')->whereDate('check_out', $today)->count(),
            ],
            'payments' => [
                'today_total' => Transaction::whereDate('created_at', $today)->sum('amount'),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    // ========== API KEY MANAGEMENT ==========

    /**
     * POST /api/v1/api-keys
     * Generate API Key baru untuk user yang sedang login
     */
    public function generateApiKey(Request $request)
    {
        $user = $request->user();

        // Generate random key
        $plainKey = 'hms_'.Str::random(40);

        // Simpan sebagai Sanctum token (hashed)
        $token = $user->createToken('api-key', ['*'], now()->addYear());

        // Kita perlu simpan plain key juga agar bisa dicocokkan
        // Override token hash dengan hash dari plain key
        $token->accessToken->forceFill([
            'token' => hash('sha256', $plainKey),
        ])->save();

        return response()->json([
            'success' => true,
            'message' => 'API Key berhasil dibuat. Simpan key ini, tidak bisa ditampilkan lagi.',
            'data' => [
                'api_key' => $plainKey,
                'name' => 'api-key',
                'created_at' => now()->toISOString(),
                'expires_at' => now()->addYear()->toISOString(),
            ],
        ], 201);
    }

    /**
     * GET /api/v1/api-keys
     * List API keys milik user
     */
    public function listApiKeys(Request $request)
    {
        $tokens = $request->user()->tokens()
            ->where('name', 'api-key')
            ->select('id', 'name', 'last_used_at', 'created_at', 'expires_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tokens,
        ]);
    }

    /**
     * DELETE /api/v1/api-keys/{id}
     * Revoke API key
     */
    public function revokeApiKey(Request $request, $id)
    {
        $deleted = $request->user()->tokens()
            ->where('id', $id)
            ->where('name', 'api-key')
            ->delete();

        if (! $deleted) {
            return response()->json([
                'success' => false,
                'message' => 'API Key tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'API Key berhasil dicabut.',
        ]);
    }
}
