<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessedEmail extends Model
{
    protected $fillable = [
        'email_uid',
        'sender',
        'subject',
        'status',
        'email_type',
        'ota_source',
        'reservation_id',
        'error_message',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    /**
     * Check if an email UID has already been processed by this sender.
     */
    public static function isProcessed(string $uid, string $sender): bool
    {
        return static::where('email_uid', $uid)
            ->where('sender', $sender)
            ->whereIn('status', ['processed', 'duplicate'])
            ->exists();
    }

    /**
     * Mark an email as processed.
     */
    public static function markProcessed(array $data): self
    {
        return static::updateOrCreate(
            ['email_uid' => $data['email_uid'], 'sender' => $data['sender']],
            array_merge($data, ['processed_at' => now()])
        );
    }
}
