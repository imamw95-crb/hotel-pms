<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountingPeriod extends Model
{
    public const STATUS_OPEN = 'open';

    public const STATUS_CLOSED = 'closed';

    protected $fillable = ['period', 'status', 'closed_at', 'closed_by'];

    protected $casts = [
        'closed_at' => 'datetime',
    ];

    public static function forDate($date): ?self
    {
        return static::where('period', static::periodKey($date))->first();
    }

    public static function periodKey($date): string
    {
        $date = $date instanceof \DateTimeInterface ? Carbon::instance($date) : Carbon::parse($date);

        return $date->format('Y-m');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->isClosed() ? 'Ditutup' : 'Terbuka';
    }
}
