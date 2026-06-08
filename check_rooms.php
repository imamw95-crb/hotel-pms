<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Total rooms: " . \App\Models\Room::count() . "\n";
echo "Not OOO: " . \App\Models\Room::whereNotIn('status', ['out_of_order'])->count() . "\n";
echo "By status:\n";
foreach (\App\Models\Room::selectRaw('status, count(*) as c')->groupBy('status')->get() as $r) {
    $s = $r->status === null ? 'NULL' : ($r->status === '' ? 'EMPTY' : $r->status);
    echo "  {$s}: {$r->c}\n";
}
echo "\nAll room numbers and statuses:\n";
foreach (\App\Models\Room::orderBy('room_number')->get() as $r) {
    echo "  {$r->room_number}: {$r->status}\n";
}
