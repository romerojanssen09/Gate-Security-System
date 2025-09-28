<?php
/**
 * Logger Class for Gate Security System
 * Handles error logging, debugging, and system monitoring
 */

class Logger {
    private static $logPath = null;
    private static $maxFileSize = 10485760; // 10MB
    
    /**
     * Initialize log path
     */
    private static function initLogPath() {
        if (self::$logPath === null) {
            // Try to determine the correct logs path
            $possiblePaths = [
                __DIR__ . '/../logs/',
                dirname(__DIR__) . '/logs/',
                'logs/'
            ];
            
            foreach ($possiblePaths as $path) {
                if (is_dir(dirname($path))) {
                    self::$logPath = $path;
                    break;
                }
            }
            
            // Create logs directory if it doesn't exist
            if (!is_dir(self::$logPath)) {
                mkdir(self::$logPath, 0755, true);
            }
        }
    }
    
    /**
     * Log error message
     */
    public static function error($message, $context = []) {
        self::writeLog('error', $message, $context);
    }
    
    /**
     * Log info message
     */
    public static function info($message, $context = []) {
        self::writeLog('info', $message, $context);
    }
    
    /**
     * Log warning message
     */
    public static function warning($message, $context = []) {
        self::writeLog('warning', $message, $context);
    }
    
    /**
     * Log debug message
     */
    public static function debug($message, $context = []) {
        $settingsPath = __DIR__ . '/../config/settings.php';
        if (file_exists($settingsPath)) {
            $settings = include $settingsPath;
            if (isset($settings['enable_debug']) && $settings['enable_debug']) {
                self::writeLog('debug', $message, $context);
            }
        } else {
            // Default to logging debug messages if settings file doesn't exist
            self::writeLog('debug', $message, $context);
        }
    }
    
    /**
     * Log security event
     */
    public static function security($message, $context = []) {
        self::writeLog('security', $message, $context);
        
        // Also log to system error log for critical security events
        error_log("SECURITY: $message - " . json_encode($context));
    }
    
    /**
     * Write log entry to file
     */
    private static function writeLog($level, $message, $context = []) {
        self::initLogPath();
        
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' - Context: ' . json_encode($context) : '';
        $logEntry = "[$timestamp] [$level] $message$contextStr" . PHP_EOL;
        
        $logFile = self::$logPath . $level . '.log';
        
        // Check if log rotation is needed
        if (file_exists($logFile) && filesize($logFile) > self::$maxFileSize) {
            self::rotateLog($logFile);
        }
        
        // Write to log file
        try {
            file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        } catch (Exception $e) {
            // Fallback to error_log if file writing fails
            error_log("Logger write failed: " . $e->getMessage() . " - Original message: $message");
        }
        
        // Also write to PHP error log for errors
        if ($level === 'error') {
            error_log($logEntry);
        }
    }
    
    /**
     * Rotate log file when it gets too large
     */
    private static function rotateLog($logFile) {
        $backupFile = $logFile . '.' . date('Y-m-d-H-i-s');
        rename($logFile, $backupFile);
        
        // Keep only last 5 backup files
        $pattern = dirname($logFile) . '/' . basename($logFile) . '.*';
        $files = glob($pattern);
        if (count($files) > 5) {
            usort($files, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            
            // Remove oldest files
            for ($i = 0; $i < count($files) - 5; $i++) {
                unlink($files[$i]);
            }
        }
    }
    
    /**
     * Get recent log entries
     */
    public static function getRecentLogs($level = 'error', $lines = 50) {
        self::initLogPath();
        $logFile = self::$logPath . $level . '.log';
        
        if (!file_exists($logFile)) {
            return [];
        }
        
        $logs = [];
        $file = new SplFileObject($logFile);
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();
        
        $startLine = max(0, $totalLines - $lines);
        $file->seek($startLine);
        
        while (!$file->eof()) {
            $line = trim($file->current());
            if (!empty($line)) {
                $logs[] = $line;
            }
            $file->next();
        }
        
        return $logs;
    }
    
    /**
     * Clear log file
     */
    public static function clearLog($level) {
        self::initLogPath();
        $logFile = self::$logPath . $level . '.log';
        if (file_exists($logFile)) {
            file_put_contents($logFile, '');
            return true;
        }
        return false;
    }
}
?>