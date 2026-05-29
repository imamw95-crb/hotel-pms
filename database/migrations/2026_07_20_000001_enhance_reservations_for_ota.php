<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Track OTA source (tiket.com, traveloka.com, etc.)
            $table->string('ota_source')->nullable()->after('ota_reservation_number');

            // Unique index on OTA reservation number to prevent duplicates
            $table->unique('ota_reservation_number', 'uq_ota_reservation_number');

            // Index for OTA lookups
            $table->index(['ota_source', 'status'], 'ix_ota_source_status');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropUnique('uq_ota_reservation_number');
            $table->dropIndex('ix_ota_source_status');
            $table->dropColumn('ota_source');
        });
    }
};
