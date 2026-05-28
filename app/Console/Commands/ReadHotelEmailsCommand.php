<?php

namespace App\Console\Commands;

use App\Jobs\ProcessBookingEmailJob;
use App\Services\EmailParserService;
use App\Services\ImapService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReadHotelEmailsCommand extends Command
{
    protected $signature = 'hotel:read-emails
                            {--dry-run : Read emails without dispatching jobs}
                            {--limit=50 : Maximum number of emails to process}';

    protected $description = 'Read OTA emails from IMAP and dispatch booking processing jobs';

    public function handle(
        ImapService $imap,
        EmailParserService $parser
    ): int {
        $this->info('📧 Starting OTA Email Autopilot...');
        Log::info('hotel:read-emails command started');

        $dryRun = $this->option('dry-run');
        $limit  = (int) $this->option('limit');

        // Connect to IMAP
        if (!$imap->connect()) {
            $this->error('❌ Failed to connect to IMAP server');
            Log::error('hotel:read-emails: IMAP connection failed');
            return self::FAILURE;
        }

        // Fetch unread emails
        $messages = $imap->getUnreadEmails();

        if (empty($messages)) {
            $this->info('ℹ️ No unread emails found');
            $imap->disconnect();
            return self::SUCCESS;
        }

        $this->info('Found ' . count($messages) . ' unread email(s)');

        $processed = 0;
        $skipped   = 0;
        $failed    = 0;

        foreach ($messages as $message) {
            if ($processed >= $limit) {
                $this->warn("Reached limit of {$limit} emails. Stopping.");
                break;
            }

            try {
                $uid     = (string) $message->getUid();
                $sender  = $message->getFrom()[0]->mail ?? '';
                $subject = $message->getSubject() ?? '';
                $body    = $this->extractBody($message);

                $this->info("Processing: [{$uid}] {$subject} (from: {$sender})");

                // Validate sender
                if (!$parser->isWhitelistedSender($sender)) {
                    $this->warn("  ⏭️ Skipping: sender not whitelisted ({$sender})");
                    $parser->markSkipped($uid, $sender, $subject, 'Sender not in OTA whitelist');
                    $imap->markAsSeen($message);
                    $skipped++;
                    continue;
                }

                // Check duplicate
                if ($parser->isDuplicate($uid, $sender)) {
                    $this->warn("  ⏭️ Skipping: duplicate email [{$uid}]");
                    $parser->markDuplicate($uid, $sender, $subject);
                    $imap->markAsSeen($message);
                    $skipped++;
                    continue;
                }

                // Detect email type
                $emailType = $parser->detectEmailType($subject, $body);
                $otaSource = $parser->getOtaSource($sender);

                $this->info("  📋 Type: {$emailType} | OTA: {$otaSource}");

                if ($dryRun) {
                    $this->info("  🔍 Dry-run: would dispatch job");
                    $imap->markAsSeen($message);
                    $processed++;
                    continue;
                }

                // Dispatch queue job
                ProcessBookingEmailJob::dispatch(
                    $uid,
                    $sender,
                    $subject,
                    $body,
                    $otaSource,
                    $emailType
                );

                // Mark as seen
                $imap->markAsSeen($message);

                $this->info("  ✅ Dispatched to queue");
                $processed++;

            } catch (\Exception $e) {
                $this->error("  ❌ Error: " . $e->getMessage());
                Log::error('hotel:read-emails: Error processing message', [
                    'uid'   => $uid ?? 'unknown',
                    'error' => $e->getMessage(),
                ]);

                try {
                    $imap->moveEmail($message, 'Failed');
                } catch (\Exception $moveError) {
                    // Ignore move errors
                }

                $failed++;
            }
        }

        // Disconnect
        $imap->disconnect();

        // Summary
        $this->newLine();
        $this->info("📊 Summary: {$processed} dispatched, {$skipped} skipped, {$failed} failed");
        Log::info('hotel:read-emails command completed', [
            'processed' => $processed,
            'skipped'   => $skipped,
            'failed'    => $failed,
        ]);

        return self::SUCCESS;
    }

    /**
     * Extract text body from email message.
     */
    private function extractBody($message): string
    {
        $body = '';

        try {
            // Try HTML body first, then plain text
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
            Log::warning('Failed to extract email body: ' . $e->getMessage());
        }

        // Clean up
        $body = preg_replace('/\s+/', ' ', $body);
        return trim($body);
    }
}
