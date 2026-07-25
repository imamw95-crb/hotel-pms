<?php
require '/www/wwwroot/icon.cloudnod.my.id/vendor/autoload.php';
$app = require_once '/www/wwwroot/icon.cloudnod.my.id/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle($request = Illuminate\Http\Request::capture());

$date = '2026-07-25';
$bizStart = $date . ' 06:00:00';
$bizEnd = '2026-07-26 06:00:00';

echo "=== CHECK-INS (checked_in, check_in within range) ===\n";
$checkins = App\Models\Reservation::where('check_in', '>=', $bizStart)
    ->where('check_in', '<', $bizEnd)
    ->where('status', 'checked_in')
    ->with('guest', 'room')
    ->get();
foreach ($checkins as $r) {
    printf("  ID: %d | Res: %s | Guest: %s | Room: %s | CI: %s\n", $r->id, $r->reservation_number, $r->guest->guest_name ?? '-', $r->room->room_number ?? '-', $r->check_in);
}
echo "Total: " . count($checkins) . "\n\n";

echo "=== IN-HOUSE Guests ===\n";
$inhouse = App\Models\Reservation::where(function($q) use ($date) {
    $q->where('status', 'checked_in')
      ->orWhere(function($sub) use ($date) {
          $sub->where('status', 'checked_out')->whereDate('check_out', $date);
      });
})
    ->with('guest', 'room')
    ->orderBy('check_out', 'asc')
    ->get();
foreach ($inhouse as $r) {
    printf("  ID: %d | Res: %s | Status: %s | Guest: %s | Room: %s | CI: %s | CO: %s\n", $r->id, $r->reservation_number, $r->status, $r->guest->guest_name ?? '-', $r->room->room_number ?? '-', $r->check_in, $r->check_out);
}
echo "Total: " . count($inhouse) . "\n\n";

echo "=== RES 606 check ===\n";
$r = App\Models\Reservation::with('room')->find(606);
printf("Status: %s, CI: %s, CO: %s\n", $r->status, $r->check_in, $r->check_out);
printf("Room: %s, Room Status: %s\n", $r->room->room_number ?? '-', $r->room->status ?? 'N/A');
printf("CI >= bizStart (%s): %s\n", $bizStart, $r->check_in >= $bizStart ? 'YES' : 'NO');
printf("CI < bizEnd (%s): %s\n", $bizEnd, $r->check_in < $bizEnd ? 'YES' : 'NO');
