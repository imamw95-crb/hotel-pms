<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotel_settings', function (Blueprint $table) {
            $table->string('ai_model', 150)->nullable()->after('cutoff_time')
                ->comment('OpenRouter model id — overrides services.openrouter.model when set');
        });
    }

    public function down(): void
    {
        Schema::table('hotel_settings', function (Blueprint $table) {
            $table->dropColumn('ai_model');
        });
    }
};
