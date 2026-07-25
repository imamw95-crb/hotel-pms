<?php
$base = '/www/wwwroot/icon.cloudnod.my.id';
require $base . '/vendor/autoload.php';

$app = require_once $base . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Show all columns in transactions table
$cols = DB::select('SHOW COLUMNS FROM transactions');
echo "=== COLUMNS ===" . PHP_EOL;
foreach ($cols as $c) {
    echo $c->Field . PHP_EOL;
}

// Search in all columns
$search = '%TRX-6A64E7AB2EEAE%';
$rows = DB::table('transactions')
    ->where('transaction_number', $search)
    ->get();

echo PHP_EOL . "=== TRANSACTIONS ===" . PHP_EOL;
if ($rows->count() > 0) {
    foreach ($rows as $r) {
        echo json_encode($r, JSON_PRETTY_PRINT) . PHP_EOL;
        echo str_repeat('-', 80) . PHP_EOL;
    }
} else {
    echo "TRANSACTION_NOT_FOUND" . PHP_EOL;
    // Try without wildcard
    echo "Trying exact match..." . PHP_EOL;
    $rows2 = DB::table('transactions')
        ->where('transaction_number', 'TRX-6A64E7AB2EEAE')
        ->get();
    if ($rows2->count() > 0) {
        foreach ($rows2 as $r) {
            echo json_encode($r, JSON_PRETTY_PRINT) . PHP_EOL;
        }
    } else {
        echo "EXACT_MATCH_NOT_FOUND" . PHP_EOL;
    }
}

if ($rows->count() > 0) {
    foreach ($rows as $r) {
        echo json_encode($r, JSON_PRETTY_PRINT) . PHP_EOL;
        echo str_repeat('-', 80) . PHP_EOL;
    }
} else {
    echo "TRANSACTION_NOT_FOUND" . PHP_EOL;
}

// Check reservation 578
echo PHP_EOL . "=== RESERVATION 578 ===" . PHP_EOL;
$res = DB::table('reservations')->where('id', 578)->first();
if ($res) {
    echo json_encode($res, JSON_PRETTY_PRINT) . PHP_EOL;
} else {
    echo "RESERVATION_578_NOT_FOUND" . PHP_EOL;
}

echo PHP_EOL . "=== HOTEL SETTINGS (cutoff) ===" . PHP_EOL;
$setting = DB::table('hotel_settings')->first();
echo 'cutoff_time: ' . ($setting->cutoff_time ?? '06:00') . PHP_EOL;

// Simulate Night Audit query for 2026-07-25
$cutoff = $setting->cutoff_time ?? '06:00';
$bizStart = '2026-07-25 ' . $cutoff . ':00';
$bizEnd = '2026-07-26 ' . $cutoff . ':00';

echo PHP_EOL . "=== BUSINESS DATE RANGE ===" . PHP_EOL;
echo "Start: $bizStart" . PHP_EOL;
echo "End: $bizEnd" . PHP_EOL;

echo PHP_EOL . "=== TRANSACTIONS IN RANGE (before status filter) ===" . PHP_EOL;
$allTx = DB::table('transactions')
    ->where('created_at', '>=', $bizStart)
    ->where('created_at', '<', $bizEnd)
    ->get();
echo "Count: " . $allTx->count() . PHP_EOL;
foreach ($allTx as $t) {
    echo "  - {$t->transaction_number} | type:{$t->type} | method:{$t->payment_method} | amount:{$t->amount} | res_id:{$t->reservation_id} | created:{$t->created_at}" . PHP_EOL;
}

// Show ALL night_audit_logs data
echo PHP_EOL . "=== ALL NIGHT AUDIT LOGS ===" . PHP_EOL;
$logs = DB::select('SELECT id, audit_date, status, total_revenue, room_revenue, created_at, locked_at FROM night_audit_logs ORDER BY audit_date DESC LIMIT 10');
echo "Count: " . count($logs) . PHP_EOL;
foreach ($logs as $l) {
    echo "  id:{$l->id} date:{$l->audit_date} status:{$l->status} rev:{$l->total_revenue} created:{$l->created_at} locked:{$l->locked_at}" . PHP_EOL;
}

// Check if there's a locking issue - maybe the night audit was locked BEFORE this transaction
echo PHP_EOL . "=== CHECK LATEST LOCKED SNAPSHOT FOR TRANSFER_BCA ===" . PHP_EOL;
$latestLocked = DB::select('SELECT id, audit_date, snapshot_data FROM night_audit_logs WHERE status = "locked" ORDER BY audit_date DESC LIMIT 1');
if (count($latestLocked) > 0) {
    $snap = json_decode($latestLocked[0]->snapshot_data, true);
    if (isset($snap['transactionsByMethod']['transfer_bca'])) {
        echo "transfer_bca transactions in snapshot:" . PHP_EOL;
        foreach ($snap['transactionsByMethod']['transfer_bca'] as $txn) {
            echo "  - {$txn['transaction_number']} | {$txn['amount']}" . PHP_EOL;
        }
    } else {
        echo "No transfer_bca in snapshot" . PHP_EOL;
        echo "Available methods: " . implode(', ', array_keys($snap['transactionsByMethod'] ?? [])) . PHP_EOL;
    }
} else {
    echo "No locked audit logs found" . PHP_EOL;
}
echo "Count: " . count($logs) . PHP_EOL;
foreach ($logs as $l) {
    echo "  id:{$l->id} date:{$l->audit_date} status:{$l->status} total_rev:{$l->total_revenue} locked_at:{$l->locked_at}" . PHP_EOL;
}

echo PHP_EOL . "=== NIGHT AUDIT LOGS ===" . PHP_EOL;
if ($auditLogs->count() > 0) {
    foreach ($auditLogs as $l) {
        echo json_encode($l, JSON_PRETTY_PRINT) . PHP_EOL;
        echo str_repeat('-', 80) . PHP_EOL;
    }
} else {
    echo "AUDIT_LOG_NOT_FOUND" . PHP_EOL;
}
