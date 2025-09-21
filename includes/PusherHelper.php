<?php
/**
 * Pusher Helper Class for Real-time Updates
 * Using official Pusher PHP library
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Pusher\Pusher;

class PusherHelper {
    private $pusher;
    
    public function __construct() {
        $configPath = __DIR__ . '/../config/pusher.php';
        if (!file_exists($configPath)) {
            throw new Exception("Pusher config file not found at: " . $configPath);
        }
        
        $config = include $configPath;
        if (!$config || !is_array($config)) {
            throw new Exception("Invalid Pusher configuration");
        }
        
        // Initialize official Pusher client
        $this->pusher = new Pusher(
            $config['key'],
            $config['secret'],
            $config['app_id'],
            [
                'cluster' => $config['cluster'],
                'useTLS' => $config['useTLS']
            ]
        );
        
        error_log("Pusher initialized with official library - App ID: {$config['app_id']}, Cluster: {$config['cluster']}");
    }
    
    /**
     * Trigger an event to a channel
     */
    public function trigger($channel, $event, $data) {
        try {
            $result = $this->pusher->trigger($channel, $event, $data);
            return true;
        } catch (Exception $e) {
            error_log("Pusher error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Broadcast RFID access event
     */
    public function broadcastRFIDAccess($rfidData) {
        return $this->trigger('rfid-access-channel', 'rfid-scanned', $rfidData);
    }
    
    /**
     * Broadcast gate status change
     */
    public function broadcastGateStatus($gateData) {
        return $this->trigger('gate-status-channel', 'gate-status-changed', $gateData);
    }
}
?>