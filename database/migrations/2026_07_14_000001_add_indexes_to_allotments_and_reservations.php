<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('allotments', function (Blueprint $table) {
            $table->index(['room_type_id', 'date', 'channel'], 'idx_allotments_type_date_channel');
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->index(['room_id', 'check_in', 'check_out', 'status'], 'idx_reservations_room_dates_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('allotments', function (Blueprint $table) {
            $table->dropIndex('idx_allotments_type_date_channel');
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropIndex('idx_reservations_room_dates_status');
        });
    }
};
