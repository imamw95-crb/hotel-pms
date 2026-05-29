<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('processed_emails', function (Blueprint $table) {
            // Change subject to text to handle long email subjects
            $table->text('subject')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('processed_emails', function (Blueprint $table) {
            $table->string('subject')->nullable()->change();
        });
    }
};
