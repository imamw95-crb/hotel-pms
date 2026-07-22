<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Blameable;

class NightAuditLog extends Model
{
    use Blameable;

    protected $fillable = [
        'audit_date', 'status',
        'total_rooms', 'occupied_rooms', 'available_rooms', 'maintenance_rooms', 'occupancy_rate',
        'room_revenue', 'resto_revenue', 'sc_revenue', 'total_revenue',
        'checkins_count', 'checkouts_count', 'in_house_count', 'new_bookings_count',
        'snapshot_data', 'draft_notes', 'locked_by', 'locked_at', 'created_by',
    ];

    protected $casts = [
        'audit_date' => 'date',
        'snapshot_data' => 'array',
        'locked_at' => 'datetime',
        'occupancy_rate' => 'decimal:2',
    ];

    public function lock(): void
    {
        $this->update([
            'status' => 'locked',
            'locked_by' => auth()->id(),
            'locked_at' => now(),
        ]);
    }

    public function isLocked(): bool
    {
        return $this->status === 'locked';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }
}
