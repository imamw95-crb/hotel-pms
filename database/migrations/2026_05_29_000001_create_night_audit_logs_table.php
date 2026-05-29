<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('night_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->date('audit_date')->unique();
            $table->enum('status', ['draft', 'locked'])->default('draft');

            // Room summary
            $table->integer('total_rooms')->default(0);
            $table->integer('occupied_rooms')->default(0);
            $table->integer('available_rooms')->default(0);
            $table->integer('maintenance_rooms')->default(0);
            $table->decimal('occupancy_rate', 5, 2)->default(0);

            // Revenue summary
            $table->decimal('room_revenue', 15, 2)->default(0);
            $table->decimal('resto_revenue', 15, 2)->default(0);
            $table->decimal('sc_revenue', 15, 2)->default(0);
            $table->decimal('total_revenue', 15, 2)->default(0);

            // Counts
            $table->integer('checkins_count')->default(0);
            $table->integer('checkouts_count')->default(0);
            $table->integer('in_house_count')->default(0);
            $table->integer('new_bookings_count')->default(0);

            // Full snapshot data (JSON)
            $table->json('snapshot_data')->nullable();

            // Notes & metadata
            $table->text('draft_notes')->nullable();
            $table->foreignId('locked_by')->nullable()->constrained('users');
            $table->timestamp('locked_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('night_audit_logs');
    }
};
