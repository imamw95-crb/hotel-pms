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
        Schema::table('allotments', function (Blueprint $table) {
            $table->decimal('price', 12, 2)->nullable()->after('booked')
                ->comment('Harga spesial per malam untuk allotment ini. Null = pakai harga master kamar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('allotments', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};
