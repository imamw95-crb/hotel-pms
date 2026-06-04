<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

use App\Models\BookingNotification;
use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Room;
use App\Services\BookingNotificationService;
use Carbon\Carbon;
use Illuminate\Contracts\Console\Kernel;

echo "=== SEBELUM ===\n";
echo 'Notifications: '.BookingNotification::count()."\n\n";

// Test: create notification via service like BookingController would
$room = Room::where('status', '!=', 'maintenance')->first();
$guest = Guest::first();

if (! $room || ! $guest) {
    echo "ERROR: Need room and guest in database\n";
    exit(1);
}

echo "Using Room: {$room->room_number} (ID: {$room->id})\n";
echo "Using Guest: {$guest->guest_name} (ID: {$guest->id})\n\n";

// Simulasi: BookingController store() untuk OTA booking
$reservation = Reservation::create([
    'reservation_number' => 'TEST-OTA-'.substr(strtoupper(uniqid()), -6),
    'ota_reservation_number' => 'TVL-TEST-'.date('Ymd'),
    'ota_source' => 'traveloka.com',
    'room_id' => $room->id,
    'guest_id' => $guest->id,
    'check_in' => Carbon::tomorrow()->setTime(12, 0, 0),
    'check_out' => Carbon::tomorrow()->addDays(2)->setTime(12, 0, 0),
    'status' => 'pending',
    'total_amount' => 500000,
    'paid_amount' => 0,
    'created_by' => 1,
]);

echo "Created reservation: {$reservation->reservation_number}\n";

// Panggil notif dengan fix baru
$service = app(BookingNotificationService::class);
$service->otaBookingCreated(
    $reservation,
    [
        'guest_name' => $guest->guest_name,
        'reservation_id' => 'TVL-TEST-'.date('Ymd'),
    ],
    'traveloka.com'
);

echo "\n=== SESUDAH ===\n";
echo 'Notifications: '.BookingNotification::count()."\n";
foreach (BookingNotification::latest()->take(5)->get() as $n) {
    echo "[{$n->id}] {$n->type}/{$n->action} - {$n->message}\n";
    echo "     is_read: {$n->is_read} | ota: {$n->ota_source}\n\n";
}

// Cleanup test data
echo "=== CLEANUP ===\n";
$reservation->delete();
BookingNotification::latest()->first()?->delete();
echo "Done\n";
