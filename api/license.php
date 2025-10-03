<?php
/**
 * License API endpoints for SmartApply Pro
 */

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../utils/LicenseGenerator.php';

// Set CORS headers
setCORSHeaders();

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'] ?? '';

// Parse the path
$pathParts = explode('/', trim($path, '/'));
$action = $pathParts[0] ?? '';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Route the request
switch ($method) {
    case 'POST':
        if ($action === 'verify') {
            verifyLicense($db);
        } else {
            sendJSONResponse(['error' => 'Invalid endpoint'], 404);
        }
        break;
        
    case 'GET':
        if (!empty($pathParts[0])) {
            $licenseKey = $pathParts[0];
            if (isset($pathParts[1]) && $pathParts[1] === 'verifications') {
                getLicenseVerifications($db, $licenseKey);
            } else {
                getLicenseInfo($db, $licenseKey);
            }
        } else {
            sendJSONResponse(['error' => 'License key required'], 400);
        }
        break;
        
    default:
        sendJSONResponse(['error' => 'Method not allowed'], 405);
}

/**
 * Verify license key
 */
function verifyLicense($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['licenseKey'])) {
        sendJSONResponse(['error' => 'License key is required'], 400);
    }
    
    $licenseKey = trim($input['licenseKey']);
    $email = $input['email'] ?? null;
    
    // Validate license key format
    if (!LicenseGenerator::validateLicenseKeyFormat($licenseKey)) {
        sendJSONResponse([
            'valid' => false,
            'message' => 'Invalid license key format'
        ], 400);
    }
    
    // Get client information
    $clientIP = $_SERVER['REMOTE_ADDR'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    try {
        // Get license from database
        $stmt = $db->prepare("SELECT * FROM licenses WHERE license_key = ?");
        $stmt->execute([$licenseKey]);
        $license = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $verificationStatus = 'invalid';
        $responseData = [
            'valid' => false,
            'message' => 'Invalid license key'
        ];
        
        if ($license) {
            // Check if license is active
            if ($license['status'] !== 'active') {
                $verificationStatus = 'invalid';
                $responseData = [
                    'valid' => false,
                    'message' => "License is {$license['status']}"
                ];
            }
            // Check if license has expired
            else if ($license['expiry_date'] && new DateTime($license['expiry_date']) < new DateTime()) {
                $verificationStatus = 'expired';
                $responseData = [
                    'valid' => false,
                    'message' => 'License has expired',
                    'expiryDate' => $license['expiry_date']
                ];
            }
            // Valid license
            else {
                $verificationStatus = 'valid';
                $responseData = [
                    'valid' => true,
                    'message' => 'License is valid',
                    'licenseInfo' => [
                        'planType' => ucfirst($license['plan_type']),
                        'status' => $license['status'],
                        'purchaseDate' => $license['purchase_date'],
                        'expiryDate' => $license['expiry_date'],
                        'customerName' => $license['full_name']
                    ]
                ];
            }
        }
        
        // Log verification attempt
        $logStmt = $db->prepare("
            INSERT INTO license_verifications (license_key, ip_address, user_agent, status) 
            VALUES (?, ?, ?, ?)
        ");
        $logStmt->execute([$licenseKey, $clientIP, $userAgent, $verificationStatus]);
        
        sendJSONResponse($responseData);
        
    } catch (Exception $e) {
        error_log("License verification error: " . $e->getMessage());
        sendJSONResponse([
            'valid' => false,
            'message' => 'Verification service temporarily unavailable'
        ], 500);
    }
}

/**
 * Get license information
 */
function getLicenseInfo($db, $licenseKey) {
    if (!LicenseGenerator::validateLicenseKeyFormat($licenseKey)) {
        sendJSONResponse(['error' => 'Invalid license key format'], 400);
    }
    
    try {
        $stmt = $db->prepare("SELECT * FROM licenses WHERE license_key = ?");
        $stmt->execute([$licenseKey]);
        $license = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$license) {
            sendJSONResponse(['error' => 'License not found'], 404);
        }
        
        // Remove sensitive information
        unset($license['stripe_payment_intent_id'], $license['stripe_customer_id']);
        
        sendJSONResponse([
            'license' => $license
        ]);
        
    } catch (Exception $e) {
        error_log("Get license info error: " . $e->getMessage());
        sendJSONResponse(['error' => 'Service temporarily unavailable'], 500);
    }
}

/**
 * Get license verification history
 */
function getLicenseVerifications($db, $licenseKey) {
    if (!LicenseGenerator::validateLicenseKeyFormat($licenseKey)) {
        sendJSONResponse(['error' => 'Invalid license key format'], 400);
    }
    
    try {
        $stmt = $db->prepare("
            SELECT ip_address, verification_date, status 
            FROM license_verifications 
            WHERE license_key = ? 
            ORDER BY verification_date DESC 
            LIMIT 50
        ");
        $stmt->execute([$licenseKey]);
        $verifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        sendJSONResponse([
            'licenseKey' => $licenseKey,
            'verifications' => $verifications
        ]);
        
    } catch (Exception $e) {
        error_log("Get license verifications error: " . $e->getMessage());
        sendJSONResponse(['error' => 'Service temporarily unavailable'], 500);
    }
}
?>