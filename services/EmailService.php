<?php
/**
 * Email service for SmartApply Pro
 */

class EmailService {
    private $smtpHost;
    private $smtpPort;
    private $smtpUsername;
    private $smtpPassword;
    private $fromEmail;
    
    public function __construct() {
        $this->smtpHost = EMAIL_SMTP_HOST;
        $this->smtpPort = EMAIL_SMTP_PORT;
        $this->smtpUsername = EMAIL_SMTP_USERNAME;
        $this->smtpPassword = EMAIL_SMTP_PASSWORD;
        $this->fromEmail = EMAIL_FROM;
    }
    
    /**
     * Send license key to customer
     */
    public function sendLicenseKey($to, $customerName, $licenseKey, $planType) {
        $subject = 'Your SmartApply License Key – Welcome to Smart Job Matching!';
        
        $planDisplay = ucfirst($planType);
        $websiteUrl = 'https://smartapplypro.com';
        
        $message = $this->getLicenseKeyEmailTemplate($customerName, $licenseKey, $planDisplay, $websiteUrl);
        
        return $this->sendEmail($to, $subject, $message);
    }
    
    /**
     * Send renewal reminder
     */
    public function sendRenewalReminder($to, $customerName, $licenseKey, $expiryDate) {
        $subject = 'SmartApply Pro License Renewal Reminder';
        
        $message = $this->getRenewalReminderTemplate($customerName, $licenseKey, $expiryDate);
        
        return $this->sendEmail($to, $subject, $message);
    }
    
    /**
     * Send trial confirmation with account credentials
     */
    public function sendTrialConfirmation($to, $customerName, $licenseKey, $username, $password) {
        $subject = 'Welcome to SmartApply - Your Trial Account is Ready!';
        
        $websiteUrl = 'https://smartapplypro.com';
        
        $message = $this->getTrialConfirmationTemplate($customerName, $licenseKey, $username, $password, $websiteUrl);
        
        return $this->sendEmail($to, $subject, $message);
    }
    
    /**
     * Send order confirmation email for pending orders (bKash/Nagad)
     */
    public function sendOrderConfirmation($to, $customerName, $orderNumber, $planType, $amount, $currency, $paymentMethod) {
        $subject = 'Order Confirmation - SmartApply Pro';
        
        $message = $this->getOrderConfirmationTemplate($customerName, $orderNumber, $planType, $amount, $currency, $paymentMethod);
        
        return $this->sendEmail($to, $subject, $message);
    }
    
    /**
     * Send license key email template
     */
    private function getLicenseKeyEmailTemplate($customerName, $licenseKey, $planType, $websiteUrl) {
        return "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Your SmartApply License Key</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #3b82f6; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .license-key { background: #fff; border: 2px solid #3b82f6; padding: 15px; margin: 20px 0; text-align: center; font-size: 18px; font-weight: bold; font-family: monospace; }
        .button { display: inline-block; background: #3b82f6; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; margin: 10px 0; }
        .footer { padding: 20px; text-align: center; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🎉 Welcome to SmartApply!</h1>
        </div>
        
        <div class='content'>
            <p>Hi {$customerName},</p>
            
            <p>Thank you for purchasing SmartApply {$planType} plan! Your payment has been processed successfully.</p>
            
            <p><strong>Your License Key:</strong></p>
            <div class='license-key'>{$licenseKey}</div>
            
            <p><strong>How to activate your license:</strong></p>
            <ol>
                <li>Open the SmartApply Chrome extension</li>
                <li>Go to the Settings or Premium tab</li>
                <li>Enter your license key in the provided field</li>
                <li>Click 'Verify' to activate your premium features</li>
            </ol>
            
            <p><strong>What's included in your {$planType} plan:</strong></p>
            <ul>
                <li>✅ Advanced Cover Letter Analysis</li>
                <li>✅ Response Rate Optimization</li>
                <li>✅ Industry-Specific Recommendations</li>
                <li>✅ Priority Support</li>
                <li>✅ Regular Updates & New Features</li>
            </ul>
            
            <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
            
            <a href='{$websiteUrl}' class='button'>Visit SmartApply Pro</a>
        </div>
        
        <div class='footer'>
            <p>This email was sent to {$to} regarding your SmartApply Pro purchase.</p>
            <p>© 2024 SmartApply Pro. All rights reserved.</p>
        </div>
    </div>
</body>
</html>";
    }
    
    /**
     * Send renewal reminder email template
     */
    private function getRenewalReminderTemplate($customerName, $licenseKey, $expiryDate) {
        $websiteUrl = 'https://smartapplypro.com';
        
        return "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>SmartApply Pro License Renewal Reminder</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #ff9800; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .button { display: inline-block; background: #ff9800; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; margin: 10px 0; }
        .footer { padding: 20px; text-align: center; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>⏰ License Renewal Reminder</h1>
        </div>
        
        <div class='content'>
            <p>Hi {$customerName},</p>
            
            <p>Your SmartApply Pro license is expiring soon!</p>
            
            <p><strong>License Key:</strong> {$licenseKey}</p>
            <p><strong>Expiry Date:</strong> {$expiryDate}</p>
            
            <p>To continue enjoying premium features, please renew your license before the expiry date.</p>
            
            <a href='{$websiteUrl}' class='button'>Renew License</a>
        </div>
        
        <div class='footer'>
            <p>© 2024 SmartApply Pro. All rights reserved.</p>
        </div>
    </div>
</body>
</html>";
    }
    
    /**
     * Send trial confirmation email template with account credentials
     */
    private function getTrialConfirmationTemplate($customerName, $licenseKey, $username, $password, $websiteUrl) {
        return "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Welcome to SmartApply - Your Trial Account</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #10b981; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .credentials-box { background: #fff; border: 2px solid #10b981; padding: 15px; margin: 20px 0; border-radius: 8px; }
        .license-key { background: #fff; border: 2px solid #3b82f6; padding: 15px; margin: 20px 0; text-align: center; font-size: 16px; font-weight: bold; font-family: monospace; border-radius: 8px; }
        .credential-item { margin: 10px 0; padding: 10px; background: #f0fdf4; border-radius: 4px; }
        .credential-label { font-weight: bold; color: #059669; }
        .credential-value { font-family: monospace; font-size: 16px; color: #333; }
        .button { display: inline-block; background: #10b981; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; margin: 10px 0; }
        .footer { padding: 20px; text-align: center; color: #666; font-size: 12px; }
        .warning { background: #fef3c7; padding: 15px; border-left: 4px solid #f59e0b; margin: 20px 0; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🎉 Welcome to SmartApply!</h1>
        </div>
        
        <div class='content'>
            <p>Hi {$customerName},</p>
            
            <p>Congratulations! Your free trial account has been created successfully. You now have lifetime access to SmartApply trial features!</p>
            
            <div class='credentials-box'>
                <h3 style='margin-top: 0; color: #059669;'>🔑 Your Account Credentials</h3>
                <div class='credential-item'>
                    <span class='credential-label'>Username:</span><br>
                    <span class='credential-value'>{$username}</span>
                </div>
                <div class='credential-item'>
                    <span class='credential-label'>Password:</span><br>
                    <span class='credential-value'>{$password}</span>
                </div>
            </div>
            
            <div class='warning'>
                <strong>⚠️ Important:</strong> Please save these credentials in a safe place. You'll need them to log in to your account.
            </div>
            
            <p><strong>Your License Key:</strong></p>
            <div class='license-key'>{$licenseKey}</div>
            
            <p><strong>How to activate your license:</strong></p>
            <ol>
                <li>Open the SmartApply Chrome extension</li>
                <li>Go to the Settings or Premium tab</li>
                <li>Enter your license key in the provided field</li>
                <li>Click 'Verify' to activate your trial features</li>
            </ol>
            
            <p><strong>What's included in your Trial plan:</strong></p>
            <ul>
                <li>✅ Lifetime Job Analysis</li>
                <li>✅ 10 Cover Letter Analyses per month</li>
                <li>✅ Profile Compatibility Scoring</li>
                <li>✅ Multi-Profile Support</li>
                <li>⚠️ Upgrade to Premium for unlimited cover letter analysis</li>
            </ul>
            
            <p>Ready to get started?</p>
            
            <a href='{$websiteUrl}' class='button'>Visit SmartApply Pro</a>
            
            <p>If you have any questions or need assistance, please don't hesitate to contact our support team at <strong>support@smartapplypro.com</strong></p>
        </div>
        
        <div class='footer'>
            <p>© 2024 SmartApply Pro. All rights reserved.</p>
        </div>
    </div>
</body>
</html>";
    }
    
    /**
     * Send order confirmation email template for pending orders
     */
    private function getOrderConfirmationTemplate($customerName, $orderNumber, $planType, $amount, $currency, $paymentMethod) {
        $websiteUrl = 'https://smartapplypro.com';
        $planDisplay = ucfirst($planType);
        $currencySymbol = $currency === 'BDT' ? '৳' : '$';
        
        return "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Order Confirmation - SmartApply Pro</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #3b82f6; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .order-box { background: #fff; border: 2px solid #3b82f6; padding: 15px; margin: 20px 0; border-radius: 8px; }
        .order-item { margin: 10px 0; padding: 10px; background: #eff6ff; border-radius: 4px; }
        .order-label { font-weight: bold; color: #1e40af; }
        .order-value { font-size: 16px; color: #333; }
        .button { display: inline-block; background: #3b82f6; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; margin: 10px 0; }
        .footer { padding: 20px; text-align: center; color: #666; font-size: 12px; }
        .info-box { background: #dbeafe; padding: 15px; border-left: 4px solid #3b82f6; margin: 20px 0; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>✅ Order Confirmed!</h1>
        </div>
        
        <div class='content'>
            <p>Hi {$customerName},</p>
            
            <p>Thank you for your order! We have received your payment via <strong>{$paymentMethod}</strong> and it is being processed.</p>
            
            <div class='order-box'>
                <h3 style='margin-top: 0; color: #1e40af;'>📦 Order Details</h3>
                <div class='order-item'>
                    <span class='order-label'>Order Number:</span><br>
                    <span class='order-value'>{$orderNumber}</span>
                </div>
                <div class='order-item'>
                    <span class='order-label'>Plan:</span><br>
                    <span class='order-value'>{$planDisplay}</span>
                </div>
                <div class='order-item'>
                    <span class='order-label'>Amount:</span><br>
                    <span class='order-value'>{$currencySymbol}{$amount}</span>
                </div>
                <div class='order-item'>
                    <span class='order-label'>Payment Method:</span><br>
                    <span class='order-value'>{$paymentMethod}</span>
                </div>
            </div>
            
            <div class='info-box'>
                <p style='margin: 0;'><strong>⏱️ What happens next?</strong></p>
                <p style='margin: 10px 0 0 0;'>Your order will be reviewed within <strong>24-48 hours</strong>. Once approved, you will receive your license key via email.</p>
            </div>
            
            <p><strong>What you'll get:</strong></p>
            <ul>
                <li>✅ License Key for SmartApply Pro</li>
                <li>✅ Activation Instructions</li>
                <li>✅ Premium Features Access</li>
                <li>✅ Priority Support</li>
            </ul>
            
            <p>If you have any questions about your order, please contact us with your order number:</p>
            
            <p style='text-align: center;'><strong>support@smartapplypro.com</strong></p>
        </div>
        
        <div class='footer'>
            <p>This email was sent regarding order #{$orderNumber}</p>
            <p>© 2024 SmartApply Pro. All rights reserved.</p>
        </div>
    </div>
</body>
</html>";
    }
    
    /**
     * Send email using PHP mail function or SMTP
     */
    private function sendEmail($to, $subject, $message) {
        // For demo purposes, using PHP mail function
        // In production, you should use a proper SMTP library like PHPMailer
        
        $headers = [
            'From: ' . $this->fromEmail,
            'Reply-To: ' . $this->fromEmail,
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8'
        ];
        
        $headerString = implode("\r\n", $headers);
        
        // Log email attempt (for demo)
        error_log("Email sent to {$to}: {$subject}");
        
        // In demo mode, just return true
        // In production: return mail($to, $subject, $message, $headerString);
        return true;
    }
}
?>