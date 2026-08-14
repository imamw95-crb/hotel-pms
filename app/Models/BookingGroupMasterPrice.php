<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingGroupMasterPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_group_master_id',
        'room_type_id',
        'price_per_night',
        'include_breakfast',
    ];

    protected $casts = [
        'price_per_night' => 'decimal:2',
        'include_breakfast' => 'boolean',
    ];

    /**
     * Master grup yang memiliki harga ini.
     */
    public function master(): BelongsTo
    {
        return $this->belongsTo(BookingGroupMaster::class, 'booking_group_master_id');
    }

    /**
     * Tipe kamar yang diatur harganya.
     */
    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }
}
