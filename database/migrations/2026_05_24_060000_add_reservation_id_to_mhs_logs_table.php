<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('mhs_logs', function (Blueprint $table) {
            $table->foreignId('reservation_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('mhs_logs', function (Blueprint $table) {
            $table->dropForeign(['reservation_id']);
            $table->dropColumn('reservation_id');
        });
    }
};
