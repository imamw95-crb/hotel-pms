<?php
// database/migrations/xxxx_xx_xx_000004_create_reservations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('reservation_number', 20)->unique();
            $table->foreignId('room_id')->constrained();
            $table->foreignId('guest_id')->constrained();
            $table->dateTime('check_in');
            $table->dateTime('check_out');
            $table->integer('number_of_cards')->default(1);
            $table->enum('status', ['pending', 'checked_in', 'checked_out', 'cancelled', 'no_show'])->default('pending');
            $table->decimal('total_amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reservations');
    }
};