<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('housekeeping_inventories', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->integer('quantity')->default(0);
            $table->integer('min_quantity')->default(0);
            $table->string('unit')->default('pcs'); // pcs, bottle, box, pack, liter
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('item_name');
            $table->index(['quantity', 'min_quantity'], 'hk_inv_qty_min_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('housekeeping_inventories');
    }
};
