<?php
/**
 * Pusher Configuration for Real-time Updates
 */

return [
    'app_id' => '2045590',
    'key' => '5b24b867b55f7decb7a5',
    'secret' => 'ffa02bca1c9e14fe3697',
    'cluster' => 'ap1',
    'useTLS' => true,
    'channels' => [
        'rfid-access' => 'rfid-access-channel',
        'gate-status' => 'gate-status-channel'
    ]
];
?>