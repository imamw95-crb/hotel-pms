<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotel_settings', function (Blueprint $table) {
            $table->string('cutoff_time', 5)->default('06:00')->after('theme')
                ->comment('Business date cutoff time (HH:mm) — check-in sebelum jam ini dianggap hari sebelumnya');
        });
    }

    public function down(): void
    {
        Schema::table('hotel_settings', function (Blueprint $table) {
            $table->dropColumn('cutoff_time');
        });
    }
};
