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
     *   "status": "confirmed",
     *   "ota_source": "tiket.com"
     * }
     *
     * Maps to existing Reservation model fillable:
     * - ota_reservation_number
     * - guest_name (via Guest model)
     * - check_in
     * - check_out
     * - number_of_cards (guest_count)
     * - status (mapped: confirmed→pending, cancelled→cancelled, modified→pending)
     * - total_amount (estimated from room type price)
     * - notes (OTA source info)
     */
    public function mapToReservation(array $aiData, int $defaultRoomId = null): array
    {
        $mapped = [
            'ota_reservation_number' => $aiData['reservation_id'] ?? null,
            'guest_name'             => $this->sanitizeString($aiData['guest_name'] ?? ''),
            'check_in'               => $this->formatDate($aiData['checkin_date'] ?? null, '12:00'),
            'check_out'              => $this->formatDate($aiData['checkout_date'] ?? null, '12:00'),
            'room_type_name'         => $aiData['room_type'] ?? null,
            'number_of_cards'        => (int) ($aiData['guest_count'] ?? 1),
            'status'                 => $this->mapStatus($aiData['status'] ?? 'confirmed'),
            'ota_source'             => $aiData['ota_source'] ?? '',
            'notes'                  => $this->buildNotes($aiData),
        ];

        // If we have a specific room ID from matching, include it
        if ($defaultRoomId) {
            $mapped['room_id'] = $defaultRoomId;
        }

        Log::info('AI data mapped to reservation format', [
            'ota_reservation_number' => $mapped['ota_reservation_number'],
            'guest_name'             => $mapped['guest_name'],
            'check_in'               => $mapped['check_in'],
            'check_out'              => $mapped['check_out'],
            'status'                 => $mapped['status'],
        ]);

        return $mapped;
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
     * Build notes from OTA data.
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

        $parts[] = "Auto-imported via OTA Email Autopilot";

        return implode(' | ', $parts);
    }
}
