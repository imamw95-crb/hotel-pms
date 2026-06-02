<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Remove old default payment methods
        DB::table('payment_methods')->whereIn('slug', [
            'cash', 'bank_transfer', 'credit_card', 'debit_card',
        ])->delete();

        // Insert new payment methods
        $methods = [
            ['name' => 'Cash',                 'slug' => 'cash',                   'is_active' => true],
            ['name' => 'EDC BCA Room',         'slug' => 'edc_bca_room',          'is_active' => true],
            ['name' => 'EDC Mandiri Room',     'slug' => 'edc_mandiri_room',      'is_active' => true],
            ['name' => 'QRIS BCA Room',        'slug' => 'qris_bca_room',         'is_active' => true],
            ['name' => 'QRIS Mandiri',         'slug' => 'qris_mandiri',          'is_active' => true],
            ['name' => 'Transfer BCA',         'slug' => 'transfer_bca',          'is_active' => true],
            ['name' => 'OTA Traveloka',        'slug' => 'ota_traveloka',         'is_active' => true],
            ['name' => 'OTA Tiket.com',        'slug' => 'ota_tiket_com',         'is_active' => true],
            ['name' => 'Total EDC Other',      'slug' => 'total_edc_other',       'is_active' => true],
        ];

        $now = now();
        foreach ($methods as &$method) {
            $method['created_at'] = $now;
            $method['updated_at'] = $now;
        }

        DB::table('payment_methods')->insert($methods);
    }

    public function down(): void
    {
        DB::table('payment_methods')->whereIn('slug', [
            'cash', 'edc_bca_room', 'edc_mandiri_room', 'qris_bca_room',
            'qris_mandiri', 'transfer_bca', 'ota_traveloka', 'ota_tiket_com',
            'total_edc_other',
        ])->delete();
    }
};
