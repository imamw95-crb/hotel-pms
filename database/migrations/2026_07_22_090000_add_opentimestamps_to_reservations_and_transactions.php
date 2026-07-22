<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->text('ots_proof')->nullable()->after('invoice_signature');
            $table->timestamp('ots_timestamped_at')->nullable()->after('ots_proof');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->text('ots_proof')->nullable()->after('notes');
            $table->timestamp('ots_timestamped_at')->nullable()->after('ots_proof');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['ots_proof', 'ots_timestamped_at']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['ots_proof', 'ots_timestamped_at']);
        });
    }
};
