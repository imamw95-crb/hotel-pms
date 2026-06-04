<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

use App\Models\BookingNotification;
use App\Models\Reservation;
use Illuminate\Contracts\Console\Kernel;

echo "=== BOOKING NOTIFICATIONS ===\n";
echo 'Total: '.BookingNotification::count()."\n";
echo 'Unread: '.BookingNotification::where('is_read', false)->count()."\n\n";

foreach (BookingNotification::latest()->take(10)->get() as $n) {
    echo "[{$n->id}] {$n->type}/{$n->action} - {$n->message} (read:{$n->is_read})\n";
    echo "     ota_source: {$n->ota_source} | reservation_id: {$n->reservation_id}\n\n";
}

echo "\n=== OTA RESERVATIONS ===\n";
$otas = Reservation::whereNotNull('ota_source')->where('ota_source', '!=', '')->where('ota_source', '!=', 'website')->latest()->take(5)->get();
echo 'Total OTA reservations: '.Reservation::whereNotNull('ota_source')->where('ota_source', '!=', '')->where('ota_source', '!=', 'website')->count()."\n";
foreach ($otas as $r) {
    echo "[{$r->id}] {$r->reservation_number} - ota:{$r->ota_source} / ref:{$r->ota_reservation_number} - status:{$r->status}\n";
}
