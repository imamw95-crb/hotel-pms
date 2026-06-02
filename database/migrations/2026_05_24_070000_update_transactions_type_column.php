<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('type', 20)->change();
            $table->string('payment_method', 20)->default('cash')->change();
        });
    }

    public function down()
    {
        //
    }
};
