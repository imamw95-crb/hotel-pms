<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingSyncService
{
    public function __construct(
        private BookingMapperService $mapper
    ) {}

    /**
     * Sync booking data to the existing reservation system.
     * Uses updateOrCreate based on ota_reservation_number.
     *
     * @param array $aiData Raw AI-parsed data
     * @return array{reservation: Reservation|null, action: string, success: bool}
     */
    public function sync(array $aiData): array
    {
        // Validate required fields
        if (empty($aiData['reservation_id'])) {
            Log::error('BookingSync: Missing reservation_id from AI data');
            return ['reservation' => null, 'action' => 'none', 'success' => false];
        }

        if (empty($aiData['guest_name'])) {
            Log::error('BookingSync: Missing guest_name from AI data');
            return ['reservation' => null, 'action' => 'none', 'success' => false];
        }

        if (empty($aiData['checkin_date']) || empty($aiData['checkout_date'])) {
            Log::error('BookingSync: Missing check-in or check-out date', [
                'reservation_id' => $aiData['reservation_id'],
            ]);
            return ['reservation' => null, 'action' => 'none', 'success' => false];
        }

        $mapped = $this->mapper->mapToReservation($aiData);

        // Try to find a matching room by room type name
        $roomId = $this->findRoomByTypeName($mapped['room_type_name'] ?? null);

        if (!$roomId) {
            Log::warning('BookingSync: No room found for type', [
                'room_type'      => $mapped['room_type_name'],
                'reservation_id' => $mapped['ota_reservation_number'],
            ]);
            // We still proceed — room can be assigned manually by Front Office
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

                $action = $existing ? ($aiData['status'] ?? '') === 'cancelled' ? 'cancelled' : 'updated' : 'created';

                // Build reservation data
                $reservationData = [
                    'guest_id'      => $guest->id,
                    'check_in'      => $mapped['check_in'],
                    'check_out'     => $mapped['check_out'],
                    'number_of_cards' => $mapped['number_of_cards'],
                    'status'        => $mapped['status'],
                    'notes'         => $mapped['notes'],
                    'created_by'    => $existing?->created_by, // preserve original creator
                ];

                if ($roomId) {
                    $reservationData['room_id'] = $roomId;
                }

                // For new reservations, set defaults
                if (!$existing) {
                    $reservationData['total_amount'] = 0;
                    $reservationData['paid_amount']  = 0;
                    $reservationData['created_by']   = 1; // system user
                }

                // For cancelled reservations, only update status
                if ($action === 'cancelled') {
                    $existing->update(['status' => 'cancelled']);
                    Log::info('BookingSync: Reservation cancelled', [
                        'reservation_number' => $existing->reservation_number,
                        'ota_reservation_number' => $mapped['ota_reservation_number'],
                    ]);
                    return ['reservation' => $existing, 'action' => 'cancelled', 'success' => true];
                }

                // For modifications, update existing
                if ($action === 'updated') {
                    $existing->update($reservationData);
                    Log::info('BookingSync: Reservation updated', [
                        'reservation_number' => $existing->reservation_number,
                        'ota_reservation_number' => $mapped['ota_reservation_number'],
                    ]);
                    return ['reservation' => $existing, 'action' => 'updated', 'success' => true];
                }

                // New reservation — use updateOrCreate
                $reservationData['ota_reservation_number'] = $mapped['ota_reservation_number'];

                $reservation = Reservation::updateOrCreate(
                    ['ota_reservation_number' => $mapped['ota_reservation_number']],
                    $reservationData
                );

                Log::info('BookingSync: New reservation created', [
                    'reservation_number' => $reservation->reservation_number,
                    'ota_reservation_number' => $mapped['ota_reservation_number'],
                ]);

                return ['reservation' => $reservation, 'action' => 'created', 'success' => true];
            });
        } catch (\Exception $e) {
            Log::error('BookingSync: Transaction failed', [
                'error'          => $e->getMessage(),
                'reservation_id' => $aiData['reservation_id'],
            ]);
            return ['reservation' => null, 'action' => 'none', 'success' => false];
        }
    }

    /**
     * Find a room by type name. Returns first available matching room.
     */
    private function findRoomByTypeName(?string $roomTypeName): ?int
    {
        if (!$roomTypeName) {
            return null;
        }

        $room = Room::where('room_type_name', 'like', "%{$roomTypeName}%")
            ->where('status', '!=', 'maintenance')
            ->first();

        return $room?->id;
    }
}
