<?php

namespace App\Jobs;

use App\Models\ProcessedEmail;
use App\Services\BookingNotificationService;
use App\Services\BookingSyncService;
use App\Services\EmailParserService;
use App\Services\OpenRouterService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessBookingEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying.
     */
    public int $backoff = 30;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 180;

    public function __construct(
        private readonly string $emailUid,
        private readonly string $sender,
        private readonly string $subject,
        private readonly string $body,
        private readonly string $otaSource,
        private readonly string $emailType
    ) {}

    public function handle(
        OpenRouterService $openRouter,
        EmailParserService $parser,
        BookingSyncService $sync
    ): void {
        Log::info('Processing OTA email', [
            'uid' => $this->emailUid,
            'sender' => $this->sender,
            'subject' => $this->subject,
            'email_type' => $this->emailType,
            'ota_source' => $this->otaSource,
        ]);

        // ═══ SKIP EMBUN BOOKINGS ═══
        if (Str::contains(strtolower($this->subject.' '.$this->body), 'embun')) {
            Log::info('Skipped Embun booking', ['uid' => $this->emailUid, 'subject' => $this->subject]);
            $this->markFailed('Embun property booking — not processed');

            return;
        }

        // Step 1: AI Parsing (returns array of bookings)
        $allAiData = $openRouter->parseBookingEmail($this->body, $this->subject, $this->otaSource);

        if (! $allAiData || ! is_array($allAiData) || count($allAiData) === 0) {
            Log::error('AI parsing failed for email', ['uid' => $this->emailUid]);
            $this->markFailed('AI parsing returned null/empty');

            return;
        }

        $roomCount = count($allAiData);
        Log::info("AI parsed {$roomCount} room(s) from email", ['uid' => $this->emailUid]);

        $successResults = [];
        $errors = [];

        foreach ($allAiData as $index => $aiData) {
            $roomLabel = $roomCount > 1 ? "Room #".($index + 1) : "Room";

            // Step 2: Validate AI output for this room
            if (! $this->validateAiOutput($aiData)) {
                $errMsg = "{$roomLabel}: AI output validation failed";
                Log::error($errMsg, ['uid' => $this->emailUid, 'ai_data' => $aiData]);
                $errors[] = $errMsg;
                continue;
            }

            // Make ota_reservation_number unique per room (e.g., "HTL-123/R1", "HTL-123/R2")
            $originalReservationId = $aiData['reservation_id'];
            if ($roomCount > 1) {
                $aiData['reservation_id'] = $originalReservationId . '/R' . ($index + 1);
            }

            // Step 3: Sync to booking system
            $result = $sync->sync($aiData);

            if (! $result['success']) {
                $errMsg = "{$roomLabel}: Booking sync failed";
                Log::error($errMsg, [
                    'uid' => $this->emailUid,
                    'reservation' => $originalReservationId,
                ]);
                $errors[] = $errMsg . ($result['error'] ? ": {$result['error']}" : '');
                continue;
            }

            $successResults[] = [
                'result' => $result,
                'aiData' => $aiData,
                'originalReservationId' => $originalReservationId,
            ];

            // Step 5: Trigger notification to Front Office (per room)
            $this->triggerNotification($result, $aiData);

            Log::info("OTA email: {$roomLabel} processed successfully", [
                'uid' => $this->emailUid,
                'action' => $result['action'],
                'reservation' => $result['reservation']?->reservation_number,
                'ota_id' => $aiData['reservation_id'],
            ]);
        }

        // Step 4: Mark as processed (if at least one room succeeded)
        if (count($successResults) > 0) {
            $first = $successResults[0];
            ProcessedEmail::markProcessed([
                'email_uid' => $this->emailUid,
                'sender' => $this->sender,
                'subject' => $this->subject,
                'status' => 'processed',
                'email_type' => $this->emailType,
                'ota_source' => $this->otaSource,
                'reservation_id' => $first['originalReservationId'],
            ]);
        } else {
            // All rooms failed
            $this->markFailed('All rooms failed: ' . implode('; ', $errors));
            return;
        }

        if (count($errors) > 0) {
            Log::warning('OTA email processed with partial errors', [
                'uid' => $this->emailUid,
                'success' => count($successResults),
                'errors' => count($errors),
                'error_details' => implode('; ', $errors),
            ]);
        } else {
            Log::info('OTA email processed successfully', [
                'uid' => $this->emailUid,
                'rooms' => count($successResults),
            ]);
        }
    }

    /**
     * Validate required fields from AI output.
     */
    private function validateAiOutput(array $data): bool
    {
        if (empty($data['reservation_id'])) {
            return false;
        }

        if (empty($data['guest_name'])) {
            return false;
        }

        if (! empty($data['checkin_date'])) {
            try {
                Carbon::parse($data['checkin_date']);
            } catch (\Exception $e) {
                return false;
            }
        }

        if (! empty($data['checkout_date'])) {
            try {
                Carbon::parse($data['checkout_date']);
            } catch (\Exception $e) {
                return false;
            }
        }

        $validStatuses = ['confirmed', 'cancelled', 'modified'];
        if (! empty($data['status']) && ! in_array(strtolower($data['status']), $validStatuses)) {
            return false;
        }

        return true;
    }

    /**
     * Mark email as failed in processed_emails table.
     */
    private function markFailed(string $error): void
    {
        ProcessedEmail::markProcessed([
            'email_uid' => $this->emailUid,
            'sender' => $this->sender,
            'subject' => $this->subject,
            'status' => 'failed',
            'email_type' => $this->emailType,
            'ota_source' => $this->otaSource,
            'error_message' => $error,
        ]);
    }

    /**
     * Trigger notification to Front Office.
     * Uses persistent database notifications via BookingNotificationService.
     */
    private function triggerNotification(array $result, array $aiData): void
    {
        if (! $result['reservation']) {
            return;
        }

        $reservation = $result['reservation'];
        $action = $result['action'];
        $notifService = app(BookingNotificationService::class);

        match ($action) {
            'created' => $notifService->otaBookingCreated($reservation, $aiData, $this->otaSource),
            'updated' => $notifService->otaBookingUpdated($reservation, $aiData, $this->otaSource),
            'cancelled' => $notifService->otaBookingCancelled($reservation, $aiData, $this->otaSource),
            default => null,
        };

        Log::info('OTA notification triggered for Front Office', [
            'reservation_id' => $reservation->id,
            'action' => $action,
        ]);
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessBookingEmailJob failed permanently', [
            'uid' => $this->emailUid,
            'sender' => $this->sender,
            'error' => $exception->getMessage(),
        ]);

        ProcessedEmail::markProcessed([
            'email_uid' => $this->emailUid,
            'sender' => $this->sender,
            'subject' => $this->subject,
            'status' => 'failed',
            'email_type' => $this->emailType,
            'ota_source' => $this->otaSource,
            'error_message' => 'Job failed: '.$exception->getMessage(),
        ]);
    }
}
