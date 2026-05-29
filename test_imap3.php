<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$host = $_ENV['IMAP_HOST'] ?? 'imap.hostinger.com';
$port = (int) ($_ENV['IMAP_PORT'] ?? 993);
$user = $_ENV['IMAP_USERNAME'] ?? '';
$pass = $_ENV['IMAP_PASSWORD'] ?? '';

echo "=== Test: Direct Config creation ===\n\n";

// Method 1: Direct Config object (bypass Config::make merge)
echo "--- Method 1: Direct Config ---\n";
try {
    $accountConfig = [
        'host'          => $host,
        'port'          => $port,
        'protocol'      => 'imap',
        'encryption'    => 'ssl',
        'validate_cert' => false,
        'username'      => $user,
        'password'      => $pass,
        'authentication' => null,
        'rfc'           => 'RFC822',
        'proxy' => [
            'socket' => null,
            'request_fulluri' => false,
            'username' => null,
            'password' => null,
        ],
        'timeout' => 30,
        'extensions' => [],
        'ssl_options' => [],
    ];

    $config = new \Webklex\PHPIMAP\Config([
        'default' => 'default',
        'accounts' => [
            'default' => $accountConfig,
        ],
    ]);

    $cm = new \Webklex\PHPIMAP\ClientManager($config);
    $client = $cm->account('default');
    $client->connect();
    echo "OK: Connected!\n";
    $folder = $client->getFolder('INBOX');
    $unseen = $folder->query()->unseen()->count();
    echo "Unread: $unseen\n";
    if ($unseen > 0) {
        $msg = $folder->query()->unseen()->limit(1)->get()->first();
        echo "Latest: UID=" . $msg->getUid() . " From=" . ($msg->getFrom()[0]->mail ?? '?') . " Subject=" . ($msg->getSubject() ?? '?') . "\n";
    }
    $client->disconnect();
} catch (\Throwable $e) {
    echo "FAIL: " . $e->getMessage() . "\n";
}

// Method 2: Config::make with full account config
echo "\n--- Method 2: Config::make ---\n";
try {
    $config2 = \Webklex\PHPIMAP\Config::make([
        'default' => 'default',
        'accounts' => [
            'default' => [
                'host'          => $host,
                'port'          => $port,
                'protocol'      => 'imap',
                'encryption'    => 'ssl',
                'validate_cert' => false,
                'username'      => $user,
                'password'      => $pass,
                'authentication' => null,
                'timeout'       => 30,
            ],
        ],
    ]);

    echo "Config default: " . $config2->get('default') . "\n";
    echo "Config accounts.default: " . print_r($config2->get('accounts.default'), true) . "\n";

    $cm2 = new \Webklex\PHPIMAP\ClientManager($config2);
    $client2 = $cm2->account('default');
    $client2->connect();
    echo "OK: Connected!\n";
    $client2->disconnect();
} catch (\Throwable $e) {
    echo "FAIL: " . $e->getMessage() . "\n";
}
