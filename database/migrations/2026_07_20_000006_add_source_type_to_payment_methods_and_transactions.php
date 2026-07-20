<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom source_type ke payment_methods
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->string('source_type', 30)->nullable()->after('slug');
        });

        // Update source_type berdasarkan slug payment method
        DB::table('payment_methods')->whereIn('slug', ['cash'])->update(['source_type' => 'tunai']);
        DB::table('payment_methods')->whereIn('slug', ['bank_transfer', 'virtual_account'])->update(['source_type' => 'transfer']);
        DB::table('payment_methods')->whereIn('slug', ['credit_card', 'debit_card'])->update(['source_type' => 'kartu']);
        DB::table('payment_methods')->whereIn('slug', ['qris', 'ewallet'])->update(['source_type' => 'e-wallet']);
        DB::table('payment_methods')->whereIn('slug', ['tiket.com', 'traveloka.com', 'ota_payment'])->update(['source_type' => 'ota']);

        // Tambah kolom source_type ke transactions
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('source_type', 30)->nullable()->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('source_type');
        });

        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn('source_type');
        });
    }
};
