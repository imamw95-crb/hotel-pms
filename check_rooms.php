<?php

use App\Models\Room;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

echo 'Total rooms: '.Room::count()."\n";
echo 'Not OOO: '.Room::whereNotIn('status', ['out_of_order'])->count()."\n";
echo "By status:\n";
foreach (Room::selectRaw('status, count(*) as c')->groupBy('status')->get() as $r) {
    $s = $r->status === null ? 'NULL' : ($r->status === '' ? 'EMPTY' : $r->status);
    echo "  {$s}: {$r->c}\n";
}
echo "\nAll room numbers and statuses:\n";
foreach (Room::orderBy('room_number')->get() as $r) {
    echo "  {$r->room_number}: {$r->status}\n";
}
