<?php
/**
 * Pusher Helper Class for Real-time Updates
 * Using official Pusher PHP library
 */

// Load Pusher library if available
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

class PusherHelper {
    private $pusher;
    
    public function __construct() {
        try {
            $configPath = __DIR__ . '/../config/pusher.php';
            if (!file_exists($configPath)) {
                throw new Exception("Pusher config file not found at: " . $configPath);
            }
            
            $config = include $configPath;
            if (!$config || !is_array($config)) {
                throw new Exception("Invalid Pusher configuration");
            }
            
            // Check if autoload exists
            $autoloadPath = __DIR__ . '/../vendor/autoload.php';
            if (!file_exists($autoloadPath)) {
                throw new Exception("Composer autoload not found. Run 'composer install'");
            }
            
            // Initialize official Pusher client
            if (class_exists('Pusher\Pusher')) {
                $this->pusher = new \Pusher\Pusher(
                    $config['key'],
                    $config['secret'],
                    $config['app_id'],
                    [
                        'cluster' => $config['cluster'],
                        'useTLS' => $config['useTLS']
                    ]
                );
            } else {
                throw new Exception("Pusher class not available");
            }
            
            error_log("Pusher initialized with official library - App ID: {$config['app_id']}, Cluster: {$config['cluster']}");
        } catch (Exception $e) {
            error_log("Pusher initialization failed: " . $e->getMessage());
            $this->pusher = null;
        }
    }
    
    /**
     * Trigger an event to a channel
     */
    public function trigger($channel, $event, $data) {
        if ($this->pusher === null) {
            error_log("Pusher not initialized, skipping broadcast");
            return false;
        }
        
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