<?php
/**
 * Error logging utility for SmartApply Pro
 * Creates and manages error logs for production environment
 */

class ErrorLogger {
    private static $logFile = null;
    private static $initialized = false;
    
    /**
     * Initialize error logging
     */
    public static function init() {
        if (self::$initialized) {
            return;
        }
        
        // Create logs directory if it doesn't exist
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Set log file path
        self::$logFile = $logDir . '/php_errors.log';
        
        // Configure PHP error logging
        ini_set('log_errors', 1);
        ini_set('error_log', self::$logFile);
        
        // For production, disable display_errors and enable logging
        if (!self::isDevelopment()) {
            ini_set('display_errors', 0);
            error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
        } else {
            ini_set('display_errors', 1);
            error_reporting(E_ALL);
        }
        
        // Set up error and exception handlers
        set_error_handler([self::class, 'errorHandler']);
        set_exception_handler([self::class, 'exceptionHandler']);
        
        self::$initialized = true;
        
        // Log initialization
        self::log('INFO', 'Error logging system initialized');
    }
    
    /**
     * Custom error handler
     */
    public static function errorHandler($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        $errorTypes = [
            E_ERROR => 'ERROR',
            E_WARNING => 'WARNING',
            E_PARSE => 'PARSE',
            E_NOTICE => 'NOTICE',
            E_CORE_ERROR => 'CORE_ERROR',
            E_CORE_WARNING => 'CORE_WARNING',
            E_COMPILE_ERROR => 'COMPILE_ERROR',
            E_COMPILE_WARNING => 'COMPILE_WARNING',
            E_USER_ERROR => 'USER_ERROR',
            E_USER_WARNING => 'USER_WARNING',
            E_USER_NOTICE => 'USER_NOTICE',
            E_STRICT => 'STRICT',
            E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
            E_DEPRECATED => 'DEPRECATED',
            E_USER_DEPRECATED => 'USER_DEPRECATED'
        ];
        
        $errorType = $errorTypes[$severity] ?? 'UNKNOWN';
        $logMessage = "[$errorType] $message in $file on line $line";
        
        self::log('ERROR', $logMessage);
        
        // Don't execute PHP internal error handler
        return true;
    }
    
    /**
     * Custom exception handler
     */
    public static function exceptionHandler($exception) {
        $message = sprintf(
            "[EXCEPTION] %s in %s on line %d\nStack trace:\n%s",
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );
        
        self::log('CRITICAL', $message);
    }
    
    /**
     * Log a message
     */
    public static function log($level, $message, $context = []) {
        if (!self::$initialized) {
            self::init();
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $contextString = !empty($context) ? ' | Context: ' . json_encode($context) : '';
        $logEntry = "[$timestamp] [$level] $message$contextString" . PHP_EOL;
        
        file_put_contents(self::$logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
        // Also log to system error log
        error_log("SmartApply [$level] $message");
    }
    
    /**
     * Log database errors specifically
     */
    public static function logDatabaseError($error, $query = '', $params = []) {
        $context = [
            'query' => $query,
            'params' => $params,
            'server_info' => [
                'PHP_VERSION' => PHP_VERSION,
                'SERVER_SOFTWARE' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
            ]
        ];
        
        self::log('DATABASE_ERROR', $error, $context);
    }
    
    /**
     * Log API errors
     */
    public static function logApiError($endpoint, $error, $requestData = []) {
        $context = [
            'endpoint' => $endpoint,
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown',
            'request_data' => $requestData,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
        ];
        
        self::log('API_ERROR', $error, $context);
    }
    
    /**
     * Check if we're in development environment
     */
    private static function isDevelopment() {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        return strpos($host, 'localhost') !== false || 
               strpos($host, '127.0.0.1') !== false ||
               strpos($host, '.local') !== false;
    }
    
    /**
     * Get log file path
     */
    public static function getLogFile() {
        return self::$logFile;
    }
    
    /**
     * Get recent log entries
     */
    public static function getRecentLogs($lines = 100) {
        if (!file_exists(self::$logFile)) {
            return [];
        }
        
        $content = file_get_contents(self::$logFile);
        $logLines = explode("\n", $content);
        $logLines = array_filter($logLines); // Remove empty lines
        
        return array_slice(array_reverse($logLines), 0, $lines);
    }
    
    /**
     * Clear log file
     */
    public static function clearLogs() {
        if (file_exists(self::$logFile)) {
            file_put_contents(self::$logFile, '');
        }
    }
}
?>