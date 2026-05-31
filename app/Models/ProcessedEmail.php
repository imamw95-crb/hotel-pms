<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

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
        'raw_body',
        'retry_count',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    // ─── Scopes ─────────────────────────────────────────────────────────

    /**
     * Scope: filter by status.
     */
    public function scopeWhereStatus(Builder $q, ?string $status): Builder
    {
        return $status ? $q->where('status', $status) : $q;
    }

    /**
     * Scope: filter by OTA source.
     */
    public function scopeWhereOta(Builder $q, ?string $source): Builder
    {
        return $source ? $q->where('ota_source', $source) : $q;
    }

    /**
     * Scope: filter by email type.
     */
    public function scopeWhereEmailType(Builder $q, ?string $type): Builder
    {
        return $type ? $q->where('email_type', $type) : $q;
    }

    /**
     * Scope: filter by date range.
     */
    public function scopeWhereDateRange(Builder $q, ?string $from, ?string $to): Builder
    {
        if ($from) {
            $q->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $q->whereDate('created_at', '<=', $to);
        }

        return $q;
    }

    /**
     * Scope: search by subject or sender.
     */
    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        if ($term) {
            $q->where(function (Builder $sub) use ($term) {
                $sub->where('subject', 'like', "%{$term}%")
                    ->orWhere('sender', 'like', "%{$term}%");
            });
        }

        return $q;
    }

    /**
     * Scope: only emails that failed.
     */
    public function scopeFailed(Builder $q): Builder
    {
        return $q->where('status', 'failed');
    }

    /**
     * Scope: only successful processings.
     */
    public function scopeSuccessful(Builder $q): Builder
    {
        return $q->where('status', 'processed');
    }

    // ─── Accessors ──────────────────────────────────────────────────────

    /**
     * Get status label with proper formatting.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'processed' => 'Berhasil',
            'failed' => 'Gagal',
            'duplicate' => 'Duplikat',
            'skipped' => 'Dilewati',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get status badge color class.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'processed' => 'bg-emerald-100 text-emerald-700',
            'failed' => 'bg-red-100 text-red-700',
            'duplicate' => 'bg-gray-100 text-gray-600',
            'skipped' => 'bg-yellow-100 text-yellow-700',
            default => 'bg-gray-100 text-gray-600',
        };
    }

    /**
     * Get email type label.
     */
    public function getEmailTypeLabelAttribute(): string
    {
        return match ($this->email_type) {
            'booking' => 'Booking Baru',
            'cancellation' => 'Pembatalan',
            'modification' => 'Modifikasi',
            'unknown' => 'Tidak Diketahui',
            default => ucfirst($this->email_type),
        };
    }

    // ─── Statistics ─────────────────────────────────────────────────────

    /**
     * Get summary stats for the monitoring dashboard.
     */
    public static function getStats(): array
    {
        $cacheKey = 'ota_email_stats';

        return Cache::remember($cacheKey, now()->addMinutes(2), function () {
            $today = now()->startOfDay();

            return [
                'total' => static::count(),
                'today' => static::where('created_at', '>=', $today)->count(),
                'processed' => static::where('status', 'processed')->count(),
                'failed' => static::where('status', 'failed')->count(),
                'duplicate' => static::where('status', 'duplicate')->count(),
                'skipped' => static::where('status', 'skipped')->count(),
                'failed_today' => static::where('status', 'failed')
                    ->where('created_at', '>=', $today)->count(),
                'by_source' => static::selectRaw('ota_source, COUNT(*) as total')
                    ->whereNotNull('ota_source')
                    ->groupBy('ota_source')
                    ->pluck('total', 'ota_source')
                    ->toArray(),
                'by_type' => static::selectRaw('email_type, COUNT(*) as total')
                    ->whereNotNull('email_type')
                    ->groupBy('email_type')
                    ->pluck('total', 'email_type')
                    ->toArray(),
                'latest' => static::latest()->limit(10)->get(),
            ];
        });
    }

    /**
     * Clear stats cache (call after new email is processed).
     */
    public static function clearStatsCache(): void
    {
        Cache::forget('ota_email_stats');
    }

    // ─── Original Methods ───────────────────────────────────────────────

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
        $record = static::updateOrCreate(
            ['email_uid' => $data['email_uid'], 'sender' => $data['sender']],
            array_merge($data, ['processed_at' => now()])
        );

        // Clear stats cache so dashboard refreshes
        static::clearStatsCache();

        return $record;
    }
}
