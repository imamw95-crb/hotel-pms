<?php

namespace App\Services;

use App\Models\ProcessedEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EmailParserService
{
    /**
     * OTA whitelist domains.
     */
    private array $allowedDomains;

    /**
     * OTA whitelist senders (exact email addresses).
     */
    private array $allowedSenders;

    public function __construct()
    {
        $this->allowedDomains = array_filter(
            explode(',', config('services.ota.whitelist_domains', 'tiket.com,traveloka.com'))
        );
        $this->allowedSenders = array_filter(
            explode(',', config('services.ota.whitelist_senders', 'info.partner@tiket.com,hotel@traveloka.com'))
        );
    }

    /**
     * Validate if the sender is from a whitelisted OTA.
     */
    public function isWhitelistedSender(string $senderEmail): bool
    {
        $senderEmail = strtolower(trim($senderEmail));

        // Check exact sender match
        if (in_array($senderEmail, array_map('strtolower', $this->allowedSenders))) {
            return true;
        }

        // Check domain match
        $domain = substr(strrchr($senderEmail, '@'), 1);
        if (in_array($domain, $this->allowedDomains)) {
            return true;
        }

        return false;
    }

    /**
     * Detect email type based on subject and body.
     */
    public function detectEmailType(string $subject, string $body): string
    {
        $text = strtolower($subject . ' ' . $body);

        // Cancellation keywords
        $cancelKeywords = ['cancel', 'cancelled', 'cancellation', 'void', 'refunded'];
        foreach ($cancelKeywords as $keyword) {
            if (Str::contains($text, $keyword)) {
                return 'cancellation';
            }
        }

        // Modification keywords
        $modifyKeywords = ['modification', 'updated reservation', 'changed booking', 'amendment', 'modify', 'modified'];
        foreach ($modifyKeywords as $keyword) {
            if (Str::contains($text, $keyword)) {
                return 'modification';
            }
        }

        // Booking keywords
        $bookingKeywords = ['booking', 'reservation', 'confirmed', 'new booking', 'new reservation'];
        foreach ($bookingKeywords as $keyword) {
            if (Str::contains($text, $keyword)) {
                return 'booking';
            }
        }

        return 'unknown';
    }

    /**
     * Determine OTA source from sender email.
     */
    public function getOtaSource(string $senderEmail): string
    {
        $domain = strtolower(substr(strrchr($senderEmail, '@'), 1));

        return match ($domain) {
            'tiket.com'     => 'tiket.com',
            'traveloka.com' => 'traveloka.com',
            default         => $domain,
        };
    }

    /**
     * Check if email was already processed (duplicate prevention).
     */
    public function isDuplicate(string $uid, string $sender): bool
    {
        return ProcessedEmail::isProcessed($uid, $sender);
    }

    /**
     * Mark email as duplicate.
     */
    public function markDuplicate(string $uid, string $sender, string $subject, ?string $rawBody = null): void
    {
        ProcessedEmail::markProcessed([
            'email_uid'   => $uid,
            'sender'      => $sender,
            'subject'     => Str::limit($subject, 500),
            'status'      => 'duplicate',
            'email_type'  => 'unknown',
            'ota_source'  => $this->getOtaSource($sender),
            'raw_body'    => $rawBody ? Str::limit($rawBody, 5000) : null,
        ]);

        Log::info("Duplicate email skipped", ['uid' => $uid, 'sender' => $sender]);
    }

    /**
     * Mark email as skipped (invalid sender, etc).
     */
    public function markSkipped(string $uid, string $sender, string $subject, string $reason, ?string $rawBody = null): void
    {
        ProcessedEmail::markProcessed([
            'email_uid'     => $uid,
            'sender'        => $sender,
            'subject'       => Str::limit($subject, 500),
            'status'        => 'skipped',
            'email_type'    => 'unknown',
            'ota_source'    => $this->getOtaSource($sender),
            'error_message' => $reason,
            'raw_body'      => $rawBody ? Str::limit($rawBody, 5000) : null,
        ]);
    }

    /**
     * Mark email as successfully processed.
     */
    public function markProcessed(string $uid, string $sender, string $subject, string $emailType, string $otaSource, ?string $reservationId = null, ?string $rawBody = null): void
    {
        ProcessedEmail::markProcessed([
            'email_uid'      => $uid,
            'sender'         => $sender,
            'subject'        => Str::limit($subject, 500),
            'status'         => 'processed',
            'email_type'     => $emailType,
            'ota_source'     => $otaSource,
            'reservation_id' => $reservationId,
            'raw_body'       => $rawBody ? Str::limit($rawBody, 5000) : null,
        ]);

        Log::info("Email processed successfully", [
            'uid'            => $uid,
            'sender'         => $sender,
            'email_type'     => $emailType,
            'ota_source'     => $otaSource,
            'reservation_id' => $reservationId,
        ]);
    }

    /**
     * Mark email as failed.
     */
    public function markFailed(string $uid, string $sender, string $subject, string $otaSource, string $error, ?string $rawBody = null): void
    {
        ProcessedEmail::markProcessed([
            'email_uid'     => $uid,
            'sender'        => $sender,
            'subject'       => Str::limit($subject, 500),
            'status'        => 'failed',
            'email_type'    => 'unknown',
            'ota_source'    => $otaSource,
            'error_message' => Str::limit($error, 500),
            'raw_body'      => $rawBody ? Str::limit($rawBody, 5000) : null,
        ]);
    }
}
