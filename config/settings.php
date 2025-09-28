<?php
/**
 * Application Settings Configuration
 * Global settings for the Gate Security System
 */

return [
    // Application Settings
    'app_name' => 'Holy Family High School Gate Security System',
    'app_version' => '1.0.0',
    'timezone' => 'Asia/Manila',
    
    // Security Settings
    'session_timeout' => 1800, // 30 minutes
    'max_login_attempts' => 3,
    'lockout_duration' => 900, // 15 minutes
    'password_min_length' => 6,
    
    // Gate Settings
    'gate_open_duration' => 10, // seconds
    'gate_locations' => ['main_gate', 'side_gate', 'parking_gate'],
    
    // System Settings
    'log_retention_days' => 180, // 6 months
    'enable_debug' => true,
    'enable_alerts' => true,
    
    // File Paths
    'log_path' => 'logs/',
    'upload_path' => 'uploads/',
    'backup_path' => 'backups/',
    
    // Pagination
    'records_per_page' => 25,
    'max_records_per_page' => 100,
    
    // RFID Settings
    'rfid_id_length' => [8, 50], // min, max length
    'supported_roles' => ['student', 'teacher', 'staff', 'visitor'],
    
    // Report Settings
    'report_formats' => ['pdf', 'excel', 'csv'],
    'max_report_records' => 10000
];
?>