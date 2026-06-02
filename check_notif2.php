<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\BookingNotification;
use App\Models\Reservation;
use App\Models\ProcessedEmail;
use App\Models\MHSLog;
use Carbon\Carbon;

echo "=== PROCESSED EMAILS ===\n";
echo "Total: " . ProcessedEmail::count() . "\n";
foreach(ProcessedEmail::latest()->take(5)->get() as $e) {
    echo "[{$e->id}] {$e->ota_source} - {$e->status} - {$e->subject} ({$e->created_at})\n";
}

echo "\n=== OTA RESERVATIONS DETAILS ===\n";
$otas = Reservation::whereNotNull('ota_source')->where('ota_source', '!=', '')->where('ota_source', '!=', 'website')->get();
foreach($otas as $r) {
    echo "[{$r->id}] {$r->reservation_number}\n";
    echo "  ota_source: {$r->ota_source}\n";
    echo "  ota_reservation_number: {$r->ota_reservation_number}\n";
    echo "  status: {$r->status}\n";
    echo "  created_at: {$r->created_at}\n";
    echo "  updated_at: {$r->updated_at}\n";
    echo "  check_in: {$r->check_in}\n";
    echo "  check_out: {$r->check_out}\n";
    echo "\n";
}

echo "\n=== MHS LOGS ===\n";
echo "Total: " . MHSLog::count() . "\n";
foreach(MHSLog::latest()->take(5)->get() as $log) {
    echo "[{$log->id}] {$log->level} - {$log->message} ({$log->created_at})\n";
}

echo "\n=== RESERVATION CREATED TODAY ===\n";
$today = Reservation::whereDate('created_at', Carbon::today())->count();
echo "Today: {$today}\n";
$all = Reservation::count();
echo "Total all reservations: {$all}\n";
