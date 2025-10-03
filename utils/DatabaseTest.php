<?php
/**
 * Database connection test utility for SmartApply Pro
 * This file helps diagnose database connection issues
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

function testDatabaseConnection() {
    echo "<h2>SmartApply Pro - Database Connection Test</h2>\n";
    echo "<style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .success { color: green; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: red; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: blue; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .warning { color: orange; background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>";
    
    // Check PHP environment
    echo "<h3>1. PHP Environment Check</h3>\n";
    echo "<div class='info'>PHP Version: " . PHP_VERSION . "</div>\n";
    
    // Check PDO MySQL extension
    if (extension_loaded('pdo_mysql')) {
        echo "<div class='success'>✓ PDO MySQL extension is loaded</div>\n";
    } else {
        echo "<div class='error'>✗ PDO MySQL extension is NOT loaded</div>\n";
        return;
    }
    
    // Check environment variables
    echo "<h3>2. Environment Variables</h3>\n";
    $dbHost = $_ENV['DB_HOST'] ?? 'Not set';
    $dbName = $_ENV['DB_NAME'] ?? 'Not set';
    $dbUser = $_ENV['DB_USER'] ?? 'Not set';
    $dbPass = $_ENV['DB_PASS'] ?? 'Not set';
    
    echo "<div class='info'>";
    echo "DB_HOST: " . htmlspecialchars($dbHost) . "<br>\n";
    echo "DB_NAME: " . htmlspecialchars($dbName) . "<br>\n";
    echo "DB_USER: " . htmlspecialchars($dbUser) . "<br>\n";
    echo "DB_PASS: " . (empty($dbPass) ? 'Not set' : '[SET - ' . strlen($dbPass) . ' characters]') . "<br>\n";
    echo "</div>\n";
    
    // Test basic connection
    echo "<h3>3. Database Connection Test</h3>\n";
    
    try {
        $database = new Database();
        $conn = $database->getConnection();
        
        if ($conn) {
            echo "<div class='success'>✓ Database connection successful!</div>\n";
            
            // Test basic query with fallback for different database types
            try {
                // Try MySQL/MariaDB specific query first
                $stmt = $conn->query("SELECT VERSION() as mysql_version, NOW() as current_time");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result && isset($result['mysql_version']) && isset($result['current_time'])) {
                    echo "<div class='success'>✓ Basic query test successful</div>\n";
                    echo "<div class='info'>";
                    echo "MySQL Version: " . htmlspecialchars($result['mysql_version']) . "<br>\n";
                    echo "Current Time: " . htmlspecialchars($result['current_time']) . "<br>\n";
                    echo "</div>\n";
                } else {
                    echo "<div class='warning'>⚠ Basic query returned incomplete results, trying fallback...</div>\n";
                    // Fallback: try a simple query that should work on any database
                    $stmt = $conn->query("SELECT 1 as test_value");
                    $fallbackResult = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($fallbackResult && isset($fallbackResult['test_value'])) {
                        echo "<div class='success'>✓ Fallback query test successful</div>\n";
                        echo "<div class='info'>Database appears to be working but may not be MySQL/MariaDB compatible</div>\n";
                    } else {
                        echo "<div class='error'>✗ Both primary and fallback queries failed</div>\n";
                    }
                }
                
                // Check if tables exist  
                echo "<h3>4. Table Structure Check</h3>\n";
                $tables = ['licenses', 'orders', 'admin_users', 'user_accounts'];  // Updated to match actual schema
                foreach ($tables as $table) {
                    try {
                        $stmt = $conn->query("SHOW TABLES LIKE '" . $table . "'");
                        if ($stmt->rowCount() > 0) {
                            echo "<div class='success'>✓ Table '{$table}' exists</div>\n";
                            
                            // Get table structure
                            $stmt = $conn->query("DESCRIBE " . $table);
                            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            echo "<div class='info'>";
                            echo "<strong>Table '{$table}' structure:</strong><br>\n";
                            echo "<pre>";
                            foreach ($columns as $column) {
                                echo htmlspecialchars($column['Field'] . " | " . $column['Type'] . " | " . $column['Null'] . " | " . $column['Key']) . "\n";
                            }
                            echo "</pre>";
                            echo "</div>\n";
                            
                        } else {
                            echo "<div class='warning'>⚠ Table '{$table}' does not exist</div>\n";
                        }
                    } catch (Exception $e) {
                        echo "<div class='error'>✗ Error checking table '{$table}': " . htmlspecialchars($e->getMessage()) . "</div>\n";
                    }
                }
                
            } catch (Exception $e) {
                echo "<div class='error'>✗ Database query test failed: " . htmlspecialchars($e->getMessage()) . "</div>\n";
                echo "<div class='warning'>This might indicate:</div>\n";
                echo "<div class='info'>";
                echo "• Database server might not be MySQL/MariaDB compatible<br>\n";
                echo "• SQL functions VERSION() or NOW() are not supported<br>\n";
                echo "• Database connection has limited permissions<br>\n";
                echo "• Trying a simple connectivity test instead...<br>\n";
                echo "</div>\n";
                
                // Try the most basic query possible
                try {
                    $stmt = $conn->query("SELECT 1");
                    if ($stmt) {
                        echo "<div class='success'>✓ Basic connectivity test passed - database is reachable</div>\n";
                    }
                } catch (Exception $basicError) {
                    echo "<div class='error'>✗ Even basic connectivity test failed: " . htmlspecialchars($basicError->getMessage()) . "</div>\n";
                }
            }
            
        } else {
            echo "<div class='error'>✗ Database connection failed - no connection object returned</div>\n";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>✗ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</div>\n";
        
        // Additional troubleshooting info
        echo "<h3>5. Troubleshooting Information</h3>\n";
        echo "<div class='warning'>";
        echo "<strong>Common solutions for shared hosting:</strong><br>\n";
        echo "1. Try 'localhost' or '127.0.0.1' as DB_HOST<br>\n";
        echo "2. Check if your hosting provider uses a different MySQL host<br>\n";
        echo "3. Verify database credentials in hosting control panel<br>\n";
        echo "4. Check if MySQL service is running<br>\n";
        echo "5. Contact hosting provider if issue persists<br>\n";
        echo "</div>\n";
    }
    
    // Show error log entries if available
    echo "<h3>6. Recent Error Log Entries</h3>\n";
    try {
        if (class_exists('ErrorLogger')) {
            $logs = ErrorLogger::getRecentLogs(10);
            if (!empty($logs)) {
                echo "<div class='info'>";
                echo "<pre>";
                foreach (array_slice($logs, 0, 10) as $log) {
                    echo htmlspecialchars($log) . "\n";
                }
                echo "</pre>";
                echo "</div>\n";
            } else {
                echo "<div class='info'>No recent error log entries found.</div>\n";
            }
        }
    } catch (Exception $e) {
        echo "<div class='error'>Could not read error logs: " . htmlspecialchars($e->getMessage()) . "</div>\n";
    }
}

// Only run if accessed directly
if (basename($_SERVER['SCRIPT_NAME']) === 'DatabaseTest.php') {
    testDatabaseConnection();
}
?>