<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('housekeeping_tasks', function (Blueprint $table) {
            $table->timestamp('started_at')->nullable()->after('assigned_to');
            $table->unsignedInteger('duration_minutes')->nullable()->after('completed_at');
            $table->string('photo_before')->nullable()->after('notes');
            $table->string('photo_after')->nullable()->after('photo_before');
            $table->text('room_condition_before')->nullable()->after('photo_after');
            $table->text('room_condition_after')->nullable()->after('room_condition_before');

            $table->index('started_at');
            $table->index('duration_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('housekeeping_tasks', function (Blueprint $table) {
            $table->dropColumn([
                'started_at',
                'duration_minutes',
                'photo_before',
                'photo_after',
                'room_condition_before',
                'room_condition_after',
            ]);
        });
    }
};
