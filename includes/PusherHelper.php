<?php
/**
 * Pusher Helper Class for Real-time Updates
 * Simple implementation without external dependencies
 */

class PusherHelper {
    private $appId;
    private $key;
    private $secret;
    private $cluster;
    private $useTLS;
    
    public function __construct() {
        $config = include 'config/pusher.php';
        $this->appId = $config['app_id'];
        $this->key = $config['key'];
        $this->secret = $config['secret'];
        $this->cluster = $config['cluster'];
        $this->useTLS = $config['useTLS'];
    }
    
    /**
     * Trigger an event to a channel
     */
    public function trigger($channel, $event, $data) {
        try {
            $timestamp = time();
            $body = json_encode([
                'name' => $event,
                'data' => json_encode($data),
                'channel' => $channel
            ]);
            
            $auth_version = '1.0';
            $auth_key = $this->key;
            $auth_timestamp = $timestamp;
            $auth_signature = $this->generateSignature('POST', '/apps/' . $this->appId . '/events', $body, $auth_timestamp);
            
            $url = ($this->useTLS ? 'https' : 'http') . '://api-' . $this->cluster . '.pusherapp.com/apps/' . $this->appId . '/events';
            
            $headers = [
                'Content-Type: application/json',
                'Authorization: Pusher ' . implode(', ', [
                    'auth_key=' . $auth_key,
                    'auth_timestamp=' . $auth_timestamp,
                    'auth_version=' . $auth_version,
                    'auth_signature=' . $auth_signature
                ])
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            return $httpCode === 200;
            
        } catch (Exception $e) {
            error_log("Pusher error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate authentication signature
     */
    private function generateSignature($method, $path, $body, $timestamp) {
        $string_to_sign = implode("\n", [
            $method,
            $path,
            'auth_key=' . $this->key . '&auth_timestamp=' . $timestamp . '&auth_version=1.0&body_md5=' . md5($body)
        ]);
        
        return hash_hmac('sha256', $string_to_sign, $this->secret);
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