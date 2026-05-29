<?php
require_once __DIR__ . '/vendor/autoload.php';

// Load .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$host = $_ENV['IMAP_HOST'] ?? 'imap.hostinger.com';
$port = (int) ($_ENV['IMAP_PORT'] ?? 993);
$encryption = $_ENV['IMAP_ENCRYPTION'] ?? 'ssl';
$username = $_ENV['IMAP_USERNAME'] ?? '';
$password = $_ENV['IMAP_PASSWORD'] ?? '';

echo "=== IMAP Connection Debug ===\n";
echo "Host: {$host}\n";
echo "Port: {$port}\n";
echo "Encryption: {$encryption}\n";
echo "Username: {$username}\n";
echo "Password: " . (empty($password) ? '(empty)' : '(set)') . "\n";
echo "\n";

// Test 1: Basic SSL connection
echo "--- Test 1: SSL Socket Connection ---\n";
$socket = @fsockopen("ssl://{$host}", $port, $errno, $errstr, 15);
if ($socket) {
    echo "✅ SSL socket connected\n";
    $banner = fgets($socket);
    echo "Banner: {$banner}";
    fclose($socket);
} else {
    echo "❌ SSL socket failed: {$errstr} ({$errno})\n";
}
echo "\n";

// Test 2: IMAP connection via webklex
echo "--- Test 2: IMAP Connection via webklex ---\n";
try {
    $cm = new \Webklex\PHPIMAP\ClientManager([
        'host'          => $host,
        'port'          => $port,
        'encryption'    => $encryption,
        'validate_cert' => true,
        'username'      => $username,
        'password'      => $password,
        'protocol'      => 'imap',
        'timeout'       => 30,
    ]);

    $client = $cm->account('default');
    $client->connect();
    echo "✅ IMAP connected!\n";

    $folder = $client->getFolder('INBOX');
    echo "✅ INBOX folder opened\n";

    $unseen = $folder->query()->unseen()->count();
    $total = $folder->query()->all()->count();
    echo "📬 Unread: {$unseen} / Total: {$total}\n";

    if ($unseen > 0) {
        echo "\n--- Latest Unread Email ---\n";
        $msg = $folder->query()->unseen()->limit(1)->get()->first();
        echo "UID: " . $msg->getUid() . "\n";
        echo "From: " . ($msg->getFrom()[0]->mail ?? 'unknown') . "\n";
        echo "Subject: " . ($msg->getSubject() ?? '(no subject)') . "\n";
        echo "Date: " . $msg->getDate()->toDateTimeString() . "\n";

        $body = '';
        try {
            $html = $msg->getHTMLBody();
            if ($html) $body = strip_tags($html);
            else {
                $text = $msg->getTextBody();
                if ($text) $body = $text;
            }
        } catch (\Exception $e) {
            $body = '(error extracting body)';
        }
        $body = preg_replace('/\s+/', ' ', $body);
        echo "Body (first 300 chars): " . substr($body, 0, 300) . "\n";
    }

    $client->disconnect();
    echo "\n✅ Disconnected cleanly\n";

} catch (\Throwable $e) {
    echo "❌ IMAP Error: " . $e->getMessage() . "\n";
    echo "Class: " . get_class($e) . "\n";
    if ($e->getPrevious()) {
        echo "Previous: " . $e->getPrevious()->getMessage() . "\n";
    }
}

echo "\n=== Done ===\n";
