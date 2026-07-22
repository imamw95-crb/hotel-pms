<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Membuat tabel invoice_timestamp untuk menyimpan bukti OpenTimestamps
     * setiap versi invoice. Mendukung multi-revision.
     */
    public function up(): void
    {
        Schema::create('invoice_timestamps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->string('invoice_type', 50)->default('reservation')
                ->comment('Tipe invoice: reservation, transaction');
            $table->unsignedTinyInteger('revision')->default(0);
            $table->string('sha256', 64)->charset('ascii');
            $table->longText('ots_file')->nullable()
                ->comment('Base64-encoded .ots binary proof file');
            $table->string('ots_status', 20)->default('pending')
                ->comment('pending, confirming, confirmed, failed');
            $table->string('calendar', 255)->nullable()
                ->comment('OpenTimestamps calendar URL');
            $table->string('bitcoin_txid', 64)->nullable()->charset('ascii');
            $table->unsignedInteger('bitcoin_block')->nullable();
            $table->string('bitcoin_block_hash', 64)->nullable()->charset('ascii');
            $table->timestamp('timestamped_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['invoice_id', 'invoice_type']);
            $table->index(['invoice_id', 'invoice_type', 'revision']);
            $table->index('ots_status');
            $table->index('sha256');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_timestamps');
    }
};
