<?php
/**
 * Database Configuration File
 * Centralized database settings for the Gate Security System
 */

return [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'gate_security_db',
    'charset' => 'utf8',
    'options' => [
        'timeout' => 30,
        'retry_attempts' => 3,
        'retry_delay' => 1
    ]
];
?>