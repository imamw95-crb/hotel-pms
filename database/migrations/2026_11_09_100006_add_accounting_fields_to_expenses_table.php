<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('expense_category', 50)->nullable()->after('description');
            $table->foreignId('account_id')->nullable()->after('expense_category')->constrained('accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('account_id');
            $table->dropColumn('expense_category');
        });
    }
};
