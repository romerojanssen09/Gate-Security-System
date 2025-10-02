<?php
/**
 * Manual Gate Control API Endpoint
 * Handles manual open/close commands and forwards them to Python bridge
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

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
        return ['status' => 'error', 'message' => 'Arduino communication failed: ' . $e->getMessage()];
    }
}

try {
    // Get JSON input
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input: ' . json_last_error_msg());
    }
    
    $command = $input['command'] ?? '';
    
    if (empty($command)) {
        throw new Exception('Command is required');
    }
    
    // Validate command
    if (!in_array($command, ['manualopen', 'manualclose'])) {
        throw new Exception('Invalid command. Only manualopen and manualclose are allowed.');
    }
    
    // Send command to Python bridge
    $arduinoResponse = sendToArduino($command);
    
    // Prepare response
    $response = [
        'success' => true,
        'command' => $command,
        'arduino_response' => $arduinoResponse,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>