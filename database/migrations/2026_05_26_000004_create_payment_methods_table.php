<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default payment methods
        DB::table('payment_methods')->insert([
            ['name' => 'Cash', 'slug' => 'cash', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bank Transfer', 'slug' => 'bank_transfer', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Credit Card', 'slug' => 'credit_card', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Debit Card', 'slug' => 'debit_card', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
