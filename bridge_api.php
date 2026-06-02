<?php
/**
 * PMS BRIDGE - HTTP API Version
 * Taruh di web server (Apache/Nginx) di SERVER BARU
 * Endpoint: http://192.168.88.3/bridge_api.php
 */

header('Content-Type: application/json');

define('MHS_SERVER_IP', '192.168.88.2');
define('MHS_INTERFACE_PORT', 10003);
define('STX', "\x02");
define('ETX', "\x03");
define('RS', '|');

function send_to_mhs($command) {
    $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if (!$socket) return [false, 'Socket creation failed'];
    
    @socket_connect($socket, MHS_SERVER_IP, MHS_INTERFACE_PORT);
    @socket_write($socket, $command, strlen($command));
    
    $response = '';
    while ($buf = @socket_read($socket, 1024)) {
        $response .= $buf;
        if (substr($response, -1) === ETX) break;
    }
    
    @socket_close($socket);
    return [true, $response];
}

function build_command($cmd_code, $fields = []) {
    $header = "01" . "03" . $cmd_code;
    $parts = [$header];
    
    foreach ($fields as $id => $val) {
        // PERBAIKAN: Format room number ke 4 digit (contoh: 101 -> 0101)
        if ($id == 'R') {
            $val = str_pad($val, 4, '0', STR_PAD_LEFT);
        }
        $parts[] = $id . $val;
    }
    
    return STX . implode(RS, $parts) . ETX;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'checkin':
        $room = $_GET['room'] ?? '';
        $name = $_GET['name'] ?? '';
        $checkin = $_GET['checkin'] ?? '';
        $checkout = $_GET['checkout'] ?? '';
        
        $cmd = build_command('G', ['R' => $room, 'N' => $name, 'D' => $checkin, 'O' => $checkout]);
        list($ok, $resp) = send_to_mhs($cmd);
        
        // Parse response untuk mengetahui hasilnya
        $responseCode = '';
        $responseMsg = '';
        if ($ok && strlen($resp) >= 6) {
            // Response format: <STX>ddssff<ETX>
            $content = substr($resp, 1, -1);
            $responseCode = strlen($content) >= 5 ? substr($content, 4, 1) : '?';
            $responseMsg = getResponseMessage($responseCode);
        }
        
        echo json_encode([
            'success' => $ok && $responseCode == '0',
            'command_sent' => bin2hex($cmd),
            'command_text' => $cmd,
            'room_formatted' => str_pad($room, 4, '0', STR_PAD_LEFT),
            'response_code' => $responseCode,
            'response_message' => $responseMsg,
            'raw_response' => bin2hex($resp)
        ]);
        break;
        
    case 'checkout':
        $room = $_GET['room'] ?? '';
        $cmd = build_command('B', ['R' => $room]);
        list($ok, $resp) = send_to_mhs($cmd);
        
        $responseCode = '';
        if ($ok && strlen($resp) >= 6) {
            $content = substr($resp, 1, -1);
            $responseCode = strlen($content) >= 5 ? substr($content, 4, 1) : '?';
        }
        
        echo json_encode([
            'success' => $ok && $responseCode == '0',
            'room_formatted' => str_pad($room, 4, '0', STR_PAD_LEFT),
            'response_code' => $responseCode,
            'raw_response' => bin2hex($resp)
        ]);
        break;
        
    case 'read':
        $cmd = build_command('E', []);
        list($ok, $resp) = send_to_mhs($cmd);
        
        // Parse response untuk membaca data kartu
        $cardData = [];
        if ($ok && $resp) {
            $content = substr($resp, 1, -1);
            $parts = explode('|', $content);
            foreach ($parts as $part) {
                if (strlen($part) > 1) {
                    $fieldId = substr($part, 0, 1);
                    $fieldValue = substr($part, 1);
                    if ($fieldId == 'R') $cardData['room'] = $fieldValue;
                    if ($fieldId == 'N') $cardData['name'] = $fieldValue;
                    if ($fieldId == 'D') $cardData['checkin'] = $fieldValue;
                    if ($fieldId == 'O') $cardData['checkout'] = $fieldValue;
                }
            }
        }
        
        echo json_encode([
            'success' => $ok,
            'card_data' => $cardData,
            'raw_response' => bin2hex($resp)
        ]);
        break;
        
    case 'test':
        // Endpoint untuk test koneksi ke MHS
        $testCmd = STX . "0103E" . ETX;
        list($ok, $resp) = send_to_mhs($testCmd);
        echo json_encode([
            'test' => 'connection_to_mhs',
            'mhs_server' => MHS_SERVER_IP . ':' . MHS_INTERFACE_PORT,
            'connected' => $ok,
            'response' => bin2hex($resp)
        ]);
        break;
        
    default:
        echo json_encode([
            'error' => 'Unknown action',
            'available' => ['checkin', 'checkout', 'read', 'test'],
            'example' => [
                'checkin' => '?action=checkin&room=101&name=John&checkin=202405221400&checkout=202405251200',
                'checkout' => '?action=checkout&room=101',
                'read' => '?action=read',
                'test' => '?action=test'
            ]
        ]);
}

/**
 * Get response message based on response code from MHS
 */
function getResponseMessage($code) {
    $messages = [
        '0' => 'Success',
        '1' => 'Unconfirmed error',
        '2' => 'Wrong (invalid) destination address',
        '3' => 'Invalid command code',
        '4' => 'Room is occupied',
        '5' => 'Wrong COMM or Encoder is busy',
        '6' => 'Invalid room number',
        '7' => 'Key code already exits',
        '8' => 'Encoder waiting overtime',
        '10' => 'Invalid time',
        '11' => 'Client end not connected',
        '12' => 'Wrong communication from the client end'
    ];
    return $messages[$code] ?? 'Unknown response code: ' . $code;
}
?>