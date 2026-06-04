<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotel_settings', function (Blueprint $table) {
            $table->string('company_video_path')->nullable()->after('logo_path');
            $table->string('company_video_url')->nullable()->after('company_video_path');
            $table->integer('tv_refresh_interval')->default(30)->after('company_video_url');
            $table->string('tv_welcome_message')->nullable()->after('tv_refresh_interval');
        });
    }

    public function down(): void
    {
        Schema::table('hotel_settings', function (Blueprint $table) {
            $table->dropColumn(['company_video_path', 'company_video_url', 'tv_refresh_interval', 'tv_welcome_message']);
        });
    }
};
