<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MHSLog;

echo "=== ALL MHS LOGS ===\n";
foreach(MHSLog::latest()->take(20)->get() as $log) {
    echo "[{$log->id}] level:{$log->level} | msg:{$log->message} | ctx:{$log->context} | {$log->created_at}\n";
}
