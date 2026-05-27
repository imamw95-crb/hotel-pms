<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->decimal('price_weekday', 12, 2)->default(0)->after('price_per_night');
            $table->decimal('price_weekend', 12, 2)->default(0)->after('price_weekday');
        });
    }

    public function down()
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['price_weekday', 'price_weekend']);
        });
    }
};
