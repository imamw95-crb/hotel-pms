<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->string('place_of_birth', 100)->nullable()->after('address');
            $table->date('date_of_birth')->nullable()->after('place_of_birth');
        });
    }

    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropColumn(['place_of_birth', 'date_of_birth']);
        });
    }
};
