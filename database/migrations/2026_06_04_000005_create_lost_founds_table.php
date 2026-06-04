<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lost_founds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('housekeeping_task_id')->nullable()->constrained('housekeeping_tasks')->onDelete('set null');
            $table->foreignId('room_id')->nullable()->constrained('rooms')->onDelete('set null');
            $table->string('guest_name')->nullable();
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->date('found_date');
            $table->string('status')->default('reported'); // reported, claimed, disposed
            $table->string('claimed_by')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('storage_location')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('status');
            $table->index('found_date');
            $table->index('item_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lost_founds');
    }
};
