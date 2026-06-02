<?php

// database/migrations/xxxx_xx_xx_000006_create_mhs_logs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mhs_logs', function (Blueprint $table) {
            $table->id();
            $table->string('command', 10);
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->boolean('success')->default(false);
            $table->string('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mhs_logs');
    }
};
