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

        // Step 1: AI Parsing
        $aiData = $openRouter->parseBookingEmail($this->body, $this->subject, $this->otaSource);

        if (! $aiData) {
            Log::error('AI parsing failed for email', ['uid' => $this->emailUid]);
            $this->markFailed('AI parsing returned null');

            return;
        }

        // Step 2: Validate AI output
        if (! $this->validateAiOutput($aiData)) {
            Log::error('AI output validation failed', [
                'uid' => $this->emailUid,
                'ai_data' => $aiData,
            ]);
            $this->markFailed('AI output validation failed: missing required fields');

            return;
        }

        // Step 3: Sync to booking system
        $result = $sync->sync($aiData);

        if (! $result['success']) {
            Log::error('Booking sync failed', [
                'uid' => $this->emailUid,
                'reservation' => $aiData['reservation_id'] ?? 'unknown',
            ]);
            $this->markFailed('Booking sync failed');

            return;
        }

        // Step 4: Mark as processed
        ProcessedEmail::markProcessed([
            'email_uid' => $this->emailUid,
            'sender' => $this->sender,
            'subject' => $this->subject,
            'status' => 'processed',
            'email_type' => $this->emailType,
            'ota_source' => $this->otaSource,
            'reservation_id' => $aiData['reservation_id'] ?? null,
        ]);

        // Step 5: Trigger notification to Front Office
        $this->triggerNotification($result, $aiData);

        Log::info('OTA email processed successfully', [
            'uid' => $this->emailUid,
            'action' => $result['action'],
            'reservation' => $aiData['reservation_id'] ?? null,
        ]);
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
