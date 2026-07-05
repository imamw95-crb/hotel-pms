<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mhs_logs', function (Blueprint $table) {
            $table->string('command', 50)->change();
        });
    }

    public function down(): void
    {
        Schema::table('mhs_logs', function (Blueprint $table) {
            $table->string('command', 10)->change();
        });
    }
};
