<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Tanggal pembayaran sesuai booking date dari OTA
            $table->dateTime('paid_date')->nullable()->after('paid_amount');

            // Index untuk query laporan pembayaran
            $table->index('paid_date', 'ix_paid_date');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropIndex('ix_paid_date');
            $table->dropColumn('paid_date');
        });
    }
};
