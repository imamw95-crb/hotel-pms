<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Drop unique constraint — multi-room OTA bookings may share same number
            $table->dropUnique('uq_ota_reservation_number');

            // Replace with regular index for performance
            $table->index('ota_reservation_number', 'ix_ota_reservation_number');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropIndex('ix_ota_reservation_number');

            // Restore unique constraint
            $table->unique('ota_reservation_number', 'uq_ota_reservation_number');
        });
    }
};
