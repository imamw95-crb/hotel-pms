<?php

namespace App\Services;

use App\Models\{Reservation, Guest, Room, Allotment};
use App\Models\PaymentMethod;
use App\Models\Transaction;
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

        // ── Semua operasi dalam satu transaksi agar lockForUpdate() benar-benar mengunci ──
        return DB::transaction(function () use ($data, $checkIn, $checkOut, $room) {
            // Lock the room row to prevent race conditions.
            // lockForUpdate() re-fetches the row WITH a row-level lock inside the transaction.
            $room->lockForUpdate();

            if (! $room->isAvailable($checkIn, $checkOut)) {
                throw new Exception("Kamar {$room->room_number} tidak tersedia untuk periode tersebut.");
            }

            // Determine if we need to track allotment (API / website)
            $otaSource = $data['ota_source'] ?? 'api';
            $trackAllotment = in_array($otaSource, [Allotment::CHANNEL_API, Allotment::CHANNEL_WEBSITE, null, '']);
            $roomTypeId = $room->room_type_id;

            if ($roomTypeId && $trackAllotment) {
                // Lock allotment rows for the date range (inside transaction = lock really holds)
                Allotment::where('room_type_id', $roomTypeId)
                    ->whereBetween('date', [$checkIn->toDateString(), $checkOut->copy()->subDay()->toDateString()])
                    ->lockForUpdate()
                    ->get();

                $unavailable = Allotment::checkAvailabilityInRange($roomTypeId, $checkIn, $checkOut, Allotment::CHANNEL_API);
                if (! empty($unavailable)) {
                    throw new Exception('Allotment tidak tersedia pada tanggal: '.implode(', ', $unavailable));
                }
            }

            // Guest handling — updateOrCreate with composite key (name + phone) to prevent collision
            $guest = Guest::updateOrCreate(
                [
                    'guest_name' => $data['guest_name'],
                    'phone'      => $data['guest_phone'] ?? null,
                ],
                [
                    'email'           => $data['guest_email'] ?? null,
                    'id_number'       => $data['guest_id_number'] ?? null,
                    'place_of_birth'  => $data['guest_place_of_birth'] ?? null,
                    'date_of_birth'   => $data['guest_date_of_birth'] ?? null,
                ]
            );

            // Calculate total amount if not provided
            $totalAmount = $data['total_amount'] ?? 0;
            if ($totalAmount <= 0) {
                $totalAmount = $room->calculateTotalForRange($checkIn, $checkOut);
            }

            if ($totalAmount <= 0) {
                throw new Exception('Total reservasi tidak valid. Periksa tanggal check-in dan check-out.');
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
    public function cancel(Reservation $reservation, bool $confirmRefund = false): void
    {
        if ($reservation->status === 'checked_in') {
            throw new Exception('Reservasi yang sudah check‑in tidak dapat dibatalkan.');
        }
        if ($reservation->status === 'cancelled') {
            throw new Exception('Reservasi sudah dibatalkan.');
        }

        // ── Cek pembayaran sebelum membatalkan ──
        // Jika sudah ada pembayaran (DP/lunas), wajib konfirmasi dulu bahwa refund sudah diurus.
        if ($reservation->paid_amount > 0 && ! $confirmRefund) {
            $sisa = max(0, $reservation->total_amount - $reservation->paid_amount);
            throw new Exception(
                'Booking sudah dibayar Rp '.number_format($reservation->paid_amount, 0, ',', '.')
                .' dari Rp '.number_format($reservation->total_amount, 0, ',', '.')
                .' (sisa Rp '.number_format($sisa, 0, ',', '.').'). '
                .'Konfirmasi dulu bahwa refund sudah diurus sebelum membatalkan.'
            );
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

        // Refund dicatat sebagai transaksi negatif (basis kas) supaya uang yang
        // dikembalikan mengurangi pendapatan periode refund, bukan menghapus
        // riwayat pembayaran yang sudah dilaporkan.
        if ($confirmRefund && $reservation->paid_amount > 0) {
            $this->recordRefund($reservation);
        }

        $reservation->update(['status' => 'cancelled']);
        Log::info('Reservation cancelled', ['id' => $reservation->id, 'user_id' => auth()->id()]);
    }

    /**
     * Catat refund pembatalan sebagai transaksi NEGATIF di periode berjalan.
     *
     * Nominal tidak boleh melebihi uang yang benar-benar diterima (ledger),
     * dan nominal historis tanpa baris transaksi tetap dipertahankan.
     */
    public function recordRefund(Reservation $reservation, ?float $amount = null): ?Transaction
    {
        $recorded = $reservation->recordedPaidAmount();
        $refund = $amount ?? $recorded;
        $refund = min($refund, $recorded);

        if ($refund <= 0) {
            return null;
        }

        $legacyPaid = $reservation->unrecordedPaidAmount();
        $method = $reservation->payment_method ?: 'cash';

        $transaction = Transaction::create([
            'transaction_number' => 'TRX-'.strtoupper(uniqid()),
            'reservation_id' => $reservation->id,
            'type' => 'refund',
            'amount' => -1 * $refund,
            'payment_method' => $method,
            'source_type' => PaymentMethod::where('slug', $method)->value('source_type'),
            'notes' => 'Refund pembatalan '.$reservation->reservation_number,
            'created_by' => auth()->id() ?? 1,
        ]);

        $reservation->syncPaidAmount($legacyPaid);
        if ($reservation->paid_amount < $reservation->total_amount) {
            $reservation->paid_date = null;
        }
        $reservation->saveQuietly();

        return $transaction;
    }

    // Additional methods (checkin, checkout, changeRoom) can be added similarly.
}
