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
     * @param array $aiData Raw AI-parsed data
     * @param int|null $roomId Pre-checked available room ID (from availability check)
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
        if (!$roomId) {
            $checkIn  = Carbon::parse($aiData['checkin_date'])->setTime(14, 0);
            $checkOut = Carbon::parse($aiData['checkout_date'])->setTime(12, 0);
            $roomId = $this->findAvailableRoom($mapped['room_type_name'] ?? null, $checkIn, $checkOut);
        }

        try {
            return DB::transaction(function () use ($mapped, $roomId, $aiData) {
                // Find or create guest
                $guest = Guest::firstOrCreate(
                    ['guest_name' => $mapped['guest_name']],
                    [
                        'phone'  => null,
                        'email'  => null,
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

                // Build reservation data — ALL payment fields included
                $reservationData = [
                    'guest_id'        => $guest->id,
                    'check_in'        => $mapped['check_in'],
                    'check_out'       => $mapped['check_out'],
                    'number_of_cards' => $mapped['number_of_cards'],
                    'total_amount'    => $mapped['total_amount'] ?? 0,
                    'payment_method'  => $mapped['payment_method'] ?? null,
                    'paid_date'       => $mapped['paid_date'] ?? null,
                    'paid_amount'     => $mapped['paid_amount'] ?? 0,
                    'status'          => $mapped['status'],
                    'notes'           => $mapped['notes'],
                    'ota_source'      => $mapped['ota_source'],
                    'created_by'      => $existing?->created_by, // preserve original creator
                ];

                if ($roomId) {
                    $reservationData['room_id'] = $roomId;
                }

                // For new reservation: set system defaults
                if (!$existing) {
                    $reservationData['created_by'] = 1; // system user
                    $reservationData['ota_reservation_number'] = $mapped['ota_reservation_number'];

                    // Fallback: calculate from room price if AI didn't provide total
                    $aiTotal = $mapped['total_amount'] ?? 0;
                    if ($aiTotal <= 0 && $roomId) {
                        $room = Room::find($roomId);
                        if ($room) {
                            $checkIn  = Carbon::parse($mapped['check_in']);
                            $checkOut = Carbon::parse($mapped['check_out']);
                            $reservationData['total_amount'] = $room->calculateTotalForRange($checkIn, $checkOut);
                        }
                    }
                }

                // For modifications: update existing reservation
                if ($action === 'updated') {
                    $existing->update($reservationData);
                    Log::info('BookingSync: Reservation updated', [
                        'reservation_number'     => $existing->reservation_number,
                        'ota_reservation_number' => $mapped['ota_reservation_number'],
                        'total_amount'           => $reservationData['total_amount'],
                        'payment_method'         => $reservationData['payment_method'],
                        'paid_amount'            => $reservationData['paid_amount'],
                    ]);
                    return ['reservation' => $existing, 'action' => 'updated', 'success' => true];
                }

                // New reservation — use updateOrCreate with unique OTA number
                $reservation = Reservation::updateOrCreate(
                    ['ota_reservation_number' => $mapped['ota_reservation_number']],
                    $reservationData
                );

                // Create transaction record for OTA payments
                $paidAmount   = $mapped['paid_amount'] ?? 0;
                $paymentMethod = $mapped['payment_method'] ?? '';
                if ($paidAmount > 0 && !empty($paymentMethod)) {
                    \App\Models\Transaction::create([
                        'reservation_id' => $reservation->id,
                        'type'           => 'ota_payment',
                        'amount'         => $paidAmount,
                        'payment_method' => $paymentMethod,
                        'notes'          => "Auto-paid via {$paymentMethod} — OTA Email Autopilot",
                        'created_by'     => 1,
                    ]);
                    Log::info('BookingSync: OTA payment transaction created', [
                        'reservation_number' => $reservation->reservation_number,
                        'amount'             => $paidAmount,
                        'payment_method'     => $paymentMethod,
                    ]);
                }

                Log::info('BookingSync: New reservation created', [
                    'reservation_number'     => $reservation->reservation_number,
                    'ota_reservation_number' => $mapped['ota_reservation_number'],
                    'total_amount'           => $reservationData['total_amount'],
                    'payment_method'         => $paymentMethod,
                    'paid_amount'            => $paidAmount,
                    'paid_date'              => $reservationData['paid_date'],
                ]);

                return ['reservation' => $reservation, 'action' => 'created', 'success' => true];
            });
        } catch (\Exception $e) {
            Log::error('BookingSync: Transaction failed', [
                'error'          => $e->getMessage(),
                'reservation_id' => $aiData['reservation_id'],
            ]);
            return ['reservation' => null, 'action' => 'none', 'success' => false, 'error' => $e->getMessage()];
        }
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
                'room_id'    => $room->id,
                'room_number' => $room->room_number,
                'room_type'  => $roomTypeName,
            ]);
            return $room->id;
        }

        // Second try: any available room (different type)
        $anyAvailable = $this->availability->getAvailableRooms($checkIn, $checkOut);

        if ($anyAvailable->isNotEmpty()) {
            $room = $anyAvailable->first();
            Log::info('BookingSync: No exact type match, using available room', [
                'room_id'       => $room->id,
                'room_number'   => $room->room_number,
                'requested_type' => $roomTypeName,
            ]);
            return $room->id;
        }

        Log::warning('BookingSync: No available rooms for date range', [
            'check_in'  => $checkIn->toDateTimeString(),
            'check_out' => $checkOut->toDateTimeString(),
            'room_type' => $roomTypeName,
        ]);

        return null;
    }
}
