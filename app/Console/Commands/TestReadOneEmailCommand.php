<?php

namespace App\Console\Commands;

use App\Services\BookingMapperService;
use App\Services\BookingSyncService;
use App\Services\EmailParserService;
use App\Services\ImapService;
use App\Services\OpenRouterService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestReadOneEmailCommand extends Command
{
    protected $signature = 'hotel:test-live
                            {--uid= : Specific email UID to read}
                            {--latest : Read the latest unread email}
                            {--process : Actually process the email (AI parse + map)}
                            {--sync : Sync the parsed booking directly to reservations table}
                            {--dry-run : Read only, don\'t dispatch or process}';

    protected $description = 'Test reading one live email from IMAP';

    public function handle(
        ImapService $imap,
        EmailParserService $parser,
        OpenRouterService $openRouter,
        BookingMapperService $mapper,
        BookingSyncService $sync
    ): int {
        $this->info('📧 Live Email Test — Reading from IMAP');
        $this->newLine();

        // ─── Connect ──────────────────────────────────────────────
        $this->info('⏳ Connecting to IMAP...');
        $this->info('   Host: '.config('services.imap.host'));
        $this->info('   Port: '.config('services.imap.port'));
        $this->info('   User: '.config('services.imap.username'));
        $this->info('   Pass: '.(empty(config('services.imap.password')) ? '(empty)' : '(set)'));
        if (! $imap->connect()) {
            $this->error('❌ Failed to connect to IMAP server');
            $this->error('Check IMAP credentials in .env');

            return self::FAILURE;
        }
        $this->info('✅ Connected to IMAP');
        $this->newLine();

        // ─── Fetch email ──────────────────────────────────────────
        $uid = $this->option('uid');
        $latest = $this->option('latest');
        $process = $this->option('process');
        $syncToDb = $this->option('sync');
        $dryRun = $this->option('dry-run');

        try {
            $client = $imap->getClient();
            $folder = $client->getFolder('INBOX');

            if ($uid) {
                $this->info("📬 Fetching email UID: {$uid}");
                $messages = $folder->query()->whereUid($uid)->get();
            } elseif ($latest) {
                $this->info('📬 Fetching latest unread email...');
                $messages = $folder->query()->unseen()->limit(1)->get();
            } else {
                $this->info('📬 Fetching latest unread email (default)...');
                $messages = $folder->query()->unseen()->limit(1)->get();
            }

            if ($messages->isEmpty()) {
                $this->warn('ℹ️ No unread emails found in inbox');
                $imap->disconnect();

                return self::SUCCESS;
            }

            $message = $messages->first();
            $emailUid = (string) $message->getUid();
            $sender = $message->getFrom()[0]->mail ?? 'unknown';
            $senderName = $message->getFrom()[0]->name ?? '';
            $subject = $message->getSubject() ?? '(no subject)';
            $date = (string) $message->getDate();

            // ─── Display email info ──────────────────────────────────
            $this->info('═══════════════════════════════════════');
            $this->info('  EMAIL FOUND');
            $this->info('═══════════════════════════════════════');
            $this->table(
                ['Field', 'Value'],
                [
                    ['UID', $emailUid],
                    ['From', "{$senderName} <{$sender}>"],
                    ['Subject', $subject],
                    ['Date', $date],
                ]
            );
            $this->newLine();

            // ─── Validate sender ─────────────────────────────────────
            $this->info('─── Sender Validation ───');
            if (! $parser->isWhitelistedSender($sender)) {
                $this->error("❌ NOT whitelisted: {$sender}");
                $this->info('   This email would be skipped in production');
                $imap->disconnect();

                return self::FAILURE;
            }
            $this->info("✅ Whitelisted: {$sender}");
            $otaSource = $parser->getOtaSource($sender);
            $this->info("   OTA Source: {$otaSource}");
            $this->newLine();

            // ─── Extract body ────────────────────────────────────────
            $body = $this->extractBody($message);
            $emailType = $parser->detectEmailType($subject, $body);

            $this->info('─── Email Content ───');
            $this->info("Type detected: {$emailType}");
            $this->info('Body length: '.strlen($body).' chars');
            $this->newLine();

            // Show first 500 chars of body
            $preview = substr($body, 0, 500);
            $this->info('Body preview:');
            $this->line(str_repeat('─', 50));
            $this->line($preview);
            if (strlen($body) > 500) {
                $this->line('... (truncated)');
            }
            $this->line(str_repeat('─', 50));
            $this->newLine();

            // ─── AI Processing ───────────────────────────────────────
            if ($process && ! $dryRun) {
                $this->info('─── AI Processing ───');
                $this->info('⏳ Sending to OpenRouter AI...');

                $aiData = $openRouter->parseBookingEmail($body, $subject, $otaSource);

                if (! $aiData) {
                    $this->error('❌ AI parsing failed');
                    $imap->disconnect();

                    return self::FAILURE;
                }

                $this->info('✅ AI parsing successful');
                $this->newLine();
                $this->table(
                    ['Field', 'Value'],
                    collect($aiData)->map(fn ($v, $k) => [$k, is_array($v) ? json_encode($v) : $v])->toArray()
                );
                $this->newLine();

                // ─── Mapping ─────────────────────────────────────────
                $this->info('─── Booking Mapping ───');
                $mapped = $mapper->mapToReservation($aiData);
                $this->table(
                    ['Field', 'Value'],
                    collect($mapped)->map(fn ($v, $k) => [$k, is_array($v) ? json_encode($v) : ($v ?? 'NULL')])->toArray()
                );
                $this->newLine();

                // ─── Sync to Database ────────────────────────────────
                if ($syncToDb) {
                    $this->info('─── Sync to Database ───');
                    $this->info('⏳ Saving reservation...');

                    $result = $sync->sync($aiData);

                    if (! $result['success']) {
                        $this->error('❌ Sync failed');
                        $imap->disconnect();

                        return self::FAILURE;
                    }

                    $reservation = $result['reservation'];
                    $action = $result['action'];

                    $this->info("✅ Reservation {$action} successfully!");
                    $this->table(
                        ['Field', 'Value'],
                        [
                            ['Reservation #', $reservation->reservation_number],
                            ['Guest', $reservation->guest->guest_name ?? 'N/A'],
                            ['Room', $reservation->room->room_number ?? 'Unassigned'],
                            ['Check-in', $reservation->check_in->format('Y-m-d H:i')],
                            ['Check-out', $reservation->check_out->format('Y-m-d H:i')],
                            ['Status', $reservation->status],
                            ['Action', $action],
                        ]
                    );
                    $this->newLine();

                    $this->info('═══════════════════════════════════════');
                    $this->info('  ✅ SYNC COMPLETE — Reservation saved');
                    $this->info('═══════════════════════════════════════');
                } else {
                    $this->info('─── Sync (SKIPPED) ───');
                    $this->info('Use --sync flag to save to reservations table');
                    $this->newLine();
                    $this->info('═══════════════════════════════════════');
                    $this->info('  ✅ LIVE TEST PASSED');
                    $this->info('  Email can be processed successfully');
                    $this->info('═══════════════════════════════════════');
                }
            } else {
                $this->info('─── AI Processing (SKIPPED) ───');
                $this->info('Use --process flag to actually parse with AI');
                $this->info('Use --process --sync to also save to database');
                $this->newLine();
                $this->info('═══════════════════════════════════════');
                $this->info('  ✅ EMAIL READ SUCCESSFUL');
                $this->info('  Sender whitelisted, ready for processing');
                $this->info('═══════════════════════════════════════');
            }

            $imap->disconnect();

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error: '.$e->getMessage());
            Log::error('TestReadOneEmail error: '.$e->getMessage());
            $imap->disconnect();

            return self::FAILURE;
        }
    }

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
            $this->warn('Warning extracting body: '.$e->getMessage());
        }
        $body = preg_replace('/\s+/', ' ', $body);

        return trim($body);
    }
}
