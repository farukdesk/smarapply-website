<?php
/**
 * Contact form API endpoint
 */

require_once '../config/config.php';
require_once '../services/EmailService.php';

// Set CORS headers
setCORSHeaders();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSONResponse(['error' => 'Method not allowed'], 405);
}

// Get input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
$required = ['name', 'email', 'subject', 'message'];
foreach ($required as $field) {
    if (empty($input[$field])) {
        sendJSONResponse(['error' => "Field '$field' is required"], 400);
    }
}

// Validate email
if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    sendJSONResponse(['error' => 'Invalid email address'], 400);
}

try {
    $emailService = new EmailService();
    
    // Send email to support
    $to = 'support@smartapplypro.com';
    $subject = 'Contact Form: ' . $input['subject'];
    
    $message = "
    <html>
    <body style='font-family: Arial, sans-serif;'>
        <h2>New Contact Form Submission</h2>
        <p><strong>From:</strong> {$input['name']} ({$input['email']})</p>
        <p><strong>Subject:</strong> {$input['subject']}</p>
        <p><strong>Message:</strong></p>
        <p>{$input['message']}</p>
    </body>
    </html>
    ";
    
    // For now, just return success (email service would need to be configured)
    sendJSONResponse([
        'success' => true,
        'message' => 'Thank you for your message. We will get back to you soon.'
    ]);
    
} catch (Exception $e) {
    error_log("Contact form error: " . $e->getMessage());
    sendJSONResponse(['error' => 'Failed to send message'], 500);
}
?>
