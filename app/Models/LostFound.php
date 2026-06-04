<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LostFound extends Model
{
    use HasFactory;

    protected $fillable = [
        'housekeeping_task_id',
        'room_id',
        'guest_name',
        'item_name',
        'description',
        'found_date',
        'status',
        'claimed_by',
        'claimed_at',
        'photo_path',
        'storage_location',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'found_date' => 'date',
        'claimed_at' => 'datetime',
    ];

    const STATUSES = [
        'reported' => 'Dilaporkan',
        'claimed' => 'Sudah Diambil',
        'disposed' => 'Dibuang',
    ];

    // ─── Relasi ───────────────────────────────────────────────────────

    public function housekeepingTask(): BelongsTo
    {
        return $this->belongsTo(HousekeepingTask::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Accessors ────────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'reported' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
            'claimed' => 'bg-green-100 text-green-800 border-green-300',
            'disposed' => 'bg-gray-100 text-gray-500 border-gray-300',
            default => 'bg-gray-100 text-gray-600 border-gray-300',
        };
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? asset('storage/'.$this->photo_path) : null;
    }

    // ─── Scopes ───────────────────────────────────────────────────────

    public function scopeReported($query)
    {
        return $query->where('status', 'reported');
    }

    public function scopeByDateRange($query, $start, $end)
    {
        return $query->whereBetween('found_date', [$start, $end]);
    }
}
