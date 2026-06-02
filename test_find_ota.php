<?php

use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Config;

require_once __DIR__.'/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$host = $_ENV['IMAP_HOST'] ?? 'imap.hostinger.com';
$port = (int) ($_ENV['IMAP_PORT'] ?? 993);
$user = $_ENV['IMAP_USERNAME'] ?? '';
$pass = $_ENV['IMAP_PASSWORD'] ?? '';

$config = Config::make([
    'default' => 'default',
    'accounts' => [
        'default' => [
            'host' => $host,
            'port' => $port,
            'protocol' => 'imap',
            'encryption' => 'ssl',
            'validate_cert' => false,
            'username' => $user,
            'password' => $pass,
            'timeout' => 30,
        ],
    ],
]);

$cm = new ClientManager($config);
$client = $cm->account('default');
$client->connect();

$folder = $client->getFolder('INBOX');

// Search for emails from OTA domains
echo "=== Searching for OTA emails ===\n\n";

$otaSenders = ['tiket.com', 'traveloka.com', 'booking.com', 'agoda.com', 'expedia.com'];
$found = [];

foreach ($otaSenders as $domain) {
    $messages = $folder->query()->from("@{$domain}")->limit(5)->get();
    if ($messages->count() > 0) {
        echo "Found {$messages->count()} email(s) from @{$domain}:\n";
        foreach ($messages as $msg) {
            echo '  UID: '.$msg->getUid().' | From: '.($msg->getFrom()[0]->mail ?? '?').' | Subject: '.($msg->getSubject() ?? '?')."\n";
            $found[] = ['uid' => $msg->getUid(), 'from' => $msg->getFrom()[0]->mail ?? '', 'subject' => $msg->getSubject() ?? ''];
        }
        echo "\n";
    }
}

if (empty($found)) {
    echo "No OTA emails found. Showing last 10 emails:\n\n";
    $recent = $folder->query()->all()->limit(10)->get();
    foreach ($recent as $msg) {
        echo '  UID: '.$msg->getUid().' | From: '.($msg->getFrom()[0]->mail ?? '?').' | Subject: '.($msg->getSubject() ?? '?')."\n";
    }
}

$client->disconnect();
