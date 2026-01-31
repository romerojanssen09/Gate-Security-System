<?php
/**
 * Automatic Gate Security System - Database Configuration
 * Enhanced database connection with error handling and table creation
 */

// Database configuration
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "gate_security_fresh_db";

// Global connection variable
$conn = null;

/**
 * Initialize database connection with retry logic
 */
function initializeDatabase() {
    global $conn, $host, $user, $pass, $dbname;
    
    $maxRetries = 3;
    $retryCount = 0;
    
    while ($retryCount < $maxRetries) {
        try {
            // Connect to MySQL server first
            $conn = @mysqli_connect($host, $user, $pass);
            
            if (!$conn) {
                throw new Exception("Failed to connect to MySQL server: " . mysqli_connect_error());
            }
            
            // Create database if it doesn't exist
            $createDbQuery = "CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8 COLLATE utf8_general_ci";
            if (!@mysqli_query($conn, $createDbQuery)) {
                error_log("Warning: Could not create database: " . mysqli_error($conn));
            }
            
            // Select the database
            if (!@mysqli_select_db($conn, $dbname)) {
                throw new Exception("Failed to select database: " . mysqli_error($conn));
            }
            
            // Set character set
            mysqli_set_charset($conn, "utf8");
            
            // Create all required tables
            createTables();
            
            // Insert default settings
            insertDefaultSettings();
            
            return true;
            
        } catch (Exception $e) {
            $retryCount++;
            error_log("Database connection attempt $retryCount failed: " . $e->getMessage());
            
            if ($retryCount >= $maxRetries) {
                // Don't die, just log and return false
                error_log("Database connection failed after $maxRetries attempts");
                return false;
            }
            
            sleep(1); // Wait 1 second before retry
        }
    }
    
    return false;
}

/**
 * Create all required database tables
 */
function createTables() {
    global $conn;
    
    // Check if tables already exist
    $checkQuery = "SHOW TABLES LIKE 'admins'";
    $result = @mysqli_query($conn, $checkQuery);
    
    if ($result && mysqli_num_rows($result) > 0) {
        // Tables already exist, skip creation
        return true;
    }
    
    $tables = [
        // Admins table
        "CREATE TABLE admins (
            id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            last_login TIMESTAMP NULL,
            failed_attempts INT(3) DEFAULT 0,
            locked_until TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
        
        // RFID Cards table
        "CREATE TABLE rfid_cards (
            id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            rfid_id VARCHAR(50) NOT NULL UNIQUE,
            full_name VARCHAR(100) NOT NULL,
            role ENUM('student', 'teacher', 'staff', 'visitor') NOT NULL,
            plate_number VARCHAR(20) NULL,
            status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
            created_by INT(11) UNSIGNED,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_rfid_id (rfid_id),
            INDEX idx_status (status),
            FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
        
        // Access Logs table
        "CREATE TABLE access_logs (
            id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            rfid_id VARCHAR(50) NOT NULL,
            card_id INT(11) UNSIGNED NULL,
            full_name VARCHAR(100) NULL,
            access_result ENUM('granted', 'denied') NOT NULL,
            denial_reason VARCHAR(100) NULL,
            access_type ENUM('time_in', 'time_out') NULL,
            gate_location VARCHAR(50) DEFAULT 'main_gate',
            access_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            ip_address VARCHAR(45) NULL,
            INDEX idx_rfid_timestamp (rfid_id, access_timestamp),
            INDEX idx_timestamp (access_timestamp),
            INDEX idx_result (access_result),
            INDEX idx_access_type (access_type),
            FOREIGN KEY (card_id) REFERENCES rfid_cards(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
        
        // System Settings table
        "CREATE TABLE system_settings (
            id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(50) NOT NULL UNIQUE,
            setting_value TEXT NOT NULL,
            description VARCHAR(255) NULL,
            updated_by INT(11) UNSIGNED,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (updated_by) REFERENCES admins(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
        
        // RFID Scan Timeout table
        "CREATE TABLE rfid_scan_timeouts (
            id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            rfid_id VARCHAR(50) NOT NULL,
            last_scan_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            timeout_until TIMESTAMP NULL,
            scan_count INT(3) DEFAULT 1,
            current_status ENUM('in', 'out') DEFAULT 'out',
            gate_location VARCHAR(50) DEFAULT 'main_gate',
            INDEX idx_rfid_timeout (rfid_id, timeout_until),
            INDEX idx_timeout_until (timeout_until),
            INDEX idx_rfid_status (rfid_id, current_status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
    ];
    
    foreach ($tables as $tableQuery) {
        if (!@mysqli_query($conn, $tableQuery)) {
            error_log("Error creating table: " . mysqli_error($conn));
            // Don't throw exception, just log and continue
        }
    }
    
    return true;
}

/**
 * Insert default system settings
 */
function insertDefaultSettings() {
    global $conn;
    
    $defaultSettings = [
        ['session_timeout', '1800', 'Session timeout in seconds (30 minutes)'],
        ['max_login_attempts', '3', 'Maximum failed login attempts before lockout'],
        ['lockout_duration', '900', 'Account lockout duration in seconds (15 minutes)'],
        ['gate_open_duration', '10', 'Gate open duration in seconds'],
        ['system_name', 'Holy Family High School Gate Security', 'System name for display'],
        ['log_retention_days', '180', 'Number of days to retain access logs'],
        ['enable_alerts', '1', 'Enable security alerts (1=enabled, 0=disabled)'],
        ['rfid_scan_timeout', '30', 'RFID scan timeout in seconds (prevents rapid re-scanning)'],
        ['rfid_max_scans_before_timeout', '3', 'Maximum scans before timeout is applied'],
        ['rfid_timeout_duration', '300', 'RFID timeout duration in seconds (5 minutes)']
    ];
    
    foreach ($defaultSettings as $setting) {
        $checkQuery = "SELECT id FROM system_settings WHERE setting_key = ?";
        $stmt = mysqli_prepare($conn, $checkQuery);
        mysqli_stmt_bind_param($stmt, "s", $setting[0]);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) == 0) {
            $insertQuery = "INSERT INTO system_settings (setting_key, setting_value, description) VALUES (?, ?, ?)";
            $insertStmt = mysqli_prepare($conn, $insertQuery);
            mysqli_stmt_bind_param($insertStmt, "sss", $setting[0], $setting[1], $setting[2]);
            
            if (!mysqli_stmt_execute($insertStmt)) {
                error_log("Error inserting default setting {$setting[0]}: " . mysqli_error($conn));
            }
            mysqli_stmt_close($insertStmt);
        }
        mysqli_stmt_close($stmt);
    }
}

/**
 * Create default admin user if none exists
 */
function createDefaultAdmin() {
    global $conn;
    
    $checkQuery = "SELECT id FROM admins LIMIT 1";
    $result = mysqli_query($conn, $checkQuery);
    
    if (mysqli_num_rows($result) == 0) {
        $defaultUsername = 'admin';
        $defaultPassword = password_hash('admin123', PASSWORD_BCRYPT);
        $defaultName = 'System Administrator';
        $defaultEmail = 'admin@holyfamily.edu.ph';
        
        $insertQuery = "INSERT INTO admins (username, password, full_name, email) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insertQuery);
        mysqli_stmt_bind_param($stmt, "ssss", $defaultUsername, $defaultPassword, $defaultName, $defaultEmail);
        
        if (mysqli_stmt_execute($stmt)) {
            error_log("Default admin user created: username=admin, password=admin123");
        } else {
            error_log("Error creating default admin: " . mysqli_error($conn));
        }
        mysqli_stmt_close($stmt);
    }
}

/**
 * Get database connection
 */
function getConnection() {
    global $conn;
    
    if ($conn === null || mysqli_ping($conn) === false) {
        initializeDatabase();
    }
    
    return $conn;
}

/**
 * Close database connection
 */
function closeConnection() {
    global $conn;
    
    if ($conn) {
        mysqli_close($conn);
        $conn = null;
    }
}

/**
 * Execute prepared statement safely
 */
function executeQuery($query, $params = [], $types = '') {
    global $conn;
    
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . mysqli_error($conn));
    }
    
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        $error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        throw new Exception("Execute failed: " . $error);
    }
    
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    
    return $result;
}

// Initialize database on include
try {
    if (!initializeDatabase()) {
        error_log("Database initialization failed - check logs");
    }
    createDefaultAdmin();
} catch (Exception $e) {
    error_log("Database initialization error: " . $e->getMessage());
}

?>
