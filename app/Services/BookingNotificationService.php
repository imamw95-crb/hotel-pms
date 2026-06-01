<?php

namespace App\Services;

use App\Models\BookingNotification;
use App\Models\Reservation;
use Carbon\Carbon;

/**
 * Service for creating booking notifications persistently in the database.
 * Replaces the old cache-based notification mechanism.
 */
class BookingNotificationService
{
    /**
     * Create notification for a new OTA booking.
     */
    public function otaBookingCreated(Reservation $reservation, array $otaData, string $otaSource): BookingNotification
    {
        $message = sprintf(
            '🆕 New OTA booking from %s: %s (Ref: %s, Check-in: %s)',
            $otaSource,
            $otaData['guest_name'] ?? $reservation->guest?->guest_name ?? 'Unknown',
            $otaData['reservation_id'] ?? $reservation->ota_reservation_number ?? '-',
            $reservation->check_in ? Carbon::parse($reservation->check_in)->format('d/m/Y') : '-'
        );

        return BookingNotification::create([
            'type' => 'ota_booking',
            'action' => 'created',
            'reservation_id' => $reservation->id,
            'guest_name' => $otaData['guest_name'] ?? $reservation->guest?->guest_name ?? 'Unknown',
            'room_number' => $reservation->room?->room_number,
            'ota_source' => $otaSource,
            'ota_reservation_number' => $otaData['reservation_id'] ?? $reservation->ota_reservation_number,
            'message' => $message,
        ]);
    }

    /**
     * Create notification for an OTA booking update.
     */
    public function otaBookingUpdated(Reservation $reservation, array $otaData, string $otaSource): BookingNotification
    {
        $message = sprintf(
            '✏️ OTA booking updated from %s: %s (Ref: %s)',
            $otaSource,
            $otaData['guest_name'] ?? $reservation->guest?->guest_name ?? 'Unknown',
            $otaData['reservation_id'] ?? $reservation->ota_reservation_number ?? '-'
        );

        return BookingNotification::create([
            'type' => 'ota_booking',
            'action' => 'updated',
            'reservation_id' => $reservation->id,
            'guest_name' => $otaData['guest_name'] ?? $reservation->guest?->guest_name ?? 'Unknown',
            'room_number' => $reservation->room?->room_number,
            'ota_source' => $otaSource,
            'ota_reservation_number' => $otaData['reservation_id'] ?? $reservation->ota_reservation_number,
            'message' => $message,
        ]);
    }

    /**
     * Create notification for an OTA cancellation.
     */
    public function otaBookingCancelled(Reservation $reservation, array $otaData, string $otaSource): BookingNotification
    {
        $message = sprintf(
            '❌ OTA booking cancelled from %s: %s (Ref: %s)',
            $otaSource,
            $otaData['guest_name'] ?? $reservation->guest?->guest_name ?? 'Unknown',
            $otaData['reservation_id'] ?? $reservation->ota_reservation_number ?? '-'
        );

        return BookingNotification::create([
            'type' => 'ota_booking',
            'action' => 'cancelled',
            'reservation_id' => $reservation->id,
            'guest_name' => $otaData['guest_name'] ?? $reservation->guest?->guest_name ?? 'Unknown',
            'room_number' => $reservation->room?->room_number,
            'ota_source' => $otaSource,
            'ota_reservation_number' => $otaData['reservation_id'] ?? $reservation->ota_reservation_number,
            'message' => $message,
        ]);
    }

    /**
     * Create notification for a web/direct booking.
     */
    public function webBookingCreated(Reservation $reservation): BookingNotification
    {
        $message = sprintf(
            '🆕 New booking by %s: Kamar %s — %s (Check-in: %s)',
            $reservation->guest?->guest_name ?? 'Unknown Guest',
            $reservation->room?->room_number ?? 'N/A',
            $reservation->guest?->guest_name ?? '-',
            $reservation->check_in ? Carbon::parse($reservation->check_in)->format('d/m/Y') : '-'
        );

        return BookingNotification::create([
            'type' => 'web_booking',
            'action' => 'created',
            'reservation_id' => $reservation->id,
            'guest_name' => $reservation->guest?->guest_name ?? 'Unknown',
            'room_number' => $reservation->room?->room_number,
            'message' => $message,
        ]);
    }

    /**
     * Get unread notification count.
     */
    public function getUnreadCount(): int
    {
        return BookingNotification::unread()->count();
    }

    /**
     * Get recent notifications with optional limit.
     */
    public function getRecent(int $limit = 20)
    {
        return BookingNotification::recent($limit)->get();
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(int $id): void
    {
        BookingNotification::where('id', $id)->unread()->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(): void
    {
        BookingNotification::markAllAsRead();
    }

    /**
     * Clean up old notifications (keep last 30 days).
     */
    public function cleanUp(): int
    {
        return BookingNotification::where('created_at', '<', now()->subDays(30))->delete();
    }
}
