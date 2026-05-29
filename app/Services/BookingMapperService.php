<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class BookingMapperService
{
    /**
     * Map AI-parsed data to the existing reservation table format.
     *
     * AI output format:
     * {
     *   "reservation_id": "OTA-12345",
     *   "guest_name": "Budi Santoso",
     *   "checkin_date": "2026-06-02",
     *   "checkout_date": "2026-06-04",
     *   "room_type": "Deluxe",
     *   "guest_count": 2,
     *   "total_price": 500000,
     *   "payment_method": "tiket.com",
     *   "payment_date": "2026-06-01",
     *   "status": "confirmed",
     *   "ota_source": "tiket.com"
     * }
     *
     * Maps to existing Reservation model fillable:
     * - ota_reservation_number, guest_name, check_in, check_out
     * - number_of_cards (guest_count)
     * - total_amount (from AI total_price, fallback to room price)
     * - payment_method (from AI, fallback to ota_source)
     * - paid_date (from AI payment_date)
     * - paid_amount (from AI total_price if OTA-paid)
     * - status, ota_source, notes
     */
    public function mapToReservation(array $aiData, int $defaultRoomId = null): array
    {
        $otaSource      = $aiData['ota_source'] ?? '';
        $aiPaymentMethod = $this->sanitizePaymentMethod($aiData['payment_method'] ?? '', $otaSource);
        $aiTotalPrice   = $this->parseAmount($aiData['total_price'] ?? 0);
        $aiPaymentDate  = $this->formatDate($aiData['payment_date'] ?? $aiData['checkin_date'] ?? null, '00:00');
        $isOtaPayment   = $this->isOtaPaymentMethod($aiPaymentMethod);

        // Determine OTA payment status
        $otaPaymentStatus = 'unpaid_ota';
        $otaPaidAmount = 0;
        if ($isOtaPayment && $aiTotalPrice > 0) {
            $otaPaymentStatus = 'paid_ota';
            $otaPaidAmount = $aiTotalPrice;
        }

        $mapped = [
            'ota_reservation_number' => $aiData['reservation_id'] ?? null,
            'guest_name'             => $this->sanitizeString($aiData['guest_name'] ?? ''),
            'check_in'               => $this->formatDate($aiData['checkin_date'] ?? null, '14:00'),
            'check_out'              => $this->formatDate($aiData['checkout_date'] ?? null, '12:00'),
            'room_type_name'         => $aiData['room_type'] ?? null,
            'number_of_cards'        => (int) ($aiData['guest_count'] ?? 1),
            'total_amount'           => $aiTotalPrice,
            'payment_method'         => $aiPaymentMethod,
            'paid_date'              => $aiPaymentDate,
            'paid_amount'            => $otaPaidAmount,
            'ota_payment_status'     => $otaPaymentStatus,
            'ota_paid_amount'        => $otaPaidAmount,
            'status'                 => $this->mapStatus($aiData['status'] ?? 'confirmed'),
            'ota_source'             => $otaSource,
            'notes'                  => $this->buildNotes($aiData),
        ];

        if ($defaultRoomId) {
            $mapped['room_id'] = $defaultRoomId;
        }

        Log::info('AI data mapped to reservation format', [
            'ota_reservation_number' => $mapped['ota_reservation_number'],
            'guest_name'             => $mapped['guest_name'],
            'check_in'               => $mapped['check_in'],
            'check_out'              => $mapped['check_out'],
            'total_amount'           => $mapped['total_amount'],
            'payment_method'         => $mapped['payment_method'],
            'paid_date'              => $mapped['paid_date'],
            'paid_amount'            => $mapped['paid_amount'],
            'status'                 => $mapped['status'],
        ]);

        return $mapped;
    }

    /**
     * Sanitize and normalize payment method from AI.
     */
    private function sanitizePaymentMethod(string $paymentMethod, string $otaSource): string
    {
        $method = strtolower(trim($paymentMethod));

        $validMethods = [
            'tiket.com', 'traveloka.com', 'ota_payment',
            'bank_transfer', 'credit_card', 'debit_card', 'cash',
            'virtual_account', 'ewallet', 'qris',
        ];

        if (in_array($method, $validMethods)) {
            return $method;
        }

        return match (true) {
            str_contains($method, 'tiket')     => 'tiket.com',
            str_contains($method, 'traveloka') => 'traveloka.com',
            str_contains($method, 'transfer')  => 'bank_transfer',
            str_contains($method, 'credit')    => 'credit_card',
            str_contains($method, 'debit')     => 'debit_card',
            str_contains($method, 'virtual')   => 'virtual_account',
            str_contains($method, 'ewallet')
                || str_contains($method, 'ovo')
                || str_contains($method, 'gopay')
                || str_contains($method, 'dana')  => 'ewallet',
            str_contains($method, 'qris')      => 'qris',
            str_contains($method, 'cash')      => 'cash',
            !empty($otaSource)                 => $otaSource,
            default                            => 'ota_payment',
        };
    }

    /**
     * Check if payment method is OTA-collected (money already received by OTA).
     */
    private function isOtaPaymentMethod(string $method): bool
    {
        return in_array($method, [
            'tiket.com', 'traveloka.com', 'ota_payment',
        ]);
    }

    /**
     * Parse amount from AI (handles "Rp 500.000", "500000", "500,000", etc).
     */
    private function parseAmount(mixed $value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        if (is_string($value)) {
            $cleaned = preg_replace('/[^\d,.-]/', '', $value);
            if (str_contains($cleaned, ',') && str_contains($cleaned, '.')) {
                $cleaned = str_replace('.', '', $cleaned);
                $cleaned = str_replace(',', '.', $cleaned);
            } elseif (str_contains($cleaned, ',')) {
                $parts = explode(',', $cleaned);
                if (strlen(end($parts)) <= 2) {
                    $cleaned = str_replace(',', '.', $cleaned);
                } else {
                    $cleaned = str_replace(',', '', $cleaned);
                }
            }
            return (float) $cleaned;
        }

        return 0.0;
    }

    /**
     * Map AI status to existing reservation status.
     */
    private function mapStatus(string $aiStatus): string
    {
        return match (strtolower($aiStatus)) {
            'confirmed'  => 'pending',
            'cancelled'  => 'cancelled',
            'modified'   => 'pending',
            default      => 'pending',
        };
    }

    /**
     * Format date string to datetime with hotel time.
     */
    private function formatDate(?string $date, string $time = '12:00'): ?string
    {
        if (!$date) {
            return null;
        }

        try {
            $parsed = \Carbon\Carbon::parse($date);
            $parsed->setTime((int) substr($time, 0, 2), (int) substr($time, 3, 2), 0);
            return $parsed->toDateTimeString();
        } catch (\Exception $e) {
            Log::warning("Failed to parse date: {$date}");
            return null;
        }
    }

    /**
     * Sanitize string input.
     */
    private function sanitizeString(string $value): string
    {
        return trim(strip_tags($value));
    }

    /**
     * Build notes from OTA data including payment info.
     */
    private function buildNotes(array $aiData): string
    {
        $parts = [];

        if (!empty($aiData['ota_source'])) {
            $parts[] = "OTA Source: {$aiData['ota_source']}";
        }

        if (!empty($aiData['reservation_id'])) {
            $parts[] = "OTA Reservation #: {$aiData['reservation_id']}";
        }

        if (!empty($aiData['room_type'])) {
            $parts[] = "Room Type: {$aiData['room_type']}";
        }

        if (!empty($aiData['total_price'])) {
            $parts[] = "Total: " . number_format($this->parseAmount($aiData['total_price']), 0, ',', '.');
        }

        if (!empty($aiData['payment_method'])) {
            $parts[] = "Payment: {$aiData['payment_method']}";
        }

        if (!empty($aiData['payment_date'])) {
            $parts[] = "Paid: {$aiData['payment_date']}";
        }

        $parts[] = "Auto-imported via OTA Email Autopilot";

        return implode(' | ', $parts);
    }
}
