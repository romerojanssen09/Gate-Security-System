<?php
/**
 * RFID Scanning API Endpoint
 * Handles RFID card scans and broadcasts real-time updates
 */

// Enable error reporting for debugging (will be suppressed later)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Clean any output buffer
if (ob_get_level()) {
    ob_clean();
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Test if files exist before requiring them
$requiredFiles = [
    '../storage/database.php',
    '../includes/PusherHelper.php',
    '../includes/Logger.php'
];

foreach ($requiredFiles as $file) {
    if (!file_exists($file)) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Required file not found: ' . $file
        ]);
        exit;
    }
}

require_once '../storage/database.php';
require_once '../includes/PusherHelper.php';
require_once '../includes/Logger.php';

// Now suppress errors for clean JSON output
error_reporting(0);
ini_set('display_errors', 0);


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
            'timeout' => 5,
            'ignore_errors' => true
        ]
    ];
    
    $context = stream_context_create($options);
    
    try {
        $result = @file_get_contents($url, false, $context);
        
        if ($result === false) {
            throw new Exception('Failed to connect to Python bridge');
        }
        
        $decoded = json_decode($result, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response from Python bridge');
        }
        
        return $decoded;
    } catch (Exception $e) {
        Logger::warning("Arduino communication failed", ['error' => $e->getMessage(), 'command' => $command]);
        return ['status' => 'error', 'message' => 'Arduino communication failed: ' . $e->getMessage()];
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON input
    $rawInput = file_get_contents('php://input');
    Logger::debug("Raw input received", ['input' => $rawInput]);
    
    $input = json_decode($rawInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input: ' . json_last_error_msg());
    }
    
    $rfidId = $input['rfid_id'] ?? '';
    $gateLocation = $input['gate_location'] ?? 'main_gate';
    
    Logger::debug("Parsed input", ['rfid_id' => $rfidId, 'gate_location' => $gateLocation]);
    
    if (empty($rfidId)) {
        throw new Exception('RFID ID is required');
    }
    
    // Get database connection
    $conn = getConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    Logger::debug("Database connection established");
    
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
            $accessResult = 'denied';
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
    try {
        $pusher = new PusherHelper();
        $broadcastResult = $pusher->broadcastRFIDAccess($responseData);
        Logger::debug("Pusher broadcast result", ['success' => $broadcastResult]);
    } catch (Exception $e) {
        Logger::error("Pusher broadcast failed", ['error' => $e->getMessage()]);
        // Don't fail the entire request if Pusher fails
    }
    

    

    
    // Send command to Arduino via Python bridge
    if ($accessResult === 'granted') {
        $arduinoCommand = 'open';
    } elseif ($accessResult === 'denied') {
        $arduinoCommand = 'unauthorized';
    } else {
        $arduinoCommand = 'close';
    }

    try {
        $arduinoResponse = sendToArduino($arduinoCommand);
        Logger::debug("Arduino command sent", ['command' => $arduinoCommand, 'response' => $arduinoResponse]);
    } catch (Exception $e) {
        Logger::warning("Arduino communication failed", ['error' => $e->getMessage()]);
        $arduinoResponse = ['status' => 'error', 'message' => 'Arduino communication failed'];
    }
    
    // Add Arduino response to response data
    $responseData['arduino_command'] = $arduinoCommand;
    $responseData['arduino_response'] = $arduinoResponse;
    
    // Broadcast gate control with Arduino integration
    if ($accessResult === 'granted') {
        try {
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
        } catch (Exception $e) {
            Logger::error("Gate status broadcast failed", ['error' => $e->getMessage()]);
        }
    }
    
    echo json_encode($responseData);
    
} catch (Exception $e) {
    // Log the error
    Logger::error("RFID scan API error", [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'input' => $_POST ?? $_GET ?? 'No input data'
    ]);
    
    // Clean any output buffer
    if (ob_get_level()) {
        ob_clean();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error',
        'error' => $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}
?>