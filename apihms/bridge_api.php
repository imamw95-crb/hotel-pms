<?php

/**
 * PMS BRIDGE - HTTP API Version (Fixed for Client Errors)
 * Endpoint: http://YOUR_SERVER_IP/bridge_api.php
 */
header('Content-Type: application/json');

// Konfigurasi
define('MHS_SERVER_IP', '192.168.88.2');  // IP Server MHS
define('MHS_INTERFACE_PORT', 10003);
define('STX', "\x02");
define('ETX', "\x03");
define('RS', '|');
define('SOCKET_TIMEOUT', 5);
define('MAX_RETRIES', 3);

// Daftar kamar valid (termasuk mapping PMSNo)
$VALID_ROOMS = [
    // Floor 1
    '0101', '0102', '0103', '0105',
    // Floor 4
    '0106', '0107', '0108', '0109', '0110', '0111', '0112',
    // Mapping PMSNo (encoder code)
    '0401', '0402', '0403',
    // Floor 2
    '0201', '0202', '0203', '0205', '0206', '0207', '0208', '0209', '0210', '0211',
    // Floor 3
    '0301', '0302', '0303', '0305', '0306', '0307', '0308', '0309', '0310', '0311',
];

/**
 * Logger untuk debugging
 */
function logMessage($type, $message, $data = null)
{
    $logFile = __DIR__.'/pms_bridge.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$type] $message";
    if ($data !== null) {
        $logEntry .= ' | Data: '.(is_string($data) ? $data : json_encode($data));
    }
    file_put_contents($logFile, $logEntry."\n", FILE_APPEND);

    // Batasi ukuran log (max 10MB)
    if (filesize($logFile) > 10 * 1024 * 1024) {
        $oldLog = file_get_contents($logFile);
        file_put_contents($logFile, substr($oldLog, -5 * 1024 * 1024));
    }
}

/**
 * Send command ke MHS dengan retry mechanism
 */
function sendToMHS($command, $retryCount = 0)
{
    logMessage('DEBUG', 'Sending command (attempt '.($retryCount + 1).')', bin2hex($command));

    $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if (! $socket) {
        $error = socket_strerror(socket_last_error());
        logMessage('ERROR', "Socket creation failed: $error");

        return ['success' => false, 'error' => $error];
    }

    // Set socket options
    socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => SOCKET_TIMEOUT, 'usec' => 0]);
    socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => SOCKET_TIMEOUT, 'usec' => 0]);
    socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);

    // Connect ke server
    if (! @socket_connect($socket, MHS_SERVER_IP, MHS_INTERFACE_PORT)) {
        $error = socket_strerror(socket_last_error($socket));
        socket_close($socket);
        logMessage('ERROR', "Connection failed: $error");

        // Retry jika masih dalam batas
        if ($retryCount < MAX_RETRIES) {
            logMessage('DEBUG', 'Retrying connection...');
            usleep(100000); // wait 100ms

            return sendToMHS($command, $retryCount + 1);
        }

        return ['success' => false, 'error' => 'Connection failed after '.MAX_RETRIES." attempts: $error"];
    }

    logMessage('DEBUG', 'Connected to '.MHS_SERVER_IP.':'.MHS_INTERFACE_PORT);

    // Kirim command
    $bytesWritten = @socket_write($socket, $command, strlen($command));
    if ($bytesWritten === false) {
        $error = socket_strerror(socket_last_error($socket));
        socket_close($socket);
        logMessage('ERROR', "Write failed: $error");

        return ['success' => false, 'error' => "Write failed: $error"];
    }

    logMessage('DEBUG', "Sent $bytesWritten bytes");

    // Baca response
    $response = '';
    $startTime = time();

    while (time() - $startTime < SOCKET_TIMEOUT) {
        $buf = @socket_read($socket, 1024);
        if ($buf === false) {
            $error = socket_strerror(socket_last_error($socket));
            if (strpos($error, 'Resource temporarily unavailable') === false) {
                logMessage('WARNING', "Read error: $error");
                break;
            }

            continue;
        }
        if ($buf === '') {
            usleep(10000);

            continue;
        }

        $response .= $buf;
        logMessage('DEBUG', 'Received chunk: '.bin2hex($buf));

        // Response selesai jika diakhiri ETX
        if (strpos($response, ETX) !== false) {
            break;
        }
    }

    socket_close($socket);

    if (empty($response)) {
        logMessage('WARNING', 'Empty response received');

        return ['success' => false, 'error' => 'No response from server'];
    }

    logMessage('DEBUG', 'Complete response: '.bin2hex($response));

    return ['success' => true, 'response' => $response];
}

/**
 * Build command sesuai format MHS yang benar
 */
function buildCommand($cmdCode, $fields = [])
{
    // Khusus command B (checkout): pakai destination 00 (database only, no card cancel)
    if ($cmdCode === 'B') {
        $header = '00'.'00'.$cmdCode;
    } else {
        $header = '01'.'03'.$cmdCode;
    }
    $parts = [$header];

    foreach ($fields as $fieldId => $value) {
        if ($fieldId === 'R') {
            $value = str_pad((string) $value, 4, '0', STR_PAD_LEFT);
        }
        if (in_array($fieldId, ['R', 'N', 'D', 'O', 'I', 'P'])) {
            $parts[] = $fieldId.$value;
        }
    }

    $command = STX.implode(RS, $parts).ETX;
    logMessage('DEBUG', 'Built command: '.bin2hex($command));

    return $command;
}

/**
 * Parse response dari MHS
 */
function parseResponse($response)
{
    if (empty($response)) {
        return ['status_code' => '99', 'fields' => [], 'error' => 'Empty response'];
    }

    // Validasi STX dan ETX
    if ($response[0] !== STX) {
        logMessage('WARNING', 'Response missing STX', bin2hex($response));

        return ['status_code' => '98', 'fields' => [], 'error' => 'Invalid response format (missing STX)'];
    }

    if (substr($response, -1) !== ETX) {
        logMessage('WARNING', 'Response missing ETX', bin2hex($response));

        return ['status_code' => '98', 'fields' => [], 'error' => 'Invalid response format (missing ETX)'];
    }

    $content = substr($response, 1, -1);

    // Cek jika ada field tambahan
    if (strpos($content, RS) !== false) {
        $parts = explode(RS, $content);
        $header = array_shift($parts);
        $statusCode = strlen($header) >= 5 ? substr($header, 4, 1) : '?';

        $fields = [];
        foreach ($parts as $part) {
            if (strlen($part) > 1) {
                $fieldId = substr($part, 0, 1);
                $fieldValue = substr($part, 1);
                $fields[$fieldId] = $fieldValue;
            }
        }

        return ['status_code' => $statusCode, 'fields' => $fields];
    } else {
        $statusCode = strlen($content) >= 5 ? substr($content, 4, 1) : '?';

        return ['status_code' => $statusCode, 'fields' => []];
    }
}

/**
 * Pesan status
 */
function getStatusMessage($code)
{
    $messages = [
        '0' => 'Sukses',
        '1' => 'Error tidak terkonfirmasi',
        '2' => 'Alamat tujuan invalid',
        '3' => 'Kode command invalid',
        '4' => 'Kamar sudah terisi',
        '5' => 'Comm error atau Encoder sibuk',
        '6' => 'Nomor kamar invalid',
        '7' => 'Kode kunci sudah ada',
        '8' => 'Encoder timeout',
        '9' => 'Gagal encode kartu',
        '10' => 'Waktu invalid',
        '11' => 'Client tidak terhubung',
        '12' => 'Komunikasi client error',
        '98' => 'Format response invalid',
        '99' => 'Response kosong',
    ];

    return $messages[$code] ?? "Status tidak dikenal: $code";
}

/**
 * Validasi kamar
 */
function isValidRoom($room, $validRooms)
{
    $roomFormatted = str_pad((string) $room, 4, '0', STR_PAD_LEFT);
    $isValid = in_array($roomFormatted, $validRooms);
    logMessage('DEBUG', "Room validation: $roomFormatted -> ".($isValid ? 'valid' : 'invalid'));

    return $isValid;
}

// ==================== MAIN HANDLER ====================

// Enable error reporting untuk debugging (matikan di production)
// error_reporting(E_ALL);
// ini_set('display_errors', 0);

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// Handle JSON input
if ($method === 'POST' && empty($_POST)) {
    $input = json_decode(file_get_contents('php://input'), true);
    if ($input) {
        $_POST = $input;
        $action = $_POST['action'] ?? $action;
    }
}

logMessage('INFO', "Request received: action=$action, method=$method");

switch ($action) {
    case 'checkin':
        $room = trim($_GET['room'] ?? $_POST['room'] ?? '');
        $name = trim($_GET['name'] ?? $_POST['name'] ?? '');
        $checkin = trim($_GET['checkin'] ?? $_POST['checkin'] ?? '');
        $checkout = trim($_GET['checkout'] ?? $_POST['checkout'] ?? '');

        // Validasi
        $errors = [];
        if (empty($room)) {
            $errors[] = 'Room number required';
        }
        if (empty($name)) {
            $errors[] = 'Guest name required';
        }
        if (empty($checkin) || ! preg_match('/^\d{12}$/', $checkin)) {
            $errors[] = 'Valid checkin date required (YYYYMMDDHHMM)';
        }
        if (empty($checkout) || ! preg_match('/^\d{12}$/', $checkout)) {
            $errors[] = 'Valid checkout date required (YYYYMMDDHHMM)';
        }

        if (! empty($errors)) {
            echo json_encode(['success' => false, 'errors' => $errors]);
            break;
        }

        $formattedRoom = str_pad($room, 4, '0', STR_PAD_LEFT);

        if (! isValidRoom($room, $VALID_ROOMS)) {
            echo json_encode([
                'success' => false,
                'error' => 'Invalid room number',
                'room_sent' => $formattedRoom,
                'valid_rooms' => $VALID_ROOMS,
            ]);
            break;
        }

        $cmd = buildCommand('G', [
            'R' => $formattedRoom,
            'N' => $name,
            'D' => $checkin,
            'O' => $checkout,
        ]);

        $result = sendToMHS($cmd);

        if (! $result['success']) {
            echo json_encode(['success' => false, 'error' => $result['error']]);
            break;
        }

        $parsed = parseResponse($result['response']);
        $isSuccess = ($parsed['status_code'] === '0');

        echo json_encode([
            'success' => $isSuccess,
            'action' => 'checkin',
            'data' => [
                'room' => $formattedRoom,
                'guest_name' => $name,
                'checkin_date' => $checkin,
                'checkout_date' => $checkout,
            ],
            'response' => [
                'code' => $parsed['status_code'],
                'message' => getStatusMessage($parsed['status_code']),
            ],
        ]);
        break;

    case 'new_checkin':
        $room = trim($_GET['room'] ?? $_POST['room'] ?? '');
        $name = trim($_GET['name'] ?? $_POST['name'] ?? '');
        $checkin = trim($_GET['checkin'] ?? $_POST['checkin'] ?? '');
        $checkout = trim($_GET['checkout'] ?? $_POST['checkout'] ?? '');

        $errors = [];
        if (empty($room)) {
            $errors[] = 'Room number required';
        }
        if (empty($name)) {
            $errors[] = 'Guest name required';
        }
        if (empty($checkin) || ! preg_match('/^\d{12}$/', $checkin)) {
            $errors[] = 'Valid checkin date required (YYYYMMDDHHMM)';
        }
        if (empty($checkout) || ! preg_match('/^\d{12}$/', $checkout)) {
            $errors[] = 'Valid checkout date required (YYYYMMDDHHMM)';
        }

        if (! empty($errors)) {
            echo json_encode(['success' => false, 'errors' => $errors]);
            break;
        }

        $formattedRoom = str_pad($room, 4, '0', STR_PAD_LEFT);

        if (! isValidRoom($room, $VALID_ROOMS)) {
            echo json_encode([
                'success' => false,
                'error' => 'Invalid room number',
                'room_sent' => $formattedRoom,
                'valid_rooms' => $VALID_ROOMS,
            ]);
            break;
        }

        $cmd = buildCommand('I', [
            'R' => $formattedRoom,
            'N' => $name,
            'D' => $checkin,
            'O' => $checkout,
        ]);

        $result = sendToMHS($cmd);

        if (! $result['success']) {
            echo json_encode(['success' => false, 'error' => $result['error']]);
            break;
        }

        $parsed = parseResponse($result['response']);
        $isSuccess = ($parsed['status_code'] === '0');

        echo json_encode([
            'success' => $isSuccess,
            'action' => 'new_checkin',
            'data' => [
                'room' => $formattedRoom,
                'guest_name' => $name,
                'checkin_date' => $checkin,
                'checkout_date' => $checkout,
            ],
            'response' => [
                'code' => $parsed['status_code'],
                'message' => getStatusMessage($parsed['status_code']),
            ],
        ]);
        break;

    case 'checkout':
        $room = trim($_GET['room'] ?? $_POST['room'] ?? '');

        if (empty($room)) {
            echo json_encode(['success' => false, 'error' => 'Room number required']);
            break;
        }

        if (! isValidRoom($room, $VALID_ROOMS)) {
            echo json_encode([
                'success' => false,
                'error' => 'Invalid room number',
                'valid_rooms' => $VALID_ROOMS,
            ]);
            break;
        }

        $formattedRoom = str_pad($room, 4, '0', STR_PAD_LEFT);
        $cmd = buildCommand('B', ['R' => $formattedRoom]);
        $result = sendToMHS($cmd);

        if (! $result['success']) {
            echo json_encode(['success' => false, 'error' => $result['error']]);
            break;
        }

        $parsed = parseResponse($result['response']);
        $isSuccess = ($parsed['status_code'] === '0');

        echo json_encode([
            'success' => $isSuccess,
            'action' => 'checkout',
            'data' => ['room' => $formattedRoom],
            'response' => [
                'code' => $parsed['status_code'],
                'message' => getStatusMessage($parsed['status_code']),
            ],
        ]);
        break;

    case 'erase_card':
        $room = trim($_GET['room'] ?? $_POST['room'] ?? '');

        if (empty($room)) {
            echo json_encode(['success' => false, 'error' => 'Room number required']);
            break;
        }

        if (! isValidRoom($room, $VALID_ROOMS)) {
            echo json_encode([
                'success' => false,
                'error' => 'Invalid room number',
                'valid_rooms' => $VALID_ROOMS,
            ]);
            break;
        }

        $formattedRoom = str_pad($room, 4, '0', STR_PAD_LEFT);
        // Erase card: pakai destination 01 (kirim ke encoder untuk cancel kartu fisik)
        $cmd = STX.'0103B'.RS.'R'.$formattedRoom.ETX;
        logMessage('DEBUG', 'Erase card command: '.bin2hex($cmd));
        $result = sendToMHS($cmd);

        if (! $result['success']) {
            echo json_encode(['success' => false, 'error' => $result['error']]);
            break;
        }

        $parsed = parseResponse($result['response']);
        $isSuccess = ($parsed['status_code'] === '0');

        echo json_encode([
            'success' => $isSuccess,
            'action' => 'erase_card',
            'data' => ['room' => $formattedRoom],
            'response' => [
                'code' => $parsed['status_code'],
                'message' => getStatusMessage($parsed['status_code']),
            ],
        ]);
        break;

    case 'read':
        $cmd = STX.'0103E'.ETX;
        $result = sendToMHS($cmd);

        if (! $result['success']) {
            echo json_encode(['success' => false, 'error' => $result['error']]);
            break;
        }

        $parsed = parseResponse($result['response']);

        echo json_encode([
            'success' => true,
            'action' => 'read',
            'card_data' => [
                'room' => $parsed['fields']['R'] ?? null,
                'name' => $parsed['fields']['N'] ?? null,
                'checkin' => $parsed['fields']['D'] ?? null,
                'checkout' => $parsed['fields']['O'] ?? null,
            ],
        ]);
        break;

    case 'rooms':
        $roomsByFloor = [];
        foreach ($VALID_ROOMS as $room) {
            $floor = substr($room, 0, 2);
            if (! isset($roomsByFloor[$floor])) {
                $roomsByFloor[$floor] = [];
            }
            $roomsByFloor[$floor][] = $room;
        }

        echo json_encode([
            'success' => true,
            'total_rooms' => count($VALID_ROOMS),
            'rooms' => $VALID_ROOMS,
            'by_floor' => $roomsByFloor,
        ]);
        break;

    case 'register_encoder':
        // Command untuk register encoder (mengatasi error client)
        $encoderIp = $_GET['ip'] ?? $_POST['ip'] ?? MHS_SERVER_IP;
        $encoderId = $_GET['encoder_id'] ?? $_POST['encoder_id'] ?? '01';

        // Command ECE: Register Encoder
        $cmdECE = STX.'ECE'.$encoderId.'013IP'.$encoderIp.ETX;
        $resultECE = sendToMHS($cmdECE);

        // Command EAG: Encoder Acknowledge
        $cmdEAG = STX.'EAG'.$encoderId.'013IP'.$encoderIp.ETX;
        $resultEAG = sendToMHS($cmdEAG);

        echo json_encode([
            'success' => $resultECE['success'] && $resultEAG['success'],
            'action' => 'register_encoder',
            'ece_response' => $resultECE['success'] ? bin2hex($resultECE['response']) : $resultECE['error'],
            'eag_response' => $resultEAG['success'] ? bin2hex($resultEAG['response']) : $resultEAG['error'],
        ]);
        break;

    case 'test':
        $testCmd = STX.'0103E'.ETX;
        $result = sendToMHS($testCmd);

        echo json_encode([
            'test' => 'connection_to_mhs',
            'mhs_server' => MHS_SERVER_IP.':'.MHS_INTERFACE_PORT,
            'connected' => $result['success'],
            'response' => $result['success'] ? bin2hex($result['response']) : null,
            'error' => $result['error'] ?? null,
            'timestamp' => date('Y-m-d H:i:s'),
        ]);
        break;

    case 'clear_log':
        // Clear log file
        $logFile = __DIR__.'/pms_bridge.log';
        if (file_exists($logFile)) {
            unlink($logFile);
        }
        echo json_encode(['success' => true, 'message' => 'Log cleared']);
        break;

    default:
        echo json_encode([
            'success' => false,
            'error' => 'Unknown action',
            'available_actions' => ['checkin', 'new_checkin', 'checkout', 'erase_card', 'read', 'rooms', 'test', 'register_encoder', 'clear_log'],
            'examples' => [
                'checkin' => '?action=checkin&room=101&name=JohnDoe&checkin=202406281400&checkout=202406301200',
                'new_checkin' => '?action=new_checkin&room=101&name=JohnDoe&checkin=202406281400&checkout=202406301200',
                'checkout' => '?action=checkout&room=101',
                'erase_card' => '?action=erase_card&room=101',
                'read' => '?action=read',
                'rooms' => '?action=rooms',
                'test' => '?action=test',
                'register_encoder' => '?action=register_encoder&ip=192.168.88.2',
            ],
        ]);
}
