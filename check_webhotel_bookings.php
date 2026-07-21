<?php
require '/home/u102361870/domains/theicon.id/public_html/webhotel/vendor/autoload.php';
$app = require '/home/u102361870/domains/theicon.id/public_html/webhotel/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$bookings = App\Models\Booking::latest()->take(10)->get();
echo 'Count: ' . App\Models\Booking::count() . PHP_EOL;
foreach ($bookings as $b) {
    echo '[' . $b->created_at . '] ' . $b->booking_code . ' - ' . $b->status . ' - PMS: ' . ($b->pms_reservation_number ?? 'NO PMS') . ' - ' . $b->name . PHP_EOL;
}
