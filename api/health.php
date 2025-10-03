<?php
/**
 * Health check endpoint for SmartApply Pro PHP backend
 */

require_once '../config/config.php';

setCORSHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJSONResponse(['error' => 'Method not allowed'], 405);
}

try {
    // Basic health check without database dependency
    $response = [
        'status' => 'healthy',
        'timestamp' => date('c'),
        'version' => '1.0.0',
        'environment' => 'php',
        'server' => 'SmartApply Pro API'
    ];
    
    // Try database connection if available
    try {
        require_once '../config/database.php';
        $database = new Database();
        $db = $database->getConnection();
        
        if ($db) {
            $stmt = $db->query("SELECT 1 as test");
            $result = $stmt->fetch();
            if ($result && isset($result['test']) && $result['test'] == 1) {
                $response['database'] = 'connected';
                $response['database_test'] = 'passed';
            } else {
                $response['database'] = 'connected';
                $response['database_test'] = 'failed';
            }
        } else {
            $response['database'] = 'disconnected';
            $response['database_test'] = 'no_connection';
        }
    } catch (Exception $e) {
        $response['database'] = 'unavailable';
        $response['database_error'] = $e->getMessage();
    }
    
    sendJSONResponse($response);
    
} catch (Exception $e) {
    sendJSONResponse([
        'status' => 'unhealthy',
        'timestamp' => date('c'),
        'error' => $e->getMessage(),
        'environment' => 'php'
    ], 500);
}
?>