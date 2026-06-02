<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('processed_emails', function (Blueprint $table) {
            // Add raw_body for debugging/reprocessing
            $table->longText('raw_body')->nullable()->after('subject');

            // Add retry count for failed emails
            $table->unsignedTinyInteger('retry_count')->default(0)->after('status');

            // Index for cleanup queries
            $table->index(['status', 'created_at'], 'ix_status_created');
        });
    }

    public function down(): void
    {
        Schema::table('processed_emails', function (Blueprint $table) {
            $table->dropIndex('ix_status_created');
            $table->dropColumn(['raw_body', 'retry_count']);
        });
    }
};
