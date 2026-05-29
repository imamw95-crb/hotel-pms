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
        Schema::create('service_charges', function (Blueprint $table) {
            $table->id();
            $table->string('charge_number', 30)->unique();
            $table->foreignId('reservation_id')->nullable()->constrained('reservations')->nullOnDelete();
            $table->foreignId('guest_id')->nullable()->constrained('guests')->nullOnDelete();
            $table->string('service_name', 200);
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2);
            $table->integer('quantity')->default(1);
            $table->decimal('total_amount', 12, 2);
            $table->date('charge_date');
            $table->string('payment_method', 50)->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_charges');
    }
};
