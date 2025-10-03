<?php
/**
 * SmartApply Pro landing page with PHP backend integration
 */

// Load configuration
require_once 'config/config.php';

// Configuration with fallbacks for development
$scheme = $_SERVER['REQUEST_SCHEME'] ?? ($_SERVER['HTTPS'] ?? null) ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
$api_base_url = $scheme . '://' . $host . '/api';
$stripe_publishable_key = STRIPE_PUBLISHABLE_KEY;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartApply Pro - Premium Chrome Extension for Job Applications</title>
    <meta name="description" content="Upgrade to SmartApply Pro for advanced cover letter analysis, response rate optimization, and premium features to boost your freelancing success.">
    
    <!-- CSS -->
    <link rel="stylesheet" href="css/styles.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="images/smartapply_icon48.png">
    
    <!-- Stripe.js -->
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="nav container">
            <div class="nav-brand">
                <img src="images/smartapply_icon48.png" alt="SmartApply Pro" class="nav-logo">
                <span class="nav-title">SmartApply Pro</span>
            </div>
            <div class="nav-links">
                <a href="#features" class="nav-link" onclick="scrollToSection('features')">Features</a>
                <a href="#pricing" class="nav-link" onclick="scrollToSection('pricing')">Pricing</a>
                <a href="#about" class="nav-link" onclick="scrollToSection('about')">About</a>
                <a href="#contact" class="nav-link" onclick="scrollToSection('contact')">Contact</a>
                <a href="#faq" class="nav-link" onclick="scrollToSection('faq')">FAQ</a>
                <button class="btn btn-primary" onclick="scrollToSection('pricing')">Get Started</button>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title"><span class="gradient-text">SmartApply</span> – AI-Powered Job Application Assistant</h1>
                <p class="hero-subtitle">💡 Stop Wasting Money on Wrong Jobs – Apply Smarter!</p>
                <p class="hero-description">SmartApply is an AI-enabled Google Chrome extension designed to help freelancers write effective applications and analyze jobs before applying. Save money, time, and get better responses on platforms like Upwork with intelligent job analysis and cover letter optimization.</p>
                
                <div class="hero-features">
                    <div class="hero-feature">
                        <span class="icon icon-check"></span>
                        <span>Job Compatibility Analysis</span>
                    </div>
                    <div class="hero-feature">
                        <span class="icon icon-chart"></span>
                        <span>Cover Letter Optimization</span>
                    </div>
                    <div class="hero-feature">
                        <span class="icon icon-target"></span>
                        <span>Profile Recommendations</span>
                    </div>
                </div>
                
                <div class="hero-cta">
                    <button class="btn btn-primary btn-large" onclick="scrollToSection('pricing')">
                        <span class="icon icon-rocket icon-with-text"></span>
                        Get Started - From $4.95/month
                    </button>
                    <p class="hero-subtext">✨ AI-enabled Chrome extension • Save time & money • Get hired faster</p>
                </div>
            </div>
            
            <div class="hero-image">
                <img src="images/smartapply_icon128.png" alt="SmartApply Extension" class="extension-preview">
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="container">
            <h2 class="section-title">✨ Two Powerful Features</h2>
            <p class="section-subtitle">Analyze jobs and optimize cover letters with AI precision</p>
            
            <div class="features-grid">
                <!-- Feature 1: Job Analysis -->
                <div class="feature-card">
                    <div class="feature-icon">
                        <span class="icon icon-target"></span>
                    </div>
                    <h3 class="feature-title">Job Analysis</h3>
                    <p class="feature-description">Check if a job is compatible with your profile using 15+ criteria including skills, experience, portfolios, and work history. Get a compatibility score and profile recommendations (main or specialized) to save money and time.</p>
                </div>
                
                <!-- Feature 2: Cover Letter Analysis -->
                <div class="feature-card">
                    <div class="feature-icon">
                        <span class="icon icon-chart"></span>
                    </div>
                    <h3 class="feature-title">Cover Letter Analysis</h3>
                    <p class="feature-description">Analyze your cover letter using 20+ factors. Get instant feedback on errors, improvements, and optimization tips to make your application stand out and rank higher in client dashboards.</p>
                </div>
                
                <!-- Feature 3: Save Money -->
                <div class="feature-card">
                    <div class="feature-icon">
                        <span class="icon icon-dollar"></span>
                    </div>
                    <h3 class="feature-title">Save Money on Applications</h3>
                    <p class="feature-description">Upwork connects cost money. Stop burning cash on jobs that won't respond. Apply only to compatible opportunities and increase your success rate.</p>
                </div>
                
                <!-- Feature 4: Profile Optimization -->
                <div class="feature-card">
                    <div class="feature-icon">
                        <span class="icon icon-user"></span>
                    </div>
                    <h3 class="feature-title">Multi-Profile Support</h3>
                    <p class="feature-description">Supports up to 3 profiles (1 main + 2 specialized). SmartApply tells you which profile to use for each job to maximize your chances.</p>
                </div>
                
                <!-- Feature 5: Get More Jobs -->
                <div class="feature-card">
                    <div class="feature-icon">
                        <span class="icon icon-briefcase"></span>
                    </div>
                    <h3 class="feature-title">Get Hired Faster</h3>
                    <p class="feature-description">Write better proposals, apply to better-fit jobs, and get more responses. This is how SmartApply helps freelancers succeed.</p>
                </div>
                
                <!-- Feature 6: AI-Powered -->
                <div class="feature-card">
                    <div class="feature-icon">
                        <span class="icon icon-zap"></span>
                    </div>
                    <h3 class="feature-title">AI-Enabled Technology</h3>
                    <p class="feature-description">Powered by advanced AI to analyze jobs and cover letters with precision, helping you make data-driven decisions.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Perfect For Section -->
    <section class="perfect-for">
        <div class="container">
            <h2 class="section-title">💼 Perfect For Freelancers Who Want To:</h2>
            
            <div class="perfect-for-grid">
                <div class="perfect-for-item">
                    <span class="perfect-for-icon">✅</span>
                    <p>Discover jobs that match their true skills and experience</p>
                </div>
                <div class="perfect-for-item">
                    <span class="perfect-for-icon">✅</span>
                    <p>Save time by applying only to the most relevant opportunities</p>
                </div>
                <div class="perfect-for-item">
                    <span class="perfect-for-icon">✅</span>
                    <p>Make confident, data-driven application decisions</p>
                </div>
                <div class="perfect-for-item">
                    <span class="perfect-for-icon">✅</span>
                    <p>Boost their job search success rate</p>
                </div>
                <div class="perfect-for-item">
                    <span class="perfect-for-icon">✅</span>
                    <p>Analyze client patterns and preferences for smarter outreach</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works">
        <div class="container">
            <h2 class="section-title">🔎 How It Works</h2>
            
            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h3 class="step-title">Install & Setup</h3>
                    <p class="step-description">Install SmartApply and set up your freelancer profile</p>
                </div>
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h3 class="step-title">Browse Jobs</h3>
                    <p class="step-description">Browse job listings on supported platforms</p>
                </div>
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h3 class="step-title">Analyze</h3>
                    <p class="step-description">Click the SmartApply icon to analyze any job post</p>
                </div>
                <div class="step-card">
                    <div class="step-number">4</div>
                    <h3 class="step-title">Review & Apply</h3>
                    <p class="step-description">Review compatibility scores & apply with confidence</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="pricing">
        <div class="container">
            <h2 class="section-title">Choose Your Plan</h2>
            <p class="section-subtitle">Simple, transparent pricing for freelancers</p>
            
            <div class="pricing-cards">
                <!-- Trial Plan -->
                <div class="pricing-card">
                    <div class="pricing-header">
                        <h3 class="pricing-title">Trial</h3>
                        <div class="pricing-price">
                            <span class="price-currency">$</span>
                            <span class="price-amount">0</span>
                            <span class="price-period">Free Forever</span>
                        </div>
                        <div class="pricing-price-bdt" style="display: none;">
                            <span class="price-currency">৳</span>
                            <span class="price-amount">0</span>
                            <span class="price-period">Free Forever</span>
                        </div>
                    </div>
                    <ul class="pricing-features">
                        <li>✅ Lifetime Job Analysis</li>
                        <li>✅ 10 Cover Letter Analyses</li>
                        <li>✅ Profile Compatibility Scoring</li>
                        <li>✅ Multi-Profile Support</li>
                        <li>⚠️ Upgrade for unlimited cover letter analysis</li>
                    </ul>
                    <button class="btn btn-primary btn-full" onclick="initiatePurchase('trial', 0, 0)">
                        Get Trial
                    </button>
                </div>
                
                <!-- Monthly Plan -->
                <div class="pricing-card">
                    <div class="pricing-header">
                        <h3 class="pricing-title">Monthly</h3>
                        <div class="pricing-price">
                            <span class="price-currency">$</span>
                            <span class="price-amount">4.95</span>
                            <span class="price-period">/month</span>
                        </div>
                        <div class="pricing-price-bdt" style="display: none;">
                            <span class="price-currency">৳</span>
                            <span class="price-amount">495</span>
                            <span class="price-period">/month</span>
                        </div>
                    </div>
                    <ul class="pricing-features">
                        <li>✅ All Trial Features</li>
                        <li>✅ 200 Cover Letter Analyses/month</li>
                        <li>✅ Unlimited Job Analysis</li>
                        <li>✅ Premium Support</li>
                        <li>✅ Profile Recommendations</li>
                    </ul>
                    <button class="btn btn-primary btn-full" onclick="initiatePurchase('monthly', 4.95, 495)">
                        Get Started
                    </button>
                </div>
                
                <!-- Yearly Plan (Most Popular) -->
                <div class="pricing-card pricing-card-featured">
                    <div class="pricing-badge">Most Popular</div>
                    <div class="pricing-header">
                        <h3 class="pricing-title">Yearly</h3>
                        <div class="pricing-price">
                            <span class="price-currency">$</span>
                            <span class="price-amount">35</span>
                            <span class="price-period">/year</span>
                        </div>
                        <div class="pricing-price-bdt" style="display: none;">
                            <span class="price-currency">৳</span>
                            <span class="price-amount">3,500</span>
                            <span class="price-period">/year</span>
                        </div>
                        <div class="pricing-savings">Save 41%</div>
                    </div>
                    <ul class="pricing-features">
                        <li>✅ All Trial Features</li>
                        <li>✅ 2,500 Cover Letter Analyses/year</li>
                        <li>✅ Unlimited Job Analysis</li>
                        <li>✅ Premium Support</li>
                        <li>✅ Best Value - Save money!</li>
                    </ul>
                    <button class="btn btn-primary btn-full" onclick="initiatePurchase('annual', 35, 3500)">
                        Get Yearly Plan
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about">
        <div class="container">
            <h2 class="section-title">About SmartApply</h2>
            <p class="section-subtitle">Your AI-Powered Job Application Assistant</p>
            
            <div class="about-content">
                <div class="about-text">
                    <h3>What is SmartApply?</h3>
                    <p>SmartApply is an AI-enabled Google Chrome extension designed to help freelancers write effective applications and make smarter decisions before applying to jobs on platforms like Upwork.</p>
                    
                    <p>Freelance platforms now cost a lot to apply for jobs. However, many freelancers don't get proper responses while burning money on connects. SmartApply solves this problem by analyzing jobs before you apply, so you can save money and time while increasing your success rate.</p>
                    
                    <h3>How Does It Help?</h3>
                    <ul class="about-features-list">
                        <li><strong>Job Analysis:</strong> Check if a job is compatible with your profile using 15+ criteria including skills, experience, portfolios, and work history. Get a compatibility score to decide if it's worth applying.</li>
                        <li><strong>Profile Recommendations:</strong> With support for up to 3 profiles (1 main + 2 specialized), SmartApply tells you which profile to use for maximum success.</li>
                        <li><strong>Cover Letter Analysis:</strong> Analyze your cover letter using 20+ factors. Get instant feedback on errors and improvements to make your application stand out and rank higher in client dashboards.</li>
                        <li><strong>Save Money & Time:</strong> Apply only to compatible jobs and write better proposals, leading to more responses and more jobs.</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="container">
            <h2 class="section-title">Get In Touch</h2>
            <p class="section-subtitle">Have questions? We're here to help!</p>
            
            <div class="contact-content">
                <div class="contact-info">
                    <div class="contact-item">
                        <span class="contact-icon">📧</span>
                        <div>
                            <h3>Email Support</h3>
                            <p><a href="mailto:support@smartapplypro.com">support@smartapplypro.com</a></p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <span class="contact-icon">🌐</span>
                        <div>
                            <h3>Website</h3>
                            <p><a href="https://smartapplypro.com">smartapplypro.com</a></p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <span class="contact-icon">⏰</span>
                        <div>
                            <h3>Business Hours</h3>
                            <p>24/7 Support for Premium Members</p>
                        </div>
                    </div>
                </div>
                
                <div class="contact-form">
                    <form id="contact-form" onsubmit="handleContactForm(event)">
                        <div class="form-group">
                            <label for="contact-name">Your Name</label>
                            <input type="text" id="contact-name" name="name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="contact-email">Your Email</label>
                            <input type="email" id="contact-email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="contact-subject">Subject</label>
                            <input type="text" id="contact-subject" name="subject" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="contact-message">Message</label>
                            <textarea id="contact-message" name="message" rows="5" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-full">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="faq">
        <div class="container">
            <h2 class="section-title">Frequently Asked Questions</h2>
            
            <div class="faq-list">
                <div class="faq-item">
                    <div class="faq-question">
                        <span>How does SmartApply Pro work?</span>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        <p>SmartApply Pro is a Chrome extension that integrates with job platforms like Upwork. It analyzes job postings, helps optimize your proposals, and provides data-driven insights to improve your success rate.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <span>Do I need to install anything?</span>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        <p>Yes, you need to install the SmartApply Chrome extension. Once you purchase a plan, you'll receive a license key to unlock premium features within the extension.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <span>Can I cancel anytime?</span>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        <p>Yes! Monthly and annual plans can be cancelled anytime. We also offer a 30-day money-back guarantee if you're not satisfied.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <span>How do I activate my license?</span>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        <p>After purchase, you'll receive a license key via email. Open the SmartApply extension, go to Settings, enter your license key, and click Verify to unlock premium features.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Payment Modal -->
    <div id="payment-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Complete Your Purchase</h3>
                <span class="modal-close" onclick="closePaymentModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="payment-form">
                    <div class="form-group">
                        <label for="customer-name">Full Name</label>
                        <input type="text" id="customer-name" name="customerName" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="customer-email">Email Address</label>
                        <input type="email" id="customer-email" name="customerEmail" required>
                    </div>

                    <!-- Payment Method Selection -->
                    <div class="form-group">
                        <label>Payment Method</label>
                        <div class="payment-methods">
                            <div class="payment-method-option">
                                <input type="radio" id="stripe-payment" name="paymentMethod" value="stripe" checked>
                                <label for="stripe-payment" class="payment-method-label">
                                    💳 Credit/Debit Card (Stripe)
                                    <span class="payment-method-description">Secure payment via Stripe. Instant activation.</span>
                                </label>
                            </div>
                            <div class="payment-method-option">
                                <input type="radio" id="bkash-payment" name="paymentMethod" value="bkash">
                                <label for="bkash-payment" class="payment-method-label">
                                    📱 bKash (Mobile Payment - Bangladesh)
                                    <span class="payment-method-description">Manual approval required. 24-48 hours processing.</span>
                                </label>
                            </div>
                            <div class="payment-method-option">
                                <input type="radio" id="nagad-payment" name="paymentMethod" value="nagad">
                                <label for="nagad-payment" class="payment-method-label">
                                    📱 Nagad (Mobile Payment - Bangladesh)
                                    <span class="payment-method-description">Manual approval required. 24-48 hours processing.</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Stripe Payment Fields -->
                    <div id="stripe-fields" class="payment-fields">
                        <div class="form-group">
                            <label for="card-element">Card Information</label>
                            <div id="card-element">
                                <!-- Stripe Elements will create form elements here -->
                            </div>
                            <div id="card-errors" role="alert"></div>
                        </div>
                    </div>

                    <!-- Bkash Payment Fields -->
                    <div id="bkash-fields" class="payment-fields" style="display: none;">
                        <div class="bkash-instructions">
                            <h4>📱 bKash Payment Instructions</h4>
                            <ol>
                                <li>Send payment to: <strong>01XXXXXXXXX</strong></li>
                                <li>Use "Send Money" option in bKash app</li>
                                <li>Enter the transaction ID below</li>
                                <li>Your order will be reviewed manually within 24-48 hours</li>
                            </ol>
                        </div>
                        <div class="form-group">
                            <label for="bkash-mobile">Your bKash Mobile Number</label>
                            <input type="tel" id="bkash-mobile" name="bkashMobile" placeholder="01XXXXXXXXX" pattern="[0-9]{11}">
                        </div>
                        <div class="form-group">
                            <label for="bkash-transaction">bKash Transaction ID</label>
                            <input type="text" id="bkash-transaction" name="bkashTransaction" placeholder="Enter transaction ID">
                        </div>
                    </div>
                    
                    <!-- Nagad Payment Fields -->
                    <div id="nagad-fields" class="payment-fields" style="display: none;">
                        <div class="nagad-instructions">
                            <h4>📱 Nagad Payment Instructions</h4>
                            <ol>
                                <li>Send payment to: <strong>01XXXXXXXXX</strong></li>
                                <li>Use "Send Money" option in Nagad app</li>
                                <li>Enter the transaction ID below</li>
                                <li>Your order will be reviewed manually within 24-48 hours</li>
                            </ol>
                        </div>
                        <div class="form-group">
                            <label for="nagad-mobile">Your Nagad Mobile Number</label>
                            <input type="tel" id="nagad-mobile" name="nagadMobile" placeholder="01XXXXXXXXX" pattern="[0-9]{11}">
                        </div>
                        <div class="form-group">
                            <label for="nagad-transaction">Nagad Transaction ID</label>
                            <input type="text" id="nagad-transaction" name="nagadTransaction" placeholder="Enter transaction ID">
                        </div>
                    </div>
                    
                    <div class="payment-summary">
                        <div class="summary-item">
                            <span class="summary-label">Plan:</span>
                            <span id="summary-plan" class="summary-value"></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Total:</span>
                            <span id="summary-total" class="summary-value"></span>
                        </div>
                    </div>
                    
                    <button type="submit" id="submit-payment" class="btn btn-primary btn-full">
                        <span id="button-text">Complete Purchase</span>
                        <div id="spinner" class="spinner hidden"></div>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="success-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">🎉 Thank You for Your Purchase!</h3>
            </div>
            <div class="modal-body">
                <div class="success-message">
                    <p><strong>Your order has been confirmed!</strong></p>
                    <p>Your license key has been sent to your email address. Please check your inbox (and spam folder) for the email.</p>
                    
                    <div class="activation-steps">
                        <h4>📋 How to activate SmartApply:</h4>
                        <ol>
                            <li>Open the SmartApply Chrome extension</li>
                            <li>Go to Settings or Premium tab</li>
                            <li>Enter your license key</li>
                            <li>Click "Verify" to activate</li>
                        </ol>
                    </div>
                    
                    <p class="support-note">Need help? Contact us at <strong>support@smartapplypro.com</strong></p>
                </div>
                <div class="success-actions">
                    <button class="btn btn-primary" onclick="closeSuccessModal()">Got it!</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bkash Success Modal -->
    <div id="bkash-success-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">✅ Order Submitted Successfully!</h3>
            </div>
            <div class="modal-body">
                <div class="success-message">
                    <p><strong>Thank you for your order!</strong></p>
                    <p>Your Bkash payment is being processed. We'll review your transaction and send your license key within 24-48 hours.</p>
                    
                    <div class="order-details">
                        <h4>📦 Order Details:</h4>
                        <p><strong>Order Number:</strong> <span id="bkash-order-number"></span></p>
                        <p><strong>Amount:</strong> <span id="bkash-amount"></span></p>
                    </div>
                    
                    <p class="support-note">You'll receive an email confirmation shortly. If you have any questions, contact us at <strong>support@smartapplypro.com</strong></p>
                </div>
                <div class="success-actions">
                    <button class="btn btn-primary" onclick="closeBkashSuccessModal()">Got it!</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Configuration with PHP variables
        const CONFIG = {
            STRIPE_PUBLISHABLE_KEY: '<?php echo $stripe_publishable_key; ?>',
            API_BASE_URL: '<?php echo $api_base_url; ?>',
            PLANS: {
                monthly: { 
                    name: 'Monthly', 
                    priceUSD: 4.95, 
                    priceBDT: 595,
                    period: 'month' 
                },
                annual: { 
                    name: 'Yearly', 
                    priceUSD: 34.95, 
                    priceBDT: 4200,
                    period: 'year' 
                },
                lifetime: { 
                    name: 'Lifetime', 
                    priceUSD: 99.95, 
                    priceBDT: 12400,
                    period: 'one-time' 
                }
            }
        };
    </script>
    <script src="js/main.js"></script>
</body>
</html>