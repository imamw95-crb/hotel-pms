<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OutOfOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'room_id',
        'start_date',
        'end_date',
        'reason',
        'notes',
        'status',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
    ];

    // ─── Status Constants ────────────────────────────────────────────

    const STATUS_ACTIVE = 'active';

    const STATUS_COMPLETED = 'completed';

    const STATUSES = [
        self::STATUS_ACTIVE => 'Aktif',
        self::STATUS_COMPLETED => 'Selesai',
    ];

    // ─── Relasi ──────────────────────────────────────────────────────

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Accessors ───────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'bg-red-100 text-red-800 border-red-300',
            self::STATUS_COMPLETED => 'bg-green-100 text-green-800 border-green-300',
            default => 'bg-gray-100 text-gray-600 border-gray-300',
        };
    }

    public function getDurationDaysAttribute(): int
    {
        $start = Carbon::parse($this->start_date);
        $end = $this->end_date ? Carbon::parse($this->end_date) : Carbon::today();

        return (int) $start->diffInDays($end) + 1;
    }

    // ─── Scopes ──────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeForDate($query, $date)
    {
        $dateStr = $date instanceof Carbon ? $date->format('Y-m-d') : $date;

        return $query->where('start_date', '<=', $dateStr)
            ->where(function ($q) use ($dateStr) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $dateStr);
            })
            ->where('status', self::STATUS_ACTIVE);
    }

    public function scopeOverlapping($query, $startDate, $endDate)
    {
        return $query->where('start_date', '<=', $endDate)
            ->where(function ($q) use ($startDate) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $startDate);
            })
            ->where('status', self::STATUS_ACTIVE);
    }
}
