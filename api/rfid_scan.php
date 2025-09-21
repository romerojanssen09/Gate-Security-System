<?php
/**
 * RFID Scanning API Endpoint
 * Handles RFID card scans and broadcasts real-time updates
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../storage/database.php';
require_once '../includes/PusherHelper.php';
require_once '../includes/Logger.php';

/**
 * Send command to Arduino via Python bridge
 */
function sendToArduino($command) {
    $url = "http://127.0.0.1:5000/send";
    $data = http_build_query(['cmd' => $command]);
    
    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => $data,
            'timeout' => 5
        ]
    ];
    
    $context = stream_context_create($options);
    
    try {
        $result = file_get_contents($url, false, $context);
        return json_decode($result, true);
    } catch (Exception $e) {
        Logger::error("Arduino communication failed", ['error' => $e->getMessage()]);
        return ['status' => 'error', 'message' => 'Arduino communication failed'];
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $rfidId = $input['rfid_id'] ?? '';
    // $rfidId = $_GET['rfid_id'];
    $gateLocation = $input['gate_location'] ?? 'main_gate';
    // $gateLocation = $_GET['gate_location'];
    
    if (empty($rfidId)) {
        throw new Exception('RFID ID is required');
    }
    
    // Get database connection
    $conn = getConnection();
    
    // Check if RFID card exists and is active
    $cardQuery = "SELECT * FROM rfid_cards WHERE rfid_id = ?";
    $cardStmt = mysqli_prepare($conn, $cardQuery);
    mysqli_stmt_bind_param($cardStmt, "s", $rfidId);
    mysqli_stmt_execute($cardStmt);
    $cardResult = mysqli_stmt_get_result($cardStmt);
    
    $accessResult = 'denied';
    $denialReason = null;
    $cardData = null;
    
    if ($cardRow = mysqli_fetch_assoc($cardResult)) {
        $cardData = $cardRow;
        
        if ($cardRow['status'] === 'active') {
            $accessResult = 'granted';
        } else {
            $accessResult = 'granted';
            $denialReason = 'Card is ' . $cardRow['status'];
        }
    } else {
        $denialReason = 'Card not found in system';
    }
    
    mysqli_stmt_close($cardStmt);
    
    // Log the access attempt
    $logQuery = "INSERT INTO access_logs (rfid_id, card_id, full_name, access_result, denial_reason, gate_location, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $logStmt = mysqli_prepare($conn, $logQuery);
    
    $cardId = $cardData ? $cardData['id'] : null;
    $fullName = $cardData ? $cardData['full_name'] : 'Unknown';
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    
    mysqli_stmt_bind_param($logStmt, "sisssss", $rfidId, $cardId, $fullName, $accessResult, $denialReason, $gateLocation, $ipAddress);
    mysqli_stmt_execute($logStmt);
    $logId = mysqli_insert_id($conn);
    mysqli_stmt_close($logStmt);
    
    // Prepare response data
    $responseData = [
        'success' => true,
        'access_result' => $accessResult,
        'rfid_id' => $rfidId,
        'full_name' => $fullName,
        'role' => $cardData['role'] ?? null,
        'plate_number' => $cardData['plate_number'] ?? null,
        'denial_reason' => $denialReason,
        'gate_location' => $gateLocation,
        'timestamp' => date('Y-m-d H:i:s'),
        'log_id' => $logId
    ];
    
    // Broadcast real-time update via Pusher
    $pusher = new PusherHelper();
    $pusher->broadcastRFIDAccess($responseData);
    
    // Log security event
    Logger::security("RFID Access: {$rfidId} - {$accessResult}", [
        'rfid_id' => $rfidId,
        'result' => $accessResult,
        'name' => $fullName,
        'gate' => $gateLocation,
        'ip' => $ipAddress
    ]);
    
    // Send command to Arduino via Python bridge
    if ($accessResult === 'granted') {
    $arduinoCommand = 'open';
    } elseif ($accessResult === 'denied') {
        $arduinoCommand = 'unauthorized';
    } else {
        $arduinoCommand = 'close';
    }

    $arduinoResponse = sendToArduino($arduinoCommand);
    
    // Add Arduino response to response data
    $responseData['arduino_command'] = $arduinoCommand;
    $responseData['arduino_response'] = $arduinoResponse;
    
    // Broadcast gate control with Arduino integration
    if ($accessResult === 'granted') {
        $gateData = [
            'gate_location' => $gateLocation,
            'status' => 'opening',
            'timestamp' => date('Y-m-d H:i:s'),
            'rfid_id' => $rfidId,
            'arduino_response' => $arduinoResponse
        ];
        $pusher->broadcastGateStatus($gateData);
        
        // Simulate gate closing after delay
        sleep(1);
        $gateData['status'] = 'closing';
        $pusher->broadcastGateStatus($gateData);
    }
    
    echo json_encode($responseData);
    
} catch (Exception $e) {
    Logger::error("RFID Scan API Error", ['error' => $e->getMessage()]);
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error',
        'error' => $e->getMessage()
    ]);
}
?>