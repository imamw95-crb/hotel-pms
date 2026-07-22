<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Blameable;

class MHSLog extends Model
{
    use Blameable;

    protected $table = 'mhs_logs';

    protected $fillable = [
        'command', 'reservation_id', 'created_by', 'request_data', 'response_data', 'success', 'error_message',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
        'success' => 'boolean',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
