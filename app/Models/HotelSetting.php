<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class HotelSetting extends Model
{
    protected $table = 'hotel_settings';

    protected $fillable = [
        'hotel_name', 'phone', 'email', 'address', 'logo_path', 'website', 'theme',
        'company_video_path', 'company_video_url', 'tv_refresh_interval', 'tv_welcome_message',
        'cutoff_time',
    ];

    /**
     * Get the singleton setting instance (cached for 24 hours).
     */
    public static function get(): self
    {
        $value = Cache::get('hotel_settings');

        // If cache is corrupted (e.g. stale serialized class), re-fetch fresh
        if ($value !== null && (! $value instanceof self)) {
            Cache::forget('hotel_settings');
            $value = null;
        }

        if ($value === null) {
            $value = self::first() ?? self::create(['hotel_name' => 'Dynamic PMS V.2']);
            Cache::put('hotel_settings', $value, 86400);
        }

        return $value;
    }

    /**
     * Clear the cached settings (call after updating settings).
     */
    public static function forgetCache(): void
    {
        Cache::forget('hotel_settings');
    }
}
