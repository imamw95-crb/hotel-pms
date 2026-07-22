<?php
require '/www/wwwroot/icon.cloudnod.my.id/vendor/autoload.php';
$app = require_once '/www/wwwroot/icon.cloudnod.my.id/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$ots = app(App\Services\OpenTimestampService::class);
$res = App\Models\Reservation::where('reservation_number', 'RES-6A5F5D59B9672')->first();
if ($res && $res->ots_proof) {
    $result = $ots->timestampInvoice($res, 'issued');
    echo 'OTS re-stamped: ' . ($result ? 'SUCCESS' : 'FAILED') . PHP_EOL;
    $proof = json_decode($res->fresh()->ots_proof, true);
    echo 'Version: ' . ($proof['proof']['ots_version'] ?? 'N/A') . PHP_EOL;
    echo 'Note: ' . ($proof['proof']['note'] ?? 'N/A') . PHP_EOL;
} else {
    echo 'Reservation not found or no existing proof' . PHP_EOL;
}
