<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete()->after('created_by');
            $table->foreignId('checked_out_by')->nullable()->constrained('users')->nullOnDelete()->after('checked_in_by');
            $table->timestamp('checked_in_at')->nullable()->after('checked_in_by');
            $table->timestamp('checked_out_at')->nullable()->after('checked_out_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['checked_in_by']);
            $table->dropForeign(['checked_out_by']);
            $table->dropColumn(['checked_in_by', 'checked_out_by', 'checked_in_at', 'checked_out_at']);
        });
    }
};
