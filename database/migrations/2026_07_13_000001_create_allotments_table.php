<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('allotments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained('room_types')->cascadeOnDelete();
            $table->date('date');
            $table->integer('allotment')->default(0)->comment('Jumlah maksimal kamar yang dialokasikan');
            $table->integer('booked')->default(0)->comment('Jumlah kamar yang sudah terbooking');
            $table->string('channel', 50)->nullable()->comment('Saluran: api, ota, website, null = semua');
            $table->timestamps();

            // Unique per room_type + date + channel
            $table->unique(['room_type_id', 'date', 'channel']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('allotments');
    }
};
