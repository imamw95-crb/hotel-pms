<?php
require '/www/wwwroot/icon.cloudnod.my.id/vendor/autoload.php';
$app = require_once '/www/wwwroot/icon.cloudnod.my.id/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle($request = Illuminate\Http\Request::capture());

$sc = App\Models\ServiceCharge::where('charge_number', 'SC-6A6540F107072')->first();
if (!$sc) { echo "SC not found!\n"; exit; }
printf("ID: %d\n", $sc->id);
printf("Charge#: %s\n", $sc->charge_number);
printf("Charge Date: %s\n", $sc->charge_date);
printf("Created At: %s\n", $sc->created_at);
printf("Service Name: %s\n", $sc->service_name);
printf("Qty: %d\n", $sc->quantity);
printf("Total: %.2f\n", $sc->total_amount);
printf("Payment Method: %s\n", $sc->payment_method ?? '-');
printf("Guest ID: %s\n", $sc->guest_id ?? 'NULL');
printf("Reservation ID: %s\n", $sc->reservation_id ?? 'NULL');
printf("Notes: %s\n", $sc->notes ?? '-');

if ($sc->reservation) {
    printf("Res#: %s\n", $sc->reservation->reservation_number);
    printf("Res Status: %s\n", $sc->reservation->status);
    printf("Res Check-in: %s\n", $sc->reservation->check_in);
    printf("Res Check-out: %s\n", $sc->reservation->check_out);
    printf("Res Guest: %s\n", $sc->reservation->guest->guest_name ?? '-');
    printf("Res Room: %s\n", $sc->reservation->room->room_number ?? '-');
}

echo "\n=== Check if charge_date within biz range (2026-07-25 06:00 - 2026-07-26 06:00) ===\n";
$bizStart = '2026-07-25 06:00:00';
$bizEnd = '2026-07-26 06:00:00';
$cd = $sc->charge_date;
printf("charge_date: %s\n", $cd);
printf(">= bizStart (%s): %s\n", $bizStart, $cd >= $bizStart ? 'YES' : 'NO');
printf("< bizEnd (%s): %s\n", $bizEnd, $cd < $bizEnd ? 'YES' : 'NO');
