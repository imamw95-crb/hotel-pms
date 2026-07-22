<?php

namespace App\Services;

use App\Models\Reservation;

class InvoiceSignatureService
{
    protected string $secretKey;

    public function __construct()
    {
        $this->secretKey = config('app.invoice_secret') ?: config('app.key');
    }

    /**
     * Generate HMAC signature dari data invoice
     */
    public function generate(Reservation $reservation): string
    {
        $data = $this->buildData($reservation);
        return hash_hmac('sha256', json_encode($data), $this->secretKey);
    }

    /**
     * Verifikasi signature cocok dengan data saat ini
     */
    public function verify(Reservation $reservation, string $signature): bool
    {
        return hash_equals(
            $this->generate($reservation),
            $signature
        );
    }

    /**
     * Ambil data invoice yang relevan untuk signature
     */
    protected function buildData(Reservation $reservation): array
    {
        return [
            'id'                 => $reservation->id,
            'reservation_number' => $reservation->reservation_number,
            'total_amount'       => $reservation->total_amount,
            'paid_amount'        => $reservation->paid_amount,
            'guest_name'         => $reservation->guest?->guest_name,
            'room_number'        => $reservation->room?->room_number,
            'check_in'           => $reservation->check_in?->format('Y-m-d H:i'),
            'check_out'          => $reservation->check_out?->format('Y-m-d H:i'),
            'nights'             => $reservation->nights,
            'status'             => $reservation->status,
        ];
    }
}
