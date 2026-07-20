<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingSyncService
{
    public function __construct(
        private BookingMapperService $mapper,
        private AvailabilityService $availability
    ) {}

    /**
     * Sync booking data to the existing reservation system.
     * Uses updateOrCreate based on ota_reservation_number.
     *
     * @param  array  $aiData  Raw AI-parsed data
     * @param  int|null  $roomId  Pre-checked available room ID (from availability check)
     * @return array{reservation: Reservation|null, action: string, success: bool, error?: string}
     */
    public function sync(array $aiData, ?int $roomId = null): array
    {
        // Validate required fields
        if (empty($aiData['reservation_id'])) {
            Log::error('BookingSync: Missing reservation_id from AI data');

            return ['reservation' => null, 'action' => 'none', 'success' => false, 'error' => 'Missing reservation_id'];
        }

        if (empty($aiData['guest_name'])) {
            Log::error('BookingSync: Missing guest_name from AI data');

            return ['reservation' => null, 'action' => 'none', 'success' => false, 'error' => 'Missing guest_name'];
        }

        if (empty($aiData['checkin_date']) || empty($aiData['checkout_date'])) {
            Log::error('BookingSync: Missing check-in or check-out date', [
                'reservation_id' => $aiData['reservation_id'],
            ]);

            return ['reservation' => null, 'action' => 'none', 'success' => false, 'error' => 'Missing dates'];
        }

        $mapped = $this->mapper->mapToReservation($aiData);

        // Use provided room ID (already availability-checked) or find one
        if (! $roomId) {
            $checkIn = Carbon::parse($aiData['checkin_date'])->setTime(14, 0);
            $checkOut = Carbon::parse($aiData['checkout_date'])->setTime(12, 0);
            $roomId = $this->findAvailableRoom($mapped['room_type_name'] ?? null, $checkIn, $checkOut);
        }

        try {
            return DB::transaction(function () use ($mapped, $roomId, $aiData) {
                // Find or create guest
                $guest = Guest::firstOrCreate(
                    ['guest_name' => $mapped['guest_name']],
                    [
                        'phone' => null,
                        'email' => null,
                        'address' => null,
                    ]
                );

                // Determine if this is new or existing
                $existing = Reservation::where('ota_reservation_number', $mapped['ota_reservation_number'])
                    ->first();

                $isCancelled = strtolower($aiData['status'] ?? '') === 'cancelled';
                $action = $existing
                    ? ($isCancelled ? 'cancelled' : 'updated')
                    : 'created';

                // For cancelled reservations, only update status
                if ($action === 'cancelled') {
                    $existing->update(['status' => 'cancelled']);
                    Log::info('BookingSync: Reservation cancelled', [
                        'reservation_number' => $existing->reservation_number,
                        'ota_reservation_number' => $mapped['ota_reservation_number'],
                    ]);

                    return ['reservation' => $existing, 'action' => 'cancelled', 'success' => true];
                }

                // Determine OTA payment status from AI data
                $isOtaPayment = $this->isOtaPaymentMethod($mapped['payment_method'] ?? '');
                $aiTotalPrice = $mapped['total_amount'] ?? 0;
                $otaPaidAmount = 0;
                $otaPaymentStatus = 'unpaid_ota';

                // Room rate fallback for ALL payment types:
                // If AI didn't provide price but we have a room, calculate from room rate
                if ($aiTotalPrice <= 0 && $roomId) {
                    $room = Room::find($roomId);
                    if ($room) {
                        $checkIn = Carbon::parse($mapped['check_in']);
                        $checkOut = Carbon::parse($mapped['check_out']);
                        $aiTotalPrice = $room->calculateTotalForRange($checkIn, $checkOut);
                    }
                }

                if ($isOtaPayment) {
                    $otaPaidAmount = $aiTotalPrice;
                    $otaPaymentStatus = 'paid_ota';
                }

                // Build reservation data
                // paid_amount = 0 — payment akan diinput via 1 form "Input Pembayaran"
                // ota_payment_status & ota_paid_amount = referensi dari email AI
                $reservationData = [
                    'guest_id' => $guest->id,
                    'check_in' => $mapped['check_in'],
                    'check_out' => $mapped['check_out'],
                    'number_of_cards' => $mapped['number_of_cards'],
                    'total_amount' => $aiTotalPrice,
                    'payment_method' => $mapped['payment_method'] ?? null,
                    'paid_date' => null,
                    'paid_amount' => 0,
                    'ota_payment_status' => $otaPaymentStatus,
                    'ota_paid_amount' => $otaPaidAmount,
                    'status' => $mapped['status'],
                    'notes' => $mapped['notes'],
                    'ota_source' => $mapped['ota_source'],
                    'created_by' => $existing?->created_by,
                ];

                if ($roomId) {
                    $reservationData['room_id'] = $roomId;
                }

                // For new reservation: set system defaults
                if (! $existing) {
                    $reservationData['created_by'] = 1; // system user
                    $reservationData['ota_reservation_number'] = $mapped['ota_reservation_number'];
                }

                // For modifications: update existing reservation
                if ($action === 'updated') {
                    // For OTA-paid bookings, total_amount wajib diisi.
                    // Untuk pay-at-hotel, 0 diperbolehkan (staff input manual nanti).
                    if (($reservationData['total_amount'] ?? 0) <= 0 && $isOtaPayment) {
                        Log::error('BookingSync: total_amount tidak valid untuk update OTA (0)', [
                            'ota_reservation_number' => $mapped['ota_reservation_number'],
                        ]);
                        return ['reservation' => null, 'action' => 'none', 'success' => false, 'error' => 'Total amount tidak valid (0). Periksa tanggal check-in/check-out.'];
                    }

                    if (($reservationData['total_amount'] ?? 0) <= 0 && ! $isOtaPayment) {
                        Log::warning('BookingSync: total_amount = 0 untuk pay-at-hotel update', [
                            'ota_reservation_number' => $mapped['ota_reservation_number'],
                            'note' => 'Staff perlu mengisi total_amount manual',
                        ]);
                    }

                    $existing->update($reservationData);

                    Log::info('BookingSync: Reservation updated', [
                        'reservation_number' => $existing->reservation_number,
                        'ota_reservation_number' => $mapped['ota_reservation_number'],
                        'total_amount' => $reservationData['total_amount'],
                        'payment_method' => $reservationData['payment_method'],
                        'paid_amount' => $reservationData['paid_amount'],
                    ]);

                    return ['reservation' => $existing->fresh(), 'action' => 'updated', 'success' => true];
                }

                // Validasi total_amount untuk reservasi baru
                // Untuk OTA-paid: wajib isi. Untuk pay-at-hotel: 0 diperbolehkan.
                if (($reservationData['total_amount'] ?? 0) <= 0 && $isOtaPayment) {
                    Log::error('BookingSync: total_amount tidak valid untuk reservasi OTA baru (0)', [
                        'ota_reservation_number' => $mapped['ota_reservation_number'],
                    ]);
                    return ['reservation' => null, 'action' => 'none', 'success' => false, 'error' => 'Total amount tidak valid (0). Periksa tanggal check-in/check-out.'];
                }

                if (($reservationData['total_amount'] ?? 0) <= 0 && ! $isOtaPayment) {
                    Log::warning('BookingSync: total_amount = 0 untuk pay-at-hotel baru', [
                        'ota_reservation_number' => $mapped['ota_reservation_number'],
                        'note' => 'Staff perlu mengisi total_amount manual',
                    ]);
                }

                // New reservation — use updateOrCreate with unique OTA number
                $reservation = Reservation::updateOrCreate(
                    ['ota_reservation_number' => $mapped['ota_reservation_number']],
                    $reservationData
                );

                // NOTE: Transaction TIDAK auto-create di sini.
                // Payment diinput via 1 form "Input Pembayaran" di halaman detail reservasi.
                // OTA status & nominal sudah tersimpan di reservation untuk referensi.

                Log::info('BookingSync: New reservation created', [
                    'reservation_number' => $reservation->reservation_number,
                    'ota_reservation_number' => $mapped['ota_reservation_number'],
                    'total_amount' => $aiTotalPrice,
                    'payment_method' => $mapped['payment_method'] ?? '',
                    'ota_payment_status' => $otaPaymentStatus,
                    'ota_paid_amount' => $otaPaidAmount,
                    'note' => 'Payment akan diinput via form Tambah Pembayaran',
                ]);

                return ['reservation' => $reservation, 'action' => 'created', 'success' => true];
            });
        } catch (\Exception $e) {
            Log::error('BookingSync: Transaction failed', [
                'error' => $e->getMessage(),
                'reservation_id' => $aiData['reservation_id'],
            ]);

            return ['reservation' => null, 'action' => 'none', 'success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Check if payment method is OTA-collected (money already received by OTA).
     */
    private function isOtaPaymentMethod(string $method): bool
    {
        return in_array($method, [
            'ota_tiket_com', 'ota_traveloka', 'tiket.com', 'traveloka.com', 'ota_payment',
        ]);
    }

    /**
     * Find an available room by type name for the given date range.
     * Uses AvailabilityService for back-to-back aware checking.
     * Returns null if no room available.
     */
    public function findAvailableRoom(?string $roomTypeName, Carbon $checkIn, Carbon $checkOut): ?int
    {
        // First try: find available rooms matching the type
        $availableRooms = $this->availability->getAvailableRooms($checkIn, $checkOut, $roomTypeName);

        if ($availableRooms->isNotEmpty()) {
            $room = $availableRooms->first();
            Log::info('BookingSync: Found available room by type', [
                'room_id' => $room->id,
                'room_number' => $room->room_number,
                'room_type' => $roomTypeName,
            ]);

            return $room->id;
        }

        // Second try: any available room (different type)
        $anyAvailable = $this->availability->getAvailableRooms($checkIn, $checkOut);

        if ($anyAvailable->isNotEmpty()) {
            $room = $anyAvailable->first();
            Log::info('BookingSync: No exact type match, using available room', [
                'room_id' => $room->id,
                'room_number' => $room->room_number,
                'requested_type' => $roomTypeName,
            ]);

            return $room->id;
        }

        Log::warning('BookingSync: No available rooms for date range', [
            'check_in' => $checkIn->toDateTimeString(),
            'check_out' => $checkOut->toDateTimeString(),
            'room_type' => $roomTypeName,
        ]);

        return null;
    }
}
