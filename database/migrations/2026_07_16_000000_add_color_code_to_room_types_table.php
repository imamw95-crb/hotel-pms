<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->string('color_code', 7)->nullable()->after('description')
                ->comment('Hex color code for room type badge (e.g., #3B82F6)');
        });
    }

    public function down()
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->dropColumn('color_code');
        });
    }
};