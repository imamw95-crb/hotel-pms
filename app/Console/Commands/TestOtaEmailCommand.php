<?php

namespace App\Console\Commands;

use App\Services\BookingMapperService;
use App\Services\EmailParserService;
use App\Services\OpenRouterService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TestOtaEmailCommand extends Command
{
    protected $signature = 'hotel:test-ota
                            {--email-file= : Path to email text file for testing}
                            {--raw : Use raw email text from stdin}
                            {--sample : Use built-in sample OTA email}
                            {--skip-ai : Skip AI parsing, test mapping only}';

    protected $description = 'Test OTA email parsing and booking sync (no IMAP needed)';

    public function handle(
        OpenRouterService $openRouter,
        EmailParserService $parser,
        BookingMapperService $mapper
    ): int {
        $this->info('🧪 OTA Email Autopilot — Test Mode');
        $this->newLine();

        // ─── Step 1: Get email content ────────────────────────────
        $emailBody = '';
        $emailSubject = '';
        $sender = '';

        if ($this->option('sample')) {
            [$emailBody, $emailSubject, $sender] = $this->getSampleEmail();
            $this->info('📧 Using built-in sample email');
        } elseif ($emailFile = $this->option('email-file')) {
            if (! file_exists($emailFile)) {
                $this->error("File not found: {$emailFile}");

                return self::FAILURE;
            }
            $emailBody = file_get_contents($emailFile);
            $emailSubject = 'OTA Booking Confirmation';
            $sender = 'info.partner@tiket.com';
            $this->info("📧 Reading email from: {$emailFile}");
        } elseif ($this->option('raw')) {
            $this->info('📧 Paste email content (Ctrl+Z then Enter to finish):');
            $emailBody = '';
            while ($line = fgets(STDIN)) {
                $emailBody .= $line;
            }
            $emailSubject = 'OTA Booking';
            $sender = 'info.partner@tiket.com';
        } else {
            $this->error('Please specify --sample, --email-file=<path>, or --raw');

            return self::FAILURE;
        }

        $this->newLine();
        $this->info("Subject: {$emailSubject}");
        $this->info("Sender: {$sender}");
        $this->info('Body length: '.strlen($emailBody).' chars');
        $this->newLine();

        // ─── Step 2: Validate sender ───────────────────────────────
        $this->info('─── Step 1: Sender Validation ───');
        if (! $parser->isWhitelistedSender($sender)) {
            $this->error("❌ Sender not whitelisted: {$sender}");

            return self::FAILURE;
        }
        $this->info("✅ Sender whitelisted: {$sender}");

        $otaSource = $parser->getOtaSource($sender);
        $this->info("   OTA Source: {$otaSource}");
        $this->newLine();

        // ─── Step 3: Detect email type ─────────────────────────────
        $this->info('─── Step 2: Email Type Detection ───');
        $emailType = $parser->detectEmailType($emailSubject, $emailBody);
        $this->info("✅ Detected type: {$emailType}");
        $this->newLine();

        // ─── Step 4: AI Parsing ────────────────────────────────────
        if ($this->option('skip-ai')) {
            $this->info('─── Step 3: AI Parsing (SKIPPED) ───');
            $aiData = [
                'reservation_id' => 'TEST-'.strtoupper(substr(md5(time()), 0, 8)),
                'guest_name' => 'Budi Santoso',
                'checkin_date' => '2026-06-15',
                'checkout_date' => '2026-06-17',
                'room_type' => 'Deluxe',
                'guest_count' => 2,
                'total_price' => 1500000,
                'payment_method' => 'tiket.com',
                'payment_date' => '2026-06-14',
                'status' => 'confirmed',
                'ota_source' => $otaSource,
            ];
            $this->info('Using test data:');
            $this->table(['Field', 'Value'], collect($aiData)->map(fn ($v, $k) => [$k, $v])->toArray());
        } else {
            $this->info('─── Step 3: AI Parsing (OpenRouter) ───');
            $this->info('⏳ Sending to AI...');

            $allAiData = $openRouter->parseBookingEmail($emailBody, $emailSubject, $otaSource);

            if (! $allAiData || ! is_array($allAiData) || count($allAiData) === 0) {
                $this->error('❌ AI parsing failed');

                return self::FAILURE;
            }

            $roomCount = count($allAiData);
            $this->info("✅ AI parsing successful — {$roomCount} room(s) detected");
            $this->newLine();

            // Show first room's data in table
            $aiData = $allAiData[0];
            $this->table(['Field', 'Value'], collect($aiData)->map(fn ($v, $k) => [$k, is_array($v) ? json_encode($v) : $v])->toArray());

            if ($roomCount > 1) {
                $this->newLine();
                $this->info("ℹ️  Email contains {$roomCount} rooms. Showing first room above.");
                $this->info("   Room #2: {$allAiData[1]['guest_name']} ({$allAiData[1]['room_type']}) " .
                    ($allAiData[2] ?? false ? "| Room #3: {$allAiData[2]['guest_name']}" : ''));
            }
        }
        $this->newLine();

        // ─── Step 5: Validate AI output ────────────────────────────
        $this->info('─── Step 4: Validation ───');
        $errors = [];

        if (empty($aiData['reservation_id'])) {
            $errors[] = 'Missing reservation_id';
        }
        if (empty($aiData['guest_name'])) {
            $errors[] = 'Missing guest_name';
        }
        if (! empty($aiData['checkin_date'])) {
            try {
                Carbon::parse($aiData['checkin_date']);
            } catch (\Exception $e) {
                $errors[] = "Invalid checkin_date: {$aiData['checkin_date']}";
            }
        }
        if (! empty($aiData['checkout_date'])) {
            try {
                Carbon::parse($aiData['checkout_date']);
            } catch (\Exception $e) {
                $errors[] = "Invalid checkout_date: {$aiData['checkout_date']}";
            }
        }

        if (! empty($errors)) {
            foreach ($errors as $error) {
                $this->error("❌ {$error}");
            }

            return self::FAILURE;
        }
        $this->info('✅ All validations passed');
        $this->newLine();

        // ─── Step 6: Map to reservation format ────────────────────
        $this->info('─── Step 5: Booking Mapping ───');
        $mapped = $mapper->mapToReservation($aiData);
        $this->info('✅ Mapped to reservation format');
        $this->newLine();
        $this->table(['Field', 'Value'], collect($mapped)->map(fn ($v, $k) => [$k, is_array($v) ? json_encode($v) : ($v ?? 'NULL')])->toArray());
        $this->newLine();

        // ─── Summary ───────────────────────────────────────────────
        $this->info('═══════════════════════════════════════');
        $this->info('  ✅ TEST PASSED — All steps successful');
        $this->info('═══════════════════════════════════════');
        $this->newLine();
        $this->info('To actually sync to database, run:');
        $this->info('  php artisan hotel:test-ota --sample --skip-ai');
        $this->info('(then use BookingSyncService manually or dispatch ProcessBookingEmailJob)');

        return self::SUCCESS;
    }

    /**
     * Get a sample OTA email for testing.
     */
    private function getSampleEmail(): array
    {
        $subject = 'Booking Confirmation - Tiket.com Reservation #TK-987654';

        $body = <<<'EMAIL'
Dear Partner,

We are pleased to confirm the following reservation at your property.

═══════════════════════════════════════
BOOKING DETAILS
═══════════════════════════════════════

Reservation ID: TK-987654
Guest Name: Budi Santoso
Check-in Date: 2026-06-15
Check-out Date: 2026-06-17
Room Type: Deluxe Room
Number of Guests: 2
Booking Status: Confirmed
Source: Tiket.com

═══════════════════════════════════════
PAYMENT INFORMATION
═══════════════════════════════════════

Total Amount: Rp 1.500.000
Payment Method: Bank Transfer
Payment Status: Paid

═══════════════════════════════════════

Please ensure the room is ready for the guest's arrival.
Check-in time: 14:00
Check-out time: 12:00

If you have any questions, please contact us at info.partner@tiket.com

Best regards,
Tiket.com Partner Team
EMAIL;

        $sender = 'info.partner@tiket.com';

        return [$body, $subject, $sender];
    }
}
