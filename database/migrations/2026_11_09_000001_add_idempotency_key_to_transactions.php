<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Token idempotensi per submit form pembayaran — mencegah satu pembayaran
     * tercatat dua kali saat tombol diklik ganda / request diulang.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('idempotency_key', 64)->nullable()->unique()->after('source_type');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropUnique(['idempotency_key']);
            $table->dropColumn('idempotency_key');
        });
    }
};
