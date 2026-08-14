<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Kolom include_breakfast ditambahkan oleh 2026_07_20_000006.
        // Lewati jika belum ada (misal migrasi fresh) agar tidak gagal.
        if (Schema::hasColumn('reservations', 'include_breakfast')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->boolean('include_breakfast')->default(true)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->boolean('include_breakfast')->default(false)->change();
        });
    }
};
