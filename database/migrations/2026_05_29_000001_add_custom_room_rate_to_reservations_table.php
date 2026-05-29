<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->decimal('custom_room_rate', 12, 2)->nullable()->after('total_amount')
                ->comment('Harga kamar per malam khusus untuk reservasi ini. NULL = gunakan harga default kamar.');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn('custom_room_rate');
        });
    }
};
