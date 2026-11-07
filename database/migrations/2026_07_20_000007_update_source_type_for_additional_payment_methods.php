<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update source_type untuk semua payment method yang belum terisi
        DB::table('payment_methods')->where('slug', 'cash')->update(['source_type' => 'tunai']);

        DB::table('payment_methods')->whereIn('slug', [
            'bank_transfer', 'bank-transfer-bca', 'transfer_bca', 'virtual_account',
        ])->update(['source_type' => 'transfer']);

        DB::table('payment_methods')->whereIn('slug', [
            'credit_card', 'debit_card', 'edc_bca_room', 'edc_mandiri_room', 'total_edc_other',
        ])->update(['source_type' => 'kartu']);

        DB::table('payment_methods')->whereIn('slug', [
            'qris', 'ewallet', 'qris_bca_room', 'qris_mandiri',
        ])->update(['source_type' => 'e-wallet']);

        DB::table('payment_methods')->whereIn('slug', [
            'tiket.com', 'traveloka.com', 'ota_payment', 'ota-traveloka', 'ota_traveloka', 'ota_tiket_com',
        ])->update(['source_type' => 'ota']);

        // Backfill source_type untuk transaksi lama (portable: jalan di MySQL & SQLite)
        DB::table('payment_methods')
            ->whereNotNull('source_type')
            ->get(['slug', 'source_type'])
            ->each(function ($method) {
                DB::table('transactions')
                    ->whereNull('source_type')
                    ->where('payment_method', $method->slug)
                    ->update(['source_type' => $method->source_type]);
            });
    }

    public function down(): void
    {
        // Tidak perlu rollback data
    }
};
