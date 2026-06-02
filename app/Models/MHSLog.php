<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MHSLog extends Model
{
    protected $table = 'mhs_logs';

    protected $fillable = [
        'command', 'reservation_id', 'request_data', 'response_data', 'success', 'error_message',
    ];

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
