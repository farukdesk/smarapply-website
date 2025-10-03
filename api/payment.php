<?php
/**
 * Payment API endpoints for SmartApply Pro
 */

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../utils/LicenseGenerator.php';
require_once '../services/EmailService.php';

// Set CORS headers
setCORSHeaders();

// Stripe PHP SDK would be loaded here
// require_once '../vendor/autoload.php';

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'] ?? '';

// Parse the path
$pathParts = explode('/', trim($path, '/'));
$action = $pathParts[0] ?? '';

// Initialize database connection with error handling
try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        sendJSONResponse(['error' => 'Database connection failed'], 500);
    }
} catch (Exception $e) {
    ErrorLogger::logDatabaseError('Payment API database connection failed: ' . $e->getMessage());
    sendJSONResponse(['error' => 'Database unavailable'], 500);
}

// Route the request
switch ($method) {
    case 'POST':
        switch ($action) {
            case 'create-intent':
                createPaymentIntent($db);
                break;
            case 'confirm-intent':
                confirmPayment($db);
                break;
            case 'bkash-order':
                createBkashOrder($db);
                break;
            case 'nagad-order':
                createNagadOrder($db);
                break;
            case 'trial-signup':
                createTrialSignup($db);
                break;
            default:
                sendJSONResponse(['error' => 'Invalid endpoint'], 404);
        }
        break;
        
    case 'GET':
        if ($action === 'status' && !empty($pathParts[1])) {
            getPaymentStatus($db, $pathParts[1]);
        } elseif ($action === 'order-status' && !empty($pathParts[1])) {
            getOrderStatus($db, $pathParts[1]);
        } else {
            sendJSONResponse(['error' => 'Invalid endpoint'], 404);
        }
        break;
        
    default:
        sendJSONResponse(['error' => 'Method not allowed'], 405);
}

/**
 * Create payment intent
 */
function createPaymentIntent($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate input
    $required = ['customerName', 'customerEmail', 'planType'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            sendJSONResponse(['error' => "Field '$field' is required"], 400);
        }
    }
    
    $customerName = trim($input['customerName']);
    $customerEmail = trim($input['customerEmail']);
    $planType = $input['planType'];
    
    // Validate plan type
    $validPlans = ['monthly', 'annual', 'lifetime'];
    if (!in_array($planType, $validPlans)) {
        sendJSONResponse(['error' => 'Invalid plan type'], 400);
    }
    
    // Define pricing
    $pricing = [
        'monthly' => ['amount' => 1900, 'currency' => 'USD'], // $19.00
        'annual' => ['amount' => 14900, 'currency' => 'USD'], // $149.00
        'lifetime' => ['amount' => 29900, 'currency' => 'USD'] // $299.00
    ];
    
    $amount = $pricing[$planType]['amount'];
    $currency = $pricing[$planType]['currency'];
    
    try {
        // Generate order number
        $orderNumber = 'ORDER-' . strtoupper(uniqid());
        
        // For demo purposes, we'll simulate Stripe integration
        // In production, you would create actual Stripe payment intent here
        $paymentIntentId = 'pi_demo_' . uniqid();
        
        // Create order in database
        $stmt = $db->prepare("
            INSERT INTO orders (
                order_number, customer_email, customer_name, plan_type, 
                amount, currency, payment_status, stripe_payment_intent_id
            ) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)
        ");
        
        $stmt->execute([
            $orderNumber,
            $customerEmail,
            $customerName,
            $planType,
            $amount / 100, // Convert from cents
            $currency,
            $paymentIntentId
        ]);
        
        sendJSONResponse([
            'success' => true,
            'paymentIntentId' => $paymentIntentId,
            'clientSecret' => $paymentIntentId . '_secret_demo',
            'orderNumber' => $orderNumber,
            'amount' => $amount,
            'currency' => $currency
        ]);
        
    } catch (Exception $e) {
        error_log("Create payment intent error: " . $e->getMessage());
        sendJSONResponse(['error' => 'Payment processing temporarily unavailable'], 500);
    }
}

/**
 * Confirm payment and create license
 */
function confirmPayment($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['paymentIntentId'])) {
        sendJSONResponse(['error' => 'Payment intent ID is required'], 400);
    }
    
    $paymentIntentId = $input['paymentIntentId'];
    
    try {
        // Get order by payment intent ID
        $stmt = $db->prepare("SELECT * FROM orders WHERE stripe_payment_intent_id = ?");
        $stmt->execute([$paymentIntentId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            sendJSONResponse(['error' => 'Order not found'], 404);
        }
        
        if ($order['payment_status'] === 'succeeded') {
            sendJSONResponse(['error' => 'Payment already processed'], 400);
        }
        
        // For demo purposes, simulate successful payment
        // In production, you would verify with Stripe here
        
        // Generate license key
        $licenseKey = LicenseGenerator::generateLicenseKey();
        
        // Calculate expiry date
        $expiryDate = null;
        if ($order['plan_type'] === 'monthly') {
            $expiryDate = date('Y-m-d H:i:s', strtotime('+30 days'));
        } elseif ($order['plan_type'] === 'annual') {
            $expiryDate = date('Y-m-d H:i:s', strtotime('+365 days'));
        }
        // Lifetime has no expiry date
        
        $db->beginTransaction();
        
        try {
            // Create license
            $licenseStmt = $db->prepare("
                INSERT INTO licenses (
                    license_key, full_name, email, plan_type, amount_paid, 
                    currency, expiry_date, stripe_payment_intent_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $licenseStmt->execute([
                $licenseKey,
                $order['customer_name'],
                $order['customer_email'],
                $order['plan_type'],
                $order['amount'],
                $order['currency'],
                $expiryDate,
                $paymentIntentId
            ]);
            
            // Update order
            $orderStmt = $db->prepare("
                UPDATE orders 
                SET payment_status = 'succeeded', order_status = 'completed', license_key = ?
                WHERE stripe_payment_intent_id = ?
            ");
            $orderStmt->execute([$licenseKey, $paymentIntentId]);
            
            $db->commit();
            
            // Send license key via email
            try {
                $emailService = new EmailService();
                $emailService->sendLicenseKey($order['customer_email'], $order['customer_name'], $licenseKey, $order['plan_type']);
            } catch (Exception $e) {
                error_log("Email send error: " . $e->getMessage());
            }
            
            sendJSONResponse([
                'success' => true,
                'licenseKey' => $licenseKey,
                'orderNumber' => $order['order_number'],
                'message' => 'Payment successful! License key has been sent to your email.'
            ]);
            
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
        
    } catch (Exception $e) {
        error_log("Confirm payment error: " . $e->getMessage());
        sendJSONResponse(['error' => 'Payment confirmation failed'], 500);
    }
}

/**
 * Get payment status
 */
function getPaymentStatus($db, $paymentIntentId) {
    try {
        $stmt = $db->prepare("SELECT * FROM orders WHERE stripe_payment_intent_id = ?");
        $stmt->execute([$paymentIntentId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            sendJSONResponse(['error' => 'Payment not found'], 404);
        }
        
        sendJSONResponse([
            'paymentIntentId' => $paymentIntentId,
            'paymentStatus' => $order['payment_status'],
            'orderStatus' => $order['order_status'],
            'orderNumber' => $order['order_number']
        ]);
        
    } catch (Exception $e) {
        error_log("Get payment status error: " . $e->getMessage());
        sendJSONResponse(['error' => 'Service temporarily unavailable'], 500);
    }
}

/**
 * Get order status
 */
function getOrderStatus($db, $orderNumber) {
    try {
        $stmt = $db->prepare("SELECT * FROM orders WHERE order_number = ?");
        $stmt->execute([$orderNumber]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            sendJSONResponse(['error' => 'Order not found'], 404);
        }
        
        sendJSONResponse([
            'orderNumber' => $orderNumber,
            'orderStatus' => $order['order_status'],
            'paymentStatus' => $order['payment_status'],
            'planType' => $order['plan_type'],
            'amount' => $order['amount'],
            'currency' => $order['currency'],
            'createdAt' => $order['created_at']
        ]);
        
    } catch (Exception $e) {
        error_log("Get order status error: " . $e->getMessage());
        sendJSONResponse(['error' => 'Service temporarily unavailable'], 500);
    }
}

/**
 * Create bKash order (manual payment processing)
 */
function createBkashOrder($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate input
    if (empty($input['customer']['name']) || empty($input['customer']['email']) || 
        empty($input['planType']) || empty($input['bkash']['mobileNumber']) || 
        empty($input['bkash']['transactionId'])) {
        sendJSONResponse(['error' => 'Missing required fields'], 400);
    }
    
    try {
        $orderNumber = 'ORD-' . strtoupper(uniqid());
        $licenseKey = LicenseGenerator::generateLicenseKey();
        
        // Insert order into database
        $stmt = $db->prepare("
            INSERT INTO orders (order_number, customer_name, customer_email, plan_type, amount, currency, 
                              payment_status, order_status, license_key, created_at)
            VALUES (?, ?, ?, ?, ?, 'BDT', 'pending', 'pending', ?, NOW())
        ");
        
        $stmt->execute([
            $orderNumber,
            $input['customer']['name'],
            $input['customer']['email'],
            $input['planType'],
            $input['amount'],
            $licenseKey
        ]);
        
        // Send order confirmation email
        try {
            $emailService = new EmailService();
            $emailService->sendOrderConfirmation(
                $input['customer']['email'],
                $input['customer']['name'],
                $orderNumber,
                $input['planType'],
                $input['amount'],
                'BDT',
                'bKash'
            );
        } catch (Exception $e) {
            error_log("Email send error: " . $e->getMessage());
            // Don't fail the order if email fails
        }
        
        sendJSONResponse([
            'success' => true,
            'orderNumber' => $orderNumber,
            'message' => 'Order submitted successfully. Your order will be reviewed within 24-48 hours.',
            'licenseKey' => $licenseKey
        ]);
        
    } catch (Exception $e) {
        error_log("bKash order error: " . $e->getMessage());
        sendJSONResponse(['error' => 'Failed to create order'], 500);
    }
}

/**
 * Create Nagad order (manual payment processing)
 */
function createNagadOrder($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate input
    if (empty($input['customer']['name']) || empty($input['customer']['email']) || 
        empty($input['planType']) || empty($input['nagad']['mobileNumber']) || 
        empty($input['nagad']['transactionId'])) {
        sendJSONResponse(['error' => 'Missing required fields'], 400);
    }
    
    try {
        $orderNumber = 'ORD-' . strtoupper(uniqid());
        $licenseKey = LicenseGenerator::generateLicenseKey();
        
        // Insert order into database
        $stmt = $db->prepare("
            INSERT INTO orders (order_number, customer_name, customer_email, plan_type, amount, currency, 
                              payment_status, order_status, license_key, created_at)
            VALUES (?, ?, ?, ?, ?, 'BDT', 'pending', 'pending', ?, NOW())
        ");
        
        $stmt->execute([
            $orderNumber,
            $input['customer']['name'],
            $input['customer']['email'],
            $input['planType'],
            $input['amount'],
            $licenseKey
        ]);
        
        // Send order confirmation email
        try {
            $emailService = new EmailService();
            $emailService->sendOrderConfirmation(
                $input['customer']['email'],
                $input['customer']['name'],
                $orderNumber,
                $input['planType'],
                $input['amount'],
                'BDT',
                'Nagad'
            );
        } catch (Exception $e) {
            error_log("Email send error: " . $e->getMessage());
            // Don't fail the order if email fails
        }
        
        sendJSONResponse([
            'success' => true,
            'orderNumber' => $orderNumber,
            'message' => 'Order submitted successfully. Your order will be reviewed within 24-48 hours.',
            'licenseKey' => $licenseKey
        ]);
        
    } catch (Exception $e) {
        error_log("Nagad order error: " . $e->getMessage());
        sendJSONResponse(['error' => 'Failed to create order'], 500);
    }
}

/**
 * Create trial signup (free plan with account creation)
 */
function createTrialSignup($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate input
    if (empty($input['customer']['name']) || empty($input['customer']['email'])) {
        sendJSONResponse(['error' => 'Name and email are required'], 400);
    }
    
    $customerName = trim($input['customer']['name']);
    $customerEmail = trim($input['customer']['email']);
    
    // Validate email format
    if (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
        sendJSONResponse(['error' => 'Invalid email format'], 400);
    }
    
    try {
        // Check if email already exists
        $checkStmt = $db->prepare("SELECT COUNT(*) FROM licenses WHERE email = ?");
        $checkStmt->execute([$customerEmail]);
        if ($checkStmt->fetchColumn() > 0) {
            sendJSONResponse(['error' => 'An account with this email already exists'], 409);
        }
        
        // Generate license key
        $licenseKey = LicenseGenerator::generateLicenseKey();
        
        // Generate username from email (part before @)
        $username = strtolower(explode('@', $customerEmail)[0]);
        // Add random suffix if username is too common
        $username = $username . rand(100, 999);
        
        // Generate random password
        $password = bin2hex(random_bytes(4)); // 8 character password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Create order number
        $orderNumber = 'TRIAL-' . strtoupper(uniqid());
        
        $db->beginTransaction();
        
        try {
            // Create license for trial (no expiry date for lifetime free trial)
            $licenseStmt = $db->prepare("
                INSERT INTO licenses (
                    license_key, full_name, email, plan_type, amount_paid, 
                    currency, status, expiry_date
                ) VALUES (?, ?, ?, 'trial', 0, 'USD', 'active', NULL)
            ");
            
            $licenseStmt->execute([
                $licenseKey,
                $customerName,
                $customerEmail
            ]);
            
            // Create order record
            $orderStmt = $db->prepare("
                INSERT INTO orders (
                    order_number, customer_name, customer_email, plan_type, 
                    amount, currency, payment_status, order_status, license_key, created_at
                ) VALUES (?, ?, ?, 'trial', 0, 'USD', 'succeeded', 'completed', ?, NOW())
            ");
            
            $orderStmt->execute([
                $orderNumber,
                $customerName,
                $customerEmail,
                $licenseKey
            ]);
            
            $db->commit();
            
            // Send trial confirmation email with credentials
            try {
                $emailService = new EmailService();
                $emailService->sendTrialConfirmation(
                    $customerEmail,
                    $customerName,
                    $licenseKey,
                    $username,
                    $password
                );
            } catch (Exception $e) {
                error_log("Email send error: " . $e->getMessage());
                // Don't fail the signup if email fails
            }
            
            sendJSONResponse([
                'success' => true,
                'orderNumber' => $orderNumber,
                'licenseKey' => $licenseKey,
                'username' => $username,
                'message' => 'Trial account created successfully! Check your email for login credentials.'
            ]);
            
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
        
    } catch (PDOException $e) {
        error_log("Trial signup database error: " . $e->getMessage());
        sendJSONResponse(['error' => 'Failed to create trial account. Please try again.'], 500);
    } catch (Exception $e) {
        error_log("Trial signup error: " . $e->getMessage());
        sendJSONResponse(['error' => 'Failed to create trial account. Please try again.'], 500);
    }
}
?>