<?php

namespace App\Console\Commands;

use App\Services\AvailabilityService;
use App\Services\BookingSyncService;
use App\Services\EmailParserService;
use App\Services\ImapService;
use App\Services\OpenRouterService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReadHotelEmailsCommand extends Command
{
    protected $signature = 'hotel:read-emails
                            {--dry-run : Read emails without syncing}
                            {--limit=10 : Maximum number of emails to process}
                            {--today : Only process emails from today}
                            {--days= : Process emails from last N days (e.g. --days=2 for today + yesterday)}
                            {--daemon : Run in daemon mode (continuous polling)}
                            {--interval=30 : Polling interval in seconds (daemon mode)}';

    protected $description = 'Read OTA emails from IMAP and auto-sync to reservations';

    /**
     * Flow: NEW EMAIL → CHECK UID → AI PARSE → CHECK ROOM → CREATE RESERVATION → SAVE UID → DONE
     */
    public function handle(
        ImapService $imap,
        EmailParserService $parser,
        OpenRouterService $openRouter,
        BookingSyncService $sync,
        AvailabilityService $availability
    ): int {
        $this->info('📧 OTA Email Autopilot — Auto-sync to Reservations');
        Log::info('hotel:read-emails started');

        $dryRun = $this->option('dry-run');
        $limit = (int) $this->option('limit');
        $today = $this->option('today');
        $days = $this->option('days') ? (int) $this->option('days') : null;
        $daemon = $this->option('daemon');
        $interval = (int) $this->option('interval');

        if ($daemon) {
            $this->info("🔄 Daemon mode — polling every {$interval}s (Ctrl+C to stop)");

            return $this->runDaemon($imap, $parser, $openRouter, $sync, $availability, $interval, $dryRun, $today, $days);
        }

        return $this->processBatch($imap, $parser, $openRouter, $sync, $availability, $limit, $dryRun, $today, $days);
    }

    /**
     * Run in daemon mode — continuous polling.
     */
    private function runDaemon(
        ImapService $imap,
        EmailParserService $parser,
        OpenRouterService $openRouter,
        BookingSyncService $sync,
        AvailabilityService $availability,
        int $interval,
        bool $dryRun,
        bool $today,
        ?int $days
    ): int {
        $running = true;
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGINT, function () use (&$running) {
                $this->newLine();
                $this->info('🛑 Shutting down gracefully...');
                $running = false;
            });
            pcntl_signal(SIGTERM, function () use (&$running) {
                $running = false;
            });
        }

        $cycle = 0;
        while ($running) {
            $cycle++;
            $this->newLine();
            $this->info("═══ Cycle #{$cycle} — ".now()->format('Y-m-d H:i:s').' ═══');

            $result = $this->processBatch($imap, $parser, $openRouter, $sync, $availability, 50, $dryRun, $today, $days);

            if ($result === self::FAILURE) {
                $this->warn('⚠️ Batch had errors, reconnecting IMAP...');
                $imap->disconnect();
                sleep(5);
            }

            if (function_exists('pcntl_signal_dispatch')) {
                pcntl_signal_dispatch();
            }

            if (! $running) {
                break;
            }

            $this->info("💤 Sleeping {$interval}s...");
            sleep($interval);
        }

        $imap->disconnect();
        $this->info('✅ Daemon stopped.');

        return self::SUCCESS;
    }

    /**
     * Process a single batch of emails.
     * Flow: NEW EMAIL → CHECK UID → AI PARSE → CHECK ROOM → CREATE RESERVATION → SAVE UID → DONE
     */
    private function processBatch(
        ImapService $imap,
        EmailParserService $parser,
        OpenRouterService $openRouter,
        BookingSyncService $sync,
        AvailabilityService $availability,
        int $limit,
        bool $dryRun,
        bool $today,
        ?int $days
    ): int {
        // Connect to IMAP
        if (! $imap->connect()) {
            $this->error('❌ IMAP connection failed');
            Log::error('hotel:read-emails: IMAP connection failed');

            return self::FAILURE;
        }

        // Fetch ONLY unread (new) emails — never old emails
        $client = $imap->getClient();
        $folder = $client->getFolder('INBOX');

        $query = $folder->query()->unseen();

        if ($today) {
            $query = $query->since(date('Y-m-d'));
        } elseif ($days && $days > 0) {
            $query = $query->since(date('Y-m-d', strtotime("-{$days} days")));
        }

        $messages = $query->limit($limit)->get();

        if ($messages->isEmpty()) {
            $this->info('ℹ️ No new OTA emails');
            $imap->disconnect();

            return self::SUCCESS;
        }

        $this->info("Found {$messages->count()} new unread email(s)");
        $this->newLine();

        $processed = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($messages as $message) {
            if (($processed + $skipped + $failed) >= $limit) {
                break;
            }

            $uid = (string) $message->getUid();
            $sender = $message->getFrom()[0]->mail ?? '';
            $subject = $message->getSubject() ?? '';
            $body = $this->extractBody($message);

            $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->info("[{$uid}] {$subject}");
            $this->info("From: {$sender}");

            try {
                // ═══ STEP 1: CHECK UID FIRST (duplicate prevention) ═══
                if ($parser->isDuplicate($uid, $sender)) {
                    $this->warn('  ⏭️ Skipped: already processed (duplicate UID)');
                    $parser->markDuplicate($uid, $sender, $subject);
                    $imap->markAsSeen($message);
                    $skipped++;

                    continue;
                }

                // ═══ STEP 2: VALIDATE SENDER (whitelist check) ═══
                if (! $parser->isWhitelistedSender($sender)) {
                    $this->warn('  ⏭️ Skipped: sender not in OTA whitelist');
                    $parser->markSkipped($uid, $sender, $subject, 'Sender not in OTA whitelist', $body);
                    $imap->markAsSeen($message);
                    $skipped++;

                    continue;
                }

                // ═══ STEP 3: DETECT EMAIL TYPE & OTA SOURCE ═══
                $emailType = $parser->detectEmailType($subject, $body);
                $otaSource = $parser->getOtaSource($sender);

                $this->info("  📋 Type: {$emailType} | OTA: {$otaSource}");

                // ═══ STEP 4: AI PARSING ═══
                $aiData = $openRouter->parseBookingEmail($body, $subject, $otaSource);

                if (! $aiData) {
                    $this->error('  ❌ AI parsing failed');
                    $parser->markFailed($uid, $sender, $subject, $otaSource, 'AI parsing returned null', $body);
                    // Don't mark as seen — allow retry
                    $failed++;

                    continue;
                }

                $this->info("  ✅ AI: {$aiData['guest_name']} ({$aiData['reservation_id']})");

                // ═══ STEP 5: VALIDATE AI OUTPUT ═══
                if (! $this->validateAiOutput($aiData)) {
                    $this->error('  ❌ AI output validation failed');
                    $parser->markFailed($uid, $sender, $subject, $otaSource, 'AI output validation failed', $body);
                    $failed++;

                    continue;
                }

                // ═══ STEP 6: DRY-RUN CHECK ═══
                if ($dryRun) {
                    $this->info('  🔍 Dry-run: not saving');
                    $this->info("     → Would create: {$aiData['guest_name']}, {$aiData['checkin_date']} to {$aiData['checkout_date']}");
                    $imap->markAsSeen($message);
                    $processed++;

                    continue;
                }

                // ═══ STEP 7: CHECK ROOM AVAILABILITY (overbooking prevention) ═══
                $checkIn = Carbon::parse($aiData['checkin_date'])->setTime(14, 0);
                $checkOut = Carbon::parse($aiData['checkout_date'])->setTime(12, 0);

                if ($checkIn->gte($checkOut)) {
                    $this->error('  ❌ Invalid dates: check-in >= check-out');
                    $parser->markFailed($uid, $sender, $subject, $otaSource, 'Invalid dates: check-in >= check-out', $body);
                    $failed++;

                    continue;
                }

                // Find available room by type (back-to-back aware)
                $roomId = $sync->findAvailableRoom($aiData['room_type'] ?? null, $checkIn, $checkOut);

                if (! $roomId && ($aiData['status'] ?? '') !== 'cancelled') {
                    $this->warn("  ⚠️ No available room for type '{$aiData['room_type']}' — reservation will be unassigned");
                }

                // ═══ STEP 8: CREATE/UPDATE RESERVATION ═══
                $result = $sync->sync($aiData, $roomId);

                if (! $result['success']) {
                    $this->error('  ❌ Sync failed');
                    $parser->markFailed($uid, $sender, $subject, $otaSource, 'Sync failed: '.($result['error'] ?? 'unknown'), $body);
                    $failed++;

                    continue;
                }

                $reservation = $result['reservation'];
                $action = $result['action'];

                $roomInfo = $reservation->room
                    ? "Room {$reservation->room->room_number}"
                    : 'Room unassigned';

                $paymentInfo = '';
                if ($reservation->ota_reservation_number) {
                    $paidStr = 'Rp '.number_format($reservation->paid_amount, 0, ',', '.');
                    $totalStr = 'Rp '.number_format($reservation->total_amount, 0, ',', '.');
                    $statusStr = str_replace('_', ' ', $reservation->ota_payment_status ?? 'unpaid_ota');
                    $paymentInfo = " | {$paidStr}/{$totalStr} ({$statusStr})";
                }

                $this->info("  ✅ {$action}: {$reservation->reservation_number} ({$roomInfo}){$paymentInfo}");

                // ═══ STEP 9: SAVE UID + MARK AS READ (atomic) ═══
                $imap->markAsSeen($message);
                $parser->markProcessed($uid, $sender, $subject, $emailType, $otaSource, $aiData['reservation_id'] ?? null, $body);

                $processed++;

            } catch (\Exception $e) {
                $this->error('  ❌ Error: '.$e->getMessage());
                Log::error('hotel:read-emails: error', [
                    'uid' => $uid,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                // Save UID as failed (don't mark as seen — allow retry)
                try {
                    $parser->markFailed($uid, $sender, $subject, $otaSource ?? '', $e->getMessage(), $body);
                } catch (\Exception $markEx) {
                    Log::error('Failed to mark email as failed: '.$markEx->getMessage());
                }

                $failed++;
            }
        }

        // ─── Summary ──────────────────────────────────────────────
        $this->newLine();
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info("📊 Done: {$processed} synced, {$skipped} skipped, {$failed} failed");
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $imap->disconnect();

        return self::SUCCESS;
    }

    /**
     * Validate AI output has required fields.
     */
    private function validateAiOutput(array $data): bool
    {
        if (empty($data['reservation_id'])) {
            $this->warn('  ⚠️ Missing reservation_id');

            return false;
        }

        if (empty($data['guest_name'])) {
            $this->warn('  ⚠️ Missing guest_name');

            return false;
        }

        if (empty($data['checkin_date']) || empty($data['checkout_date'])) {
            $this->warn('  ⚠️ Missing dates');

            return false;
        }

        try {
            $ci = Carbon::parse($data['checkin_date']);
            $co = Carbon::parse($data['checkout_date']);
            if ($ci->gte($co)) {
                $this->warn('  ⚠️ check-in >= check-out');

                return false;
            }
        } catch (\Exception $e) {
            $this->warn('  ⚠️ Invalid date format');

            return false;
        }

        return true;
    }

    /**
     * Extract clean text body from email message.
     */
    private function extractBody($message): string
    {
        $body = '';
        try {
            $htmlBody = $message->getHTMLBody();
            if ($htmlBody) {
                $body = strip_tags($htmlBody);
            } else {
                $textBody = $message->getTextBody();
                if ($textBody) {
                    $body = $textBody;
                }
            }
        } catch (\Exception $e) {
            Log::warning('Body extraction error: '.$e->getMessage());
        }

        return trim(preg_replace('/\s+/', ' ', $body));
    }
}
