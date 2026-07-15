<?php

namespace App\Services;

use App\Models\{Reservation, Guest, Room, Allotment};
use App\Jobs\WebBookingCreatedJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ReservationService
{
    /**
     * Create a new reservation.
     *
     * @param array $data Validated data from ReservationStoreRequest
     * @return Reservation
     * @throws Exception
     */
    public function create(array $data): Reservation
    {
        $checkIn  = Carbon::parse($data['check_in'])->setTime(config('hotel.check_in_time_hour'), config('hotel.check_in_time_minute'));
        $checkOut = Carbon::parse($data['check_out'])->setTime(config('hotel.check_out_time_hour'), config('hotel.check_out_time_minute'));

        $room = Room::findOrFail($data['room_id']);

        // Lock the room row to avoid race conditions on availability check
        $room->lockForUpdate();

        if (! $room->isAvailable($checkIn, $checkOut)) {
            throw new Exception("Kamar {$room->room_number} tidak tersedia untuk periode tersebut.");
        }

        // Determine if we need to track allotment (API / website)
        $otaSource = $data['ota_source'] ?? 'api';
        $trackAllotment = in_array($otaSource, [Allotment::CHANNEL_API, Allotment::CHANNEL_WEBSITE, null, '']);
        $roomTypeId = $room->room_type_id;

        if ($roomTypeId && $trackAllotment) {
            // Lock allotment rows for the date range
            Allotment::where('room_type_id', $roomTypeId)
                ->whereBetween('date', [$checkIn->toDateString(), $checkOut->subDay()->toDateString()])
                ->lockForUpdate()
                ->get();

            $unavailable = Allotment::checkAvailabilityInRange($roomTypeId, $checkIn, $checkOut, Allotment::CHANNEL_API);
            if (! empty($unavailable)) {
                throw new Exception('Allotment tidak tersedia pada tanggal: '.implode(', ', $unavailable));
            }
        }

        return DB::transaction(function () use ($data, $checkIn, $checkOut, $room, $trackAllotment, $roomTypeId, $otaSource) {
            // Guest handling
            $guest = Guest::firstOrCreate(
                ['guest_name' => $data['guest_name']],
                [
                    'phone'     => $data['guest_phone'] ?? null,
                    'email'     => $data['guest_email'] ?? null,
                    'id_number' => $data['guest_id_number'] ?? null,
                ]
            );

            // Calculate total amount if not provided
            $totalAmount = $data['total_amount'] ?? 0;
            if ($totalAmount <= 0) {
                $totalAmount = $room->calculateTotalForRange($checkIn, $checkOut);
            }

            $isWebsite = $otaSource === Allotment::CHANNEL_WEBSITE;

            $reservation = Reservation::create([
                'guest_id'                => $guest->id,
                'room_id'                 => $room->id,
                'check_in'                => $checkIn,
                'check_out'               => $checkOut,
                'number_of_cards'         => $data['guest_count'] ?? 1,
                'total_amount'            => $totalAmount,
                'payment_method'          => $data['payment_method'] ?? ($isWebsite ? 'transfer_bca' : 'cash'),
                'paid_amount'             => 0,
                'status'                  => $isWebsite ? 'menunggu_pembayaran' : 'pending',
                'notes'                   => $data['notes'] ?? null,
                'ota_source'              => $otaSource,
                'ota_payment_status'      => $isWebsite ? 'pending' : null,
                'ota_reservation_number'  => $data['ota_reservation_number'] ?? null,
                'room_type_name'          => $room->room_type_name,
                'created_by'              => auth()->id(),
            ]);

            // Increment allotment booked count if needed
            if ($roomTypeId && $trackAllotment) {
                Allotment::incrementBooked($roomTypeId, $checkIn, $checkOut, Allotment::CHANNEL_API);
            }

            // Dispatch notification job
            if (! empty($otaSource) && ! in_array($otaSource, [Allotment::CHANNEL_WEBSITE, Allotment::CHANNEL_API])) {
                WebBookingCreatedJob::dispatch($reservation, $otaSource);
            } else {
                // For API / website bookings, create notification directly
                try {
                    app(\App\Services\BookingNotificationService::class)->webBookingCreated($reservation);
                } catch (\Exception $e) {
                    Log::error('Failed to create booking notification', [
                        'reservation_id' => $reservation->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('Reservation created', ['id' => $reservation->id, 'user_id' => auth()->id()]);

            return $reservation;
        });
    }

    /**
     * Cancel a reservation.
     */
    public function cancel(Reservation $reservation): void
    {
        if ($reservation->status === 'checked_in') {
            throw new Exception('Reservasi yang sudah check‑in tidak dapat dibatalkan.');
        }
        if ($reservation->status === 'cancelled') {
            throw new Exception('Reservasi sudah dibatalkan.');
        }

        $trackAllotment = in_array($reservation->ota_source, [Allotment::CHANNEL_API, Allotment::CHANNEL_WEBSITE, null, '']);
        if ($reservation->room->room_type_id && $trackAllotment) {
            Allotment::decrementBooked(
                $reservation->room->room_type_id,
                $reservation->check_in,
                $reservation->check_out,
                Allotment::CHANNEL_API
            );
        }

        $reservation->update(['status' => 'cancelled']);
        Log::info('Reservation cancelled', ['id' => $reservation->id, 'user_id' => auth()->id()]);
    }

    // Additional methods (checkin, checkout, changeRoom) can be added similarly.
}
