<?php
/**
 * Database configuration for SmartApply Pro
 */

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function __construct() {
        // Load from environment variables or set defaults
        $this->host = $_ENV['DB_HOST'] ?? 'localhost';
        $this->db_name = $_ENV['DB_NAME'] ?? 'u110140557_ProSmart';
        $this->username = $_ENV['DB_USER'] ?? 'u110140557_tregOmar';
        $this->password = $_ENV['DB_PASS'] ?? '14#nI+$!zQ';
        
        // Special handling for shared hosting environments
        if (class_exists('ErrorLogger')) {
            ErrorLogger::log('INFO', 'Database configuration loaded', [
                'host' => $this->host,
                'database' => $this->db_name,
                'username' => $this->username,
                'environment' => $this->detectEnvironment()
            ]);
        }
    }
    
    /**
     * Detect the current environment
     */
    private function detectEnvironment() {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
            return 'development';
        } else if (strpos($host, 'smartapplypro.com') !== false) {
            return 'production';
        } else {
            return 'unknown';
        }
    }

    public function getConnection() {
        $this->conn = null;

        try {
            // Add port and timeout options for better production compatibility
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                PDO::ATTR_TIMEOUT => 10 // 10 second timeout
            ];
            
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
            if (class_exists('ErrorLogger')) {
                ErrorLogger::log('INFO', 'Database connected successfully', [
                    'host' => $this->host,
                    'database' => $this->db_name,
                    'username' => $this->username
                ]);
            }
            
        } catch(PDOException $exception) {
            $errorMessage = "Database connection error: " . $exception->getMessage();
            
            if (class_exists('ErrorLogger')) {
                ErrorLogger::logDatabaseError($errorMessage, '', [
                    'host' => $this->host,
                    'database' => $this->db_name,
                    'username' => $this->username,
                    'error_code' => $exception->getCode()
                ]);
            } else {
                error_log($errorMessage);
            }
            
            throw $exception;
        }

        return $this->conn;
    }
}

// Helper function for easy database access
function getDatabase() {
    $database = new Database();
    return $database->getConnection();
}
?>