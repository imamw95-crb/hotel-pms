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
        Schema::create('booking_group_master_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_group_master_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->decimal('price_per_night', 12, 2)->default(0);
            $table->boolean('include_breakfast')->default(true);
            $table->timestamps();

            $table->unique(['booking_group_master_id', 'room_type_id'], 'bgm_price_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_group_master_prices');
    }
};
