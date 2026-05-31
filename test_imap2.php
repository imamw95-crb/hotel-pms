<?php

use Webklex\PHPIMAP\ClientManager;

require_once __DIR__.'/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$host = $_ENV['IMAP_HOST'] ?? 'imap.hostinger.com';
$port = (int) ($_ENV['IMAP_PORT'] ?? 993);
$user = $_ENV['IMAP_USERNAME'] ?? '';
$pass = $_ENV['IMAP_PASSWORD'] ?? '';

echo "Host: $host\n";
echo "Port: $port\n";
echo "User: $user\n";
echo 'Pass: '.(empty($pass) ? 'EMPTY' : 'SET')."\n\n";

// Test SSL connection
echo "Testing SSL socket...\n";
$ctx = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true]]);
$socket = @stream_socket_client("ssl://$host:$port", $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $ctx);
if ($socket) {
    echo "OK: SSL connected\n";
    $banner = fgets($socket);
    echo "Banner: $banner\n";
    fclose($socket);
} else {
    echo "FAIL: $errstr ($errno)\n";
}

// Test without cert validation
echo "\nTesting SSL (no cert validation)...\n";
$ctx2 = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
$socket2 = @stream_socket_client("ssl://$host:$port", $errno2, $errstr2, 15, STREAM_CLIENT_CONNECT, $ctx2);
if ($socket2) {
    echo "OK: SSL connected (no cert check)\n";
    $banner2 = fgets($socket2);
    echo "Banner: $banner2\n";
    fclose($socket2);
} else {
    echo "FAIL: $errstr2 ($errno2)\n";
}

// Test webklex IMAP with correct config format
echo "\nTesting webklex IMAP (correct config)...\n";
try {
    $config = [
        'default' => 'default',
        'accounts' => [
            'default' => [
                'host' => $host,
                'port' => $port,
                'encryption' => 'ssl',
                'validate_cert' => false,
                'username' => $user,
                'password' => $pass,
                'protocol' => 'imap',
                'timeout' => 30,
            ],
        ],
    ];

    $cm = new ClientManager($config);
    $client = $cm->account('default');
    $client->connect();
    echo "OK: IMAP connected!\n";
    $folder = $client->getFolder('INBOX');
    $unseen = $folder->query()->unseen()->count();
    $total = $folder->query()->all()->count();
    echo "Unread: $unseen / Total: $total\n";
    if ($unseen > 0) {
        $msg = $folder->query()->unseen()->limit(1)->get()->first();
        echo 'Latest: UID='.$msg->getUid().' From='.($msg->getFrom()[0]->mail ?? '?').' Subject='.($msg->getSubject() ?? '?')."\n";
    }
    $client->disconnect();
} catch (Throwable $e) {
    echo 'FAIL: '.$e->getMessage()."\n";
}
