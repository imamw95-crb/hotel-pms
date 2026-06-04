<?php
/**
 * GitHub Auto-Deploy Webhook
 *
 * Menerima push event dari GitHub, lalu menjalankan:
 *   git pull → composer install → migrate → optimize → queue restart
 *
 * Setup:
 * 1. Pastikan file ini ada di public/deploy.php
 * 2. Set DEPLOY_SECRET di .env (generate: php -r "echo bin2hex(random_bytes(32));")
 * 3. GitHub repo → Settings → Webhooks → Add webhook
 *    - Payload URL : https://icon.cloudnod.my.id/deploy.php
 *    - Content type: application/json
 *    - Secret      : (isi dengan DEPLOY_SECRET dari .env)
 *    - Events      : Just the push event
 * 4. Pastikan storage/logs/ writable oleh web server
 */

// ===== KONFIGURASI =====
$projectDir  = '/www/wwwroot/icon.cloudnod.my.id';
$logFile     = $projectDir . '/storage/logs/deploy.log';
$branch      = 'refs/heads/main';

// Read DEPLOY_SECRET from environment first, then fallback to .env if necessary.
$secret = getenv('DEPLOY_SECRET') ?: null;
$envFile = $projectDir . '/.env';
if (!$secret && is_readable($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        if (str_starts_with($line, 'DEPLOY_SECRET=')) {
            $value = substr($line, strlen('DEPLOY_SECRET='));
            // Strip surrounding quotes
            $value = trim($value, '"\'');
            $secret = $value !== '' ? $value : null;
            break;
        }
    }
}
// =======================

header('Content-Type: application/json');

function writeLog(string $msg): void
{
    global $logFile;
    $time = date('Y-m-d H:i:s');
    @file_put_contents($logFile, "[$time] {$msg}\n", FILE_APPEND);
}

function runCmd(string $cmd): array
{
    $output    = [];
    $exitCode  = 0;
    exec($cmd, $output, $exitCode);
    return ['output' => implode("\n", $output), 'exitCode' => $exitCode];
}

// --- Require DEPLOY_SECRET ---
if (!$secret) {
    writeLog('ERROR: DEPLOY_SECRET not configured');
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'DEPLOY_SECRET not configured']);
    exit;
}

writeLog('=== DEPLOY TRIGGERED ===');
writeLog('IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));

// --- Only accept POST ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    writeLog('ERROR: Invalid method ' . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// --- Read raw payload ---
$rawBody     = file_get_contents('php://input');
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

// --- Verify HMAC signature (MUST use raw body before any parsing) ---
$sigHeader = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

if ($sigHeader) {
    [$algo, $sig] = explode('=', $sigHeader, 2);
    $expected = hash_hmac('sha256', $rawBody, $secret);
    if (!hash_equals($expected, $sig)) {
        writeLog('ERROR: Invalid HMAC signature');
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Invalid signature']);
        exit;
    }
} else {
    writeLog('ERROR: No signature header');
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Missing signature']);
    exit;
}

// --- Parse payload (after signature verification) ---
if (str_contains($contentType, 'application/x-www-form-urlencoded')) {
    parse_str($rawBody, $formData);
    $payload = $formData['payload'] ?? $rawBody;
} else {
    $payload = $rawBody;
}

// --- Parse event ---
$event = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? 'push';
$data  = json_decode($payload, true);

if ($event === 'ping') {
    writeLog('PING received — webhook OK');
    echo json_encode(['success' => true, 'message' => 'pong']);
    exit;
}

writeLog("Event: {$event} | Ref: " . ($data['ref'] ?? 'unknown'));

if ($event !== 'push') {
    writeLog('SKIP: Not a push event');
    echo json_encode(['success' => true, 'message' => 'Not a push event, ignored']);
    exit;
}

// --- Only deploy main branch ---
$ref = $data['ref'] ?? '';
if ($ref !== $branch) {
    writeLog("SKIP: Push to {$ref}, not {$branch}");
    echo json_encode(['success' => true, 'message' => "Push to {$ref} ignored"]);
    exit;
}

// === EKSEKUSI DEPLOY ===
$results = [];

// 1. Git pull
writeLog('Step 1/7: git pull origin main');
$result = runCmd("cd {$projectDir} && git pull origin main 2>&1");
$results['git_pull'] = $result;
writeLog('Git pull exit=' . $result['exitCode'] . ' | ' . substr($result['output'], 0, 500));

if ($result['exitCode'] !== 0) {
    writeLog('ERROR: Git pull failed');
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Git pull failed', 'results' => $results]);
    exit;
}

// 2. Composer install (only if composer.json or lock changed)
$composerChanged = str_contains($result['output'], 'composer.json')
                || str_contains($result['output'], 'composer.lock');
if ($composerChanged) {
    writeLog('Step 2/7: composer install (dependencies changed)');
    $result = runCmd("cd {$projectDir} && composer install --no-dev --optimize-autoloader --no-interaction 2>&1");
    $results['composer_install'] = $result;
    writeLog('Composer exit=' . $result['exitCode'] . ' | ' . substr($result['output'], 0, 500));
} else {
    writeLog('Step 2/7: composer install (skipped — no dependency changes)');
    $results['composer_install'] = ['output' => 'skipped', 'exitCode' => 0];
}

// 3. Database migrate
writeLog('Step 3/7: artisan migrate --force');
$result = runCmd("cd {$projectDir} && php artisan migrate --force 2>&1");
$results['migrate'] = $result;
writeLog('Migrate exit=' . $result['exitCode'] . ' | ' . substr($result['output'], 0, 500));

if ($result['exitCode'] !== 0) {
    writeLog('ERROR: Migration failed — deploy halted');
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Migration failed', 'results' => $results]);
    exit;
}

// 4. Config cache
writeLog('Step 4/7: artisan config:cache');
$result = runCmd("cd {$projectDir} && php artisan config:cache 2>&1");
$results['config_cache'] = $result;
writeLog('Config cache exit=' . $result['exitCode']);

// 5. Route cache
writeLog('Step 5/7: artisan route:cache');
$result = runCmd("cd {$projectDir} && php artisan route:cache 2>&1");
$results['route_cache'] = $result;
writeLog('Route cache exit=' . $result['exitCode']);

// 6. View cache
writeLog('Step 6/7: artisan view:cache');
$result = runCmd("cd {$projectDir} && php artisan view:cache 2>&1");
$results['view_cache'] = $result;
writeLog('View cache exit=' . $result['exitCode']);

// 7. Queue restart
writeLog('Step 7/7: artisan queue:restart');
$result = runCmd("cd {$projectDir} && php artisan queue:restart 2>&1");
$results['queue_restart'] = $result;
writeLog('Queue restart exit=' . $result['exitCode']);

writeLog('=== DEPLOY COMPLETED SUCCESSFULLY ===');

echo json_encode([
    'success' => true,
    'message'  => 'Deploy completed',
    'branch'   => $ref,
    'commit'   => $data['after'] ?? null,
    'results'  => $results,
]);
