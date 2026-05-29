<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelSetting extends Model
{
    protected $table = 'hotel_settings';

    protected $fillable = [
        'hotel_name', 'phone', 'email', 'address', 'logo_path', 'website', 'theme',
    ];

    /**
     * Get the singleton setting instance.
     */
    public static function get(): self
    {
        return self::first() ?? self::create(['hotel_name' => 'Hotel PMS']);
    }
}
