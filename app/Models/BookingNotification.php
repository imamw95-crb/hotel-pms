<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingNotification extends Model
{
    protected $fillable = [
        'type',
        'action',
        'reservation_id',
        'guest_name',
        'room_number',
        'ota_source',
        'ota_reservation_number',
        'message',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Scope: only unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope: recent notifications.
     */
    public function scopeRecent($query, int $limit = 20)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Mark as read.
     */
    public function markAsRead(): void
    {
        $this->update(['is_read' => true, 'read_at' => now()]);
    }

    /**
     * Mark all notifications as read.
     */
    public static function markAllAsRead(): void
    {
        static::unread()->update(['is_read' => true, 'read_at' => now()]);
    }
}
