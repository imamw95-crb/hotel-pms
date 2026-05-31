<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $methods = [
            ['name' => 'tiket.com',       'slug' => 'tiket.com',       'is_active' => true],
            ['name' => 'traveloka.com',   'slug' => 'traveloka.com',   'is_active' => true],
            ['name' => 'OTA Payment',     'slug' => 'ota_payment',     'is_active' => true],
            ['name' => 'Virtual Account', 'slug' => 'virtual_account', 'is_active' => true],
            ['name' => 'E-Wallet',        'slug' => 'ewallet',         'is_active' => true],
            ['name' => 'QRIS',            'slug' => 'qris',            'is_active' => true],
        ];

        foreach ($methods as $method) {
            $exists = DB::table('payment_methods')->where('slug', $method['slug'])->exists();
            if (! $exists) {
                DB::table('payment_methods')->insert(array_merge($method, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    public function down(): void
    {
        DB::table('payment_methods')->whereIn('slug', [
            'tiket.com', 'traveloka.com', 'ota_payment',
            'virtual_account', 'ewallet', 'qris',
        ])->delete();
    }
};
