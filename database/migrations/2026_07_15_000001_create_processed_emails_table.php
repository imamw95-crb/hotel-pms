<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('processed_emails', function (Blueprint $table) {
            $table->id();
            $table->string('email_uid')->index();
            $table->string('sender');
            $table->string('subject')->nullable();
            $table->enum('status', ['processed', 'failed', 'duplicate', 'skipped'])->default('processed');
            $table->string('email_type')->nullable()->comment('booking, cancellation, modification, unknown');
            $table->string('ota_source')->nullable()->comment('tiket.com, traveloka.com');
            $table->string('reservation_id')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->unique(['email_uid', 'sender']);
            $table->index('processed_at');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processed_emails');
    }
};
