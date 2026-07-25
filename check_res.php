<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$r = App\Models\Reservation::with('guest', 'room')->find(606);
if (!$r) {
    echo "Reservation 606 not found!\n";
    exit;
}
echo "ID: {$r->id}\n";
echo "Res#: {$r->reservation_number}\n";
echo "Status: {$r->status}\n";
echo "Guest: {$r->guest->guest_name}\n";
echo "Room: {$r->room->room_number}\n";
echo "Check-in: {$r->check_in}\n";
echo "Check-out: {$r->check_out}\n";
echo "Total: {$r->total_amount}\n";
echo "Paid: {$r->paid_amount}\n";
echo "Created: {$r->created_at}\n";
echo "Nights: {$r->nights}\n";
echo "OTA source: {$r->ota_source}\n";
echo "Payment method: {$r->payment_method}\n";
echo "Include breakfast: {$r->include_breakfast}\n";
