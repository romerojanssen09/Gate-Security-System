<?php
/**
 * Database Setup Script
 * Run this script to initialize the database and create default admin user
 */

require_once '../storage/database.php';
require_once '../includes/Logger.php';

echo "<h2>Gate Security System - Database Setup</h2>";

try {
    echo "<p>Initializing database connection...</p>";
    
    // Test database connection
    $conn = getConnection();
    if ($conn) {
        echo "<p style='color: green;'>✓ Database connection successful</p>";
        
        // Test table creation
        $tables = ['admins', 'rfid_cards', 'access_logs', 'system_settings'];
        foreach ($tables as $table) {
            $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
            if (mysqli_num_rows($result) > 0) {
                echo "<p style='color: green;'>✓ Table '$table' exists</p>";
            } else {
                echo "<p style='color: red;'>✗ Table '$table' missing</p>";
            }
        }
        
        // Check default admin
        $adminCheck = mysqli_query($conn, "SELECT username FROM admins WHERE username = 'admin'");
        if (mysqli_num_rows($adminCheck) > 0) {
            echo "<p style='color: green;'>✓ Default admin user exists</p>";
            echo "<p><strong>Default Login Credentials:</strong><br>";
            echo "Username: admin<br>";
            echo "Password: admin123</p>";
        } else {
            echo "<p style='color: orange;'>! No admin user found</p>";
        }
        
        // Check system settings
        $settingsCheck = mysqli_query($conn, "SELECT COUNT(*) as count FROM system_settings");
        $settingsRow = mysqli_fetch_assoc($settingsCheck);
        echo "<p style='color: green;'>✓ System settings: {$settingsRow['count']} entries</p>";
        
        echo "<h3>Database Setup Complete!</h3>";
        echo "<p><a href='../index.php'>Go to Login Page</a></p>";
        
    } else {
        echo "<p style='color: red;'>✗ Database connection failed</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    Logger::error("Database setup failed", ['error' => $e->getMessage()]);
}
?>