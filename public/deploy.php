<?php
/**
 * GitHub Auto-Deploy Webhook
 * 
 * Setup:
 * 1. Upload this file to public/deploy.php
 * 2. Go to GitHub repo -> Settings -> Webhooks -> Add webhook
 * 3. Payload URL: https://icon.cloudnod.my.id/deploy.php
 * 4. Content type: application/json
 * 5. Secret: (isi dengan secret yang kamu buat)
 * 6. Events: Just push the push event
 */

// ===== KONFIGURASI =====
$secret = getenv('DEPLOY_SECRET') ?: 'hotel-pms-deploy-2026';
$projectDir = '/www/wwwroot/icon.cloudnod.my.id';
$logFile = '/www/wwwroot/icon.cloudnod.my.id/storage/logs/deploy.log';
// =======================

// Headers for response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

function writeLog($msg) {
    global $logFile;
    $time = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$time] $msg\n", FILE_APPEND);
}

writeLog("=== DEPLOY STARTED ===");
writeLog("Method: " . $_SERVER['REQUEST_METHOD']);
writeLog("IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));

// Hanya terima POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    writeLog("ERROR: Invalid method " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    die(json_encode(['error' => 'Method not allowed']));
}

// Verifikasi signature
$payload = file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
if ($sigHeader) {
    list($algo, $sig) = explode('=', $sigHeader, 2);
    $expected = hash_hmac('sha256', $payload, $secret);
    if (!hash_equals($expected, $sig)) {
        writeLog("ERROR: Invalid signature");
        http_response_code(403);
        die(json_encode(['error' => 'Invalid signature']));
    }
} else {
    // Fallback: cek token via query string
    $token = $_GET['token'] ?? '';
    if ($token !== $secret) {
        writeLog("ERROR: Invalid token");
        http_response_code(403);
        die(json_encode(['error' => 'Invalid token']));
    }
}

// Parse event
$event = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? 'push';
$data = json_decode($payload, true);

if ($event === 'ping') {
    writeLog("PING received - webhook OK");
    echo json_encode(['success' => true, 'message' => 'pong']);
    exit;
}

writeLog("Event: $event | Branch: " . ($data['ref'] ?? 'unknown'));

// Hanya deploy untuk push ke main
if ($event !== 'push') {
    writeLog("SKIP: Not a push event");
    echo json_encode(['success' => true, 'message' => 'Not a push event, ignored']);
    exit;
}

// Cek branch
$ref = $data['ref'] ?? '';
if ($ref !== 'refs/heads/main') {
    writeLog("SKIP: Push to $ref, not main");
    echo json_encode(['success' => true, 'message' => "Push to $ref ignored"]);
    exit;
}

// === EKSEKUSI DEPLOY ===
$output = [];
$exitCode = 0;

writeLog("Starting git pull...");

// Git pull
$cmd = "cd $projectDir && git pull origin main 2>&1";
$outputStr = shell_exec($cmd);
// shell_exec already returns string
writeLog("Git pull: $outputStr");

if ($exitCode !== 0) {
    writeLog("ERROR: Git pull failed");
    echo json_encode(['success' => false, 'error' => 'Git pull failed', 'output' => $outputStr]);
    exit;
}

// Post-deploy commands
$commands = [
    "cd $projectDir && php artisan migrate --force 2>&1",
    "cd $projectDir && php artisan view:clear 2>&1",
    "cd $projectDir && php artisan config:clear 2>&1",
    "cd $projectDir && php artisan route:clear 2>&1",
    "cd $projectDir && php artisan cache:clear 2>&1",
    "cd $projectDir && php artisan optimize:clear 2>&1",
];

foreach ($commands as $cmd) {
    $cmdStr = shell_exec($cmd);
    // shell_exec already returns string
    writeLog("Artisan: $cmdStr");
}

writeLog("=== DEPLOY COMPLETED ===");

echo json_encode([
    'success' => true,
    'message' => 'Deploy completed successfully',
    'output' => $outputStr,
]);
