<?php
/**
 * Logger Class for Gate Security System
 * Handles error logging, debugging, and system monitoring
 */

class Logger {
    private static $logPath = '../logs/';
    private static $maxFileSize = 10485760; // 10MB
    
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
        $settings = include 'config/settings.php';
        if ($settings['enable_debug']) {
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
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' - Context: ' . json_encode($context) : '';
        $logEntry = "[$timestamp] [$level] $message$contextStr" . PHP_EOL;
        
        $logFile = self::$logPath . $level . '.log';
        
        // Check if log rotation is needed
        if (file_exists($logFile) && filesize($logFile) > self::$maxFileSize) {
            self::rotateLog($logFile);
        }
        
        // Write to log file
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
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
        $logFile = self::$logPath . $level . '.log';
        if (file_exists($logFile)) {
            file_put_contents($logFile, '');
            return true;
        }
        return false;
    }
}
?>