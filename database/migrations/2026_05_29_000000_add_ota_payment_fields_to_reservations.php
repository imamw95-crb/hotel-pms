<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('ota_payment_status')->nullable()->after('ota_reservation_number')
                ->comment('paid_ota, partial_ota, unpaid_ota');
            $table->decimal('ota_paid_amount', 12, 2)->nullable()->after('ota_payment_status')
                ->comment('Nominal yang sudah dibayar oleh OTA');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['ota_payment_status', 'ota_paid_amount']);
        });
    }
};
