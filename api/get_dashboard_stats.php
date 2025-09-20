<?php
/**
 * API endpoint to get dashboard statistics for real-time updates
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../storage/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get database connection
    $conn = getConnection();
    
    // Get statistics
    $statsQuery = "
        SELECT 
            COUNT(*) as total_logs,
            SUM(CASE WHEN access_result = 'granted' THEN 1 ELSE 0 END) as granted_count,
            SUM(CASE WHEN access_result = 'denied' THEN 1 ELSE 0 END) as denied_count,
            SUM(CASE WHEN DATE(access_timestamp) = CURDATE() THEN 1 ELSE 0 END) as today_count
        FROM access_logs
    ";
    $statsResult = mysqli_query($conn, $statsQuery);
    $stats = mysqli_fetch_assoc($statsResult);
    
    // Get active RFID cards count
    $cardsQuery = "SELECT COUNT(*) as active_cards FROM rfid_cards WHERE status = 'active'";
    $cardsResult = mysqli_query($conn, $cardsQuery);
    $cardsStats = mysqli_fetch_assoc($cardsResult);
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'granted_count' => intval($stats['granted_count']),
            'denied_count' => intval($stats['denied_count']),
            'today_count' => intval($stats['today_count']),
            'active_cards' => intval($cardsStats['active_cards'])
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error',
        'error' => $e->getMessage()
    ]);
}
?>