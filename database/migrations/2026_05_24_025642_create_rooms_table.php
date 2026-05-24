<?php
// database/migrations/xxxx_xx_xx_000002_create_rooms_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_number', 10)->unique();
            $table->foreignId('room_type_id')->nullable()->constrained('room_types');
            $table->string('room_type_name')->nullable();
            $table->decimal('price_per_night', 12, 2)->default(0);
            $table->integer('max_occupancy')->default(2);
            $table->enum('status', ['available', 'occupied', 'maintenance', 'cleaning'])->default('available');
            $table->text('facilities')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rooms');
    }
};