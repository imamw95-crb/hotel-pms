<?php

use App\Models\ServiceCharge;

$base = '/www/wwwroot/icon.cloudnod.my.id';
require $base.'/vendor/autoload.php';
$app = require $base.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$sc = ServiceCharge::where('reservation_id', 79)->get();
echo json_encode($sc->toArray(), JSON_PRETTY_PRINT)."\n";
