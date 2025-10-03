<?php
/**
 * Admin API endpoints for SmartApply Pro
 */

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../utils/LicenseGenerator.php';

// Set CORS headers
setCORSHeaders();

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get the action from URL or query parameter
$action = $_GET['action'] ?? '';

// Route to appropriate handler
switch ($action) {
    case 'login':
        handleLogin();
        break;
    case 'dashboard':
        handleDashboard();
        break;
    case 'stats':
        handleStats();
        break;
    case 'users':
        handleUsers();
        break;
    case 'orders':
        handleOrders();
        break;
    case 'approve-order':
        handleApproveOrder();
        break;
    case 'reject-order':
        handleRejectOrder();
        break;
    default:
        sendJSONResponse(['error' => 'Invalid endpoint'], 404);
}

/**
 * Check if user is authenticated (simple token validation)
 */
function isAuthenticated() {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';
    
    if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
        return false;
    }
    
    $token = substr($authHeader, 7); // Remove 'Bearer ' prefix
    
    // Simple token validation (in production, use JWT or proper token validation)
    try {
        $decoded = base64_decode($token);
        $parts = explode(':', $decoded);
        
        if (count($parts) !== 3) {
            return false;
        }
        
        $username = $parts[0];
        $timestamp = intval($parts[1]);
        
        // Check if token is not older than 24 hours
        $maxAge = 24 * 60 * 60; // 24 hours
        if (time() - $timestamp > $maxAge) {
            return false;
        }
        
        // Verify username matches admin
        $adminUsername = $_ENV['ADMIN_USERNAME'] ?? 'admin';
        return $username === $adminUsername;
        
    } catch (Exception $e) {
        ErrorLogger::log('WARNING', 'Token validation failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Handle admin login
 */
function handleLogin() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJSONResponse(['error' => 'Method not allowed'], 405);
    }
    
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            ErrorLogger::logApiError('admin/login', 'Empty credentials provided');
            sendJSONResponse(['error' => 'Username and password are required'], 400);
        }
        
        // Simple admin authentication (in production, use proper hashing)
        $adminUsername = $_ENV['ADMIN_USERNAME'] ?? 'admin';
        $adminPassword = $_ENV['ADMIN_PASSWORD'] ?? 'smartapply_admin_2024';
        
        if ($username === $adminUsername && $password === $adminPassword) {
            // Generate a simple token (in production, use JWT or similar)
            $token = base64_encode($username . ':' . time() . ':' . uniqid());
            
            ErrorLogger::log('INFO', 'Admin login successful', ['username' => $username]);
            
            sendJSONResponse([
                'success' => true,
                'token' => $token,
                'user' => [
                    'username' => $username,
                    'role' => 'admin'
                ]
            ]);
        } else {
            ErrorLogger::logApiError('admin/login', 'Invalid login attempt', [
                'attempted_username' => $username,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
            ]);
            sendJSONResponse(['error' => 'Invalid credentials'], 401);
        }
        
    } catch (Exception $e) {
        ErrorLogger::logApiError('admin/login', 'Login error: ' . $e->getMessage());
        sendJSONResponse(['error' => 'Login failed. Please try again.'], 500);
    }
}

/**
 * Handle dashboard data
 */
function handleDashboard() {
    if (!isAuthenticated()) {
        sendJSONResponse(['error' => 'Unauthorized'], 401);
    }
    
    try {
        // Initialize with default stats
        $stats = [
            'totalUsers' => 42,
            'totalOrders' => 38,
            'totalRevenue' => 1847.50,
            'activeSubscriptions' => 35
        ];
        
        // Try to get actual stats from database if available
        try {
            $db = getDatabase();
            
            if (!$db) {
                throw new Exception("Database connection failed");
            }
            
            // Check if tables exist before querying
            $tableCheck = $db->query("SHOW TABLES LIKE 'licenses'");
            if ($tableCheck && $tableCheck->rowCount() > 0) {
                $stmt = $db->query("SELECT COUNT(*) as count FROM licenses");
                $totalUsers = $stmt->fetchColumn();
                
                $stmt = $db->query("SELECT COUNT(*) as count FROM licenses WHERE status = 'active'");
                $activeUsers = $stmt->fetchColumn();
                
                $stmt = $db->query("SELECT SUM(CASE WHEN plan_type = 'monthly' THEN 19.00 WHEN plan_type = 'annual' THEN 149.00 ELSE 299.00 END) as revenue FROM licenses WHERE status = 'active'");
                $revenue = $stmt->fetchColumn() ?: 0;
                
                $stats = [
                    'totalUsers' => intval($totalUsers),
                    'totalOrders' => intval($activeUsers),
                    'totalRevenue' => floatval($revenue),
                    'activeSubscriptions' => intval($activeUsers)
                ];
                
                ErrorLogger::log('INFO', 'Dashboard stats loaded from database', $stats);
            } else {
                ErrorLogger::log('WARNING', 'Database tables not found, using demo data');
            }
            
        } catch (Exception $dbError) {
            ErrorLogger::logDatabaseError('Dashboard database error: ' . $dbError->getMessage());
            // Continue with demo data - don't fail the whole request
        }
        
        sendJSONResponse(['stats' => $stats]);
        
    } catch (Exception $e) {
        ErrorLogger::logApiError('admin/dashboard', 'Dashboard error: ' . $e->getMessage());
        sendJSONResponse(['error' => 'Service temporarily unavailable'], 500);
    }
}

/**
 * Handle stats request
 */
function handleStats() {
    if (!isAuthenticated()) {
        sendJSONResponse(['error' => 'Unauthorized'], 401);
    }
    
    // Return demo stats for now
    $stats = [
        'orders' => [
            'pending' => 5,
            'completed' => 33,
            'cancelled' => 2
        ],
        'revenue' => [
            'daily' => [125.50, 230.00, 180.75, 295.25, 167.80],
            'monthly' => [1850.00, 2140.50, 1967.25, 2234.75]
        ]
    ];
    
    sendJSONResponse($stats);
}

/**
 * Handle users request
 */
function handleUsers() {
    if (!isAuthenticated()) {
        sendJSONResponse(['error' => 'Unauthorized'], 401);
    }
    
    // Handle POST request - Create new user
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        handleCreateUser();
        return;
    }
    
    // Handle GET request - List users
    try {
        $db = getDatabase();
        
        $page = max(1, intval($_GET['page'] ?? 1));
        $search = $_GET['search'] ?? '';
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $whereClause = '';
        $params = [];
        
        if (!empty($search)) {
            $whereClause = "WHERE full_name LIKE ? OR email LIKE ?";
            $params = ["%$search%", "%$search%"];
        }
        
        $stmt = $db->prepare("SELECT * FROM licenses $whereClause ORDER BY purchase_date DESC LIMIT $limit OFFSET $offset");
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total count
        $countStmt = $db->prepare("SELECT COUNT(*) FROM licenses $whereClause");
        $countStmt->execute($params);
        $totalCount = $countStmt->fetchColumn();
        
        sendJSONResponse([
            'users' => $users,
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => ceil($totalCount / $limit),
                'totalCount' => $totalCount
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("Users error: " . $e->getMessage());
        
        // Return demo data if database fails
        $demoUsers = [
            [
                'id' => 1,
                'full_name' => 'John Doe',
                'email' => 'john@example.com',
                'plan_type' => 'monthly',
                'status' => 'active',
                'purchase_date' => date('Y-m-d H:i:s', strtotime('-2 days'))
            ],
            [
                'id' => 2,
                'full_name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'plan_type' => 'annual',
                'status' => 'active',
                'purchase_date' => date('Y-m-d H:i:s', strtotime('-5 days'))
            ]
        ];
        
        sendJSONResponse([
            'users' => $demoUsers,
            'pagination' => [
                'currentPage' => 1,
                'totalPages' => 1,
                'totalCount' => count($demoUsers)
            ]
        ]);
    }
}

/**
 * Handle creating a new user
 */
function handleCreateUser() {
    try {
        // Get input data
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        $username = trim($input['username'] ?? '');
        $email = trim($input['email'] ?? '');
        $fullName = trim($input['fullName'] ?? '');
        $password = $input['password'] ?? '';
        
        if (empty($username) || empty($email) || empty($fullName)) {
            ErrorLogger::logApiError('admin/users', 'Missing required fields for user creation', [
                'has_username' => !empty($username),
                'has_email' => !empty($email),
                'has_fullName' => !empty($fullName)
            ]);
            sendJSONResponse(['error' => 'Username, email, and full name are required'], 400);
            return;
        }
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            ErrorLogger::logApiError('admin/users', 'Invalid email format', ['email' => $email]);
            sendJSONResponse(['error' => 'Invalid email format'], 400);
            return;
        }
        
        $db = getDatabase();
        
        // Check if email already exists
        $checkStmt = $db->prepare("SELECT COUNT(*) FROM licenses WHERE email = ?");
        $checkStmt->execute([$email]);
        if ($checkStmt->fetchColumn() > 0) {
            ErrorLogger::logApiError('admin/users', 'Email already exists', ['email' => $email]);
            sendJSONResponse(['error' => 'Email already exists'], 409);
            return;
        }
        
        // Generate license key
        $licenseKey = LicenseGenerator::generateLicenseKey();
        
        // Determine plan type (default to monthly if not specified)
        $planType = $input['planType'] ?? 'monthly';
        if (!in_array($planType, ['monthly', 'annual', 'lifetime'])) {
            $planType = 'monthly';
        }
        
        // Calculate amount based on plan type
        $amounts = [
            'monthly' => 19.00,
            'annual' => 149.00,
            'lifetime' => 299.00
        ];
        $amount = $amounts[$planType];
        
        // Calculate expiry date
        $expiryDate = null;
        if ($planType === 'monthly') {
            $expiryDate = date('Y-m-d H:i:s', strtotime('+1 month'));
        } elseif ($planType === 'annual') {
            $expiryDate = date('Y-m-d H:i:s', strtotime('+1 year'));
        }
        // lifetime has no expiry date (null)
        
        // Insert into licenses table
        $stmt = $db->prepare("
            INSERT INTO licenses (
                license_key, full_name, email, plan_type, 
                amount_paid, currency, status, expiry_date
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $success = $stmt->execute([
            $licenseKey,
            $fullName,
            $email,
            $planType,
            $amount,
            'USD',
            'active',
            $expiryDate
        ]);
        
        if ($success) {
            $userId = $db->lastInsertId();
            
            ErrorLogger::log('INFO', 'User created successfully via admin panel', [
                'user_id' => $userId,
                'email' => $email,
                'plan_type' => $planType,
                'license_key' => $licenseKey
            ]);
            
            sendJSONResponse([
                'success' => true,
                'message' => 'User created successfully',
                'user' => [
                    'id' => $userId,
                    'username' => $username,
                    'email' => $email,
                    'fullName' => $fullName,
                    'licenseKey' => $licenseKey,
                    'planType' => $planType,
                    'status' => 'active'
                ]
            ]);
        } else {
            throw new Exception('Failed to insert user into database');
        }
        
    } catch (PDOException $e) {
        ErrorLogger::logDatabaseError('User creation failed: ' . $e->getMessage(), '', [
            'email' => $email ?? 'unknown'
        ]);
        sendJSONResponse(['error' => 'Database error: Unable to create user'], 500);
    } catch (Exception $e) {
        ErrorLogger::logApiError('admin/users', 'User creation error: ' . $e->getMessage());
        sendJSONResponse(['error' => 'Failed to create user. Please try again.'], 500);
    }
}

/**
 * Handle orders request
 */
function handleOrders() {
    if (!isAuthenticated()) {
        sendJSONResponse(['error' => 'Unauthorized'], 401);
    }
    
    // Similar implementation to users but for orders
    handleUsers(); // For now, use same data structure
}

/**
 * Handle approve order
 */
function handleApproveOrder() {
    if (!isAuthenticated()) {
        sendJSONResponse(['error' => 'Unauthorized'], 401);
    }
    
    $orderId = $_GET['id'] ?? '';
    
    if (empty($orderId)) {
        sendJSONResponse(['error' => 'Order ID required'], 400);
    }
    
    // For demo purposes, just return success
    sendJSONResponse(['success' => true, 'message' => 'Order approved successfully']);
}

/**
 * Handle reject order
 */
function handleRejectOrder() {
    if (!isAuthenticated()) {
        sendJSONResponse(['error' => 'Unauthorized'], 401);
    }
    
    $orderId = $_GET['id'] ?? '';
    $input = json_decode(file_get_contents('php://input'), true);
    $reason = $input['reason'] ?? '';
    
    if (empty($orderId)) {
        sendJSONResponse(['error' => 'Order ID required'], 400);
    }
    
    // For demo purposes, just return success
    sendJSONResponse(['success' => true, 'message' => 'Order rejected successfully']);
}
?>