// SmartApply Pro Landing Page JavaScript

// Configuration - now uses global CONFIG from PHP
const WEBSITE_CONFIG = window.CONFIG || {
    // Fallback configuration if CONFIG is not set
    STRIPE_PUBLISHABLE_KEY: 'pk_test_your_stripe_key_here',
    API_BASE_URL: getApiBaseUrl(),
    PLANS: {
        trial: { 
            name: 'Trial', 
            priceUSD: 0, 
            priceBDT: 0,
            period: 'Free Forever' 
        },
        monthly: { 
            name: 'Monthly', 
            priceUSD: 4.95, 
            priceBDT: 495,
            period: 'month' 
        },
        annual: { 
            name: 'Yearly', 
            priceUSD: 35, 
            priceBDT: 3500,
            period: 'year' 
        }
    }
};

// Get dynamic API base URL for PHP backend
function getApiBaseUrl() {
    const hostname = window.location.hostname;
    const protocol = window.location.protocol;
    
    if (hostname === 'localhost' || hostname === '127.0.0.1') {
        // Local development
        return `${protocol}//${hostname}:8000/api`;
    } else if (hostname === 'smartapplypro.com' || hostname === 'www.smartapplypro.com') {
        // Production environment
        return `${protocol}//${hostname}/api`;
    } else {
        // Fallback for other environments
        return `${protocol}//${hostname}/api`;
    }
}

// Initialize Stripe (will be loaded when needed)
let stripe = null;
let elements = null;
let cardElement = null;

// Current purchase details
let currentPurchase = {
    plan: null,
    amountUSD: 0,
    amountBDT: 0,
    currency: 'USD'
};

// DOM Ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('SmartApply Pro landing page loaded');
    setupEventListeners();
});

// Set up event listeners
function setupEventListeners() {
    // FAQ toggle functionality
    setupFAQToggles();
    
    // Smooth scrolling for navigation links
    setupSmoothScrolling();
    
    // Modal event listeners
    setupModalEvents();
    
    // Payment method selection
    setupPaymentMethodSelection();
}

// FAQ Toggle functionality
function setupFAQToggles() {
    const faqQuestions = document.querySelectorAll('.faq-question');
    
    faqQuestions.forEach(question => {
        question.addEventListener('click', function() {
            const answer = this.nextElementSibling;
            const isOpen = answer.style.display === 'block';
            
            // Close all other answers
            document.querySelectorAll('.faq-answer').forEach(ans => {
                ans.style.display = 'none';
            });
            
            // Toggle current answer
            answer.style.display = isOpen ? 'none' : 'block';
        });
    });
}

// Smooth scrolling for navigation
function setupSmoothScrolling() {
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href.startsWith('#')) {
                e.preventDefault();
                scrollToSection(href.substring(1));
            }
        });
    });
}

// Scroll to section function
function scrollToSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        section.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
}

// Modal event listeners
function setupModalEvents() {
    // Close modals when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            closePaymentModal();
            closeSuccessModal();
            closeBkashSuccessModal();
        }
    });
    
    // Close modals on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePaymentModal();
            closeSuccessModal();
            closeBkashSuccessModal();
        }
    });
}

// Payment method selection setup
function setupPaymentMethodSelection() {
    const paymentMethodInputs = document.querySelectorAll('input[name="paymentMethod"]');
    
    paymentMethodInputs.forEach(input => {
        input.addEventListener('change', function() {
            togglePaymentFields(this.value);
            updateSubmitButton(this.value);
        });
    });
}

// Toggle payment fields based on selected method
function togglePaymentFields(paymentMethod) {
    const stripeFields = document.getElementById('stripe-fields');
    const bkashFields = document.getElementById('bkash-fields');
    const nagadFields = document.getElementById('nagad-fields');
    const bkashMobile = document.getElementById('bkash-mobile');
    const bkashTransaction = document.getElementById('bkash-transaction');
    const nagadMobile = document.getElementById('nagad-mobile');
    const nagadTransaction = document.getElementById('nagad-transaction');
    const totalElement = document.getElementById('summary-total');
    
    if (paymentMethod === 'stripe') {
        stripeFields.style.display = 'block';
        bkashFields.style.display = 'none';
        nagadFields.style.display = 'none';
        
        // Remove required attribute from mobile payment fields
        bkashMobile.required = false;
        bkashTransaction.required = false;
        nagadMobile.required = false;
        nagadTransaction.required = false;
        
        // Switch to USD
        currentPurchase.currency = 'USD';
        if (totalElement) {
            const usdAmount = totalElement.getAttribute('data-usd');
            totalElement.textContent = `$${usdAmount}`;
        }
        
        // Update pricing cards to show USD
        updatePricingDisplay('USD');
        
    } else if (paymentMethod === 'bkash') {
        stripeFields.style.display = 'none';
        bkashFields.style.display = 'block';
        nagadFields.style.display = 'none';
        
        // Add required attribute to bKash fields
        bkashMobile.required = true;
        bkashTransaction.required = true;
        nagadMobile.required = false;
        nagadTransaction.required = false;
        
        // Switch to BDT
        currentPurchase.currency = 'BDT';
        if (totalElement) {
            const bdtAmount = totalElement.getAttribute('data-bdt');
            totalElement.textContent = `৳${bdtAmount}`;
        }
        
        // Update pricing cards to show BDT
        updatePricingDisplay('BDT');
        
    } else if (paymentMethod === 'nagad') {
        stripeFields.style.display = 'none';
        bkashFields.style.display = 'none';
        nagadFields.style.display = 'block';
        
        // Add required attribute to Nagad fields
        nagadMobile.required = true;
        nagadTransaction.required = true;
        bkashMobile.required = false;
        bkashTransaction.required = false;
        
        // Switch to BDT
        currentPurchase.currency = 'BDT';
        if (totalElement) {
            const bdtAmount = totalElement.getAttribute('data-bdt');
            totalElement.textContent = `৳${bdtAmount}`;
        }
        
        // Update pricing cards to show BDT
        updatePricingDisplay('BDT');
    }
}

// Update pricing display based on currency
function updatePricingDisplay(currency) {
    const pricingCards = document.querySelectorAll('.pricing-card');
    
    pricingCards.forEach(card => {
        const usdPrice = card.querySelector('.pricing-price');
        const bdtPrice = card.querySelector('.pricing-price-bdt');
        
        if (currency === 'USD') {
            if (usdPrice) usdPrice.style.display = 'block';
            if (bdtPrice) bdtPrice.style.display = 'none';
        } else if (currency === 'BDT') {
            if (usdPrice) usdPrice.style.display = 'none';
            if (bdtPrice) bdtPrice.style.display = 'block';
        }
    });
}

// Update submit button text based on payment method
function updateSubmitButton(paymentMethod) {
    const buttonText = document.getElementById('button-text');
    
    if (paymentMethod === 'stripe') {
        buttonText.textContent = 'Complete Purchase';
    } else if (paymentMethod === 'bkash' || paymentMethod === 'nagad') {
        buttonText.textContent = 'Submit Order';
    }
}

// Initiate purchase process
async function initiatePurchase(planType, amountUSD, amountBDT) {
    console.log(`Initiating purchase for ${planType} plan: $${amountUSD} / ৳${amountBDT}`);
    
    // Handle trial plan separately (free signup)
    if (planType === 'trial' && amountUSD === 0) {
        handleTrialSignup();
        return;
    }
    
    currentPurchase = {
        plan: planType,
        amountUSD: amountUSD,
        amountBDT: amountBDT,
        currency: 'USD'
    };
    
    // Update modal with purchase details
    updatePaymentModal(planType, amountUSD, amountBDT);
    
    // Initialize Stripe if not already done
    if (!stripe) {
        await initializeStripe();
    }
    
    // Show payment modal
    showPaymentModal();
}

// Handle trial signup
function handleTrialSignup() {
    // Prompt user for name and email
    const name = prompt('Please enter your full name:');
    if (!name || name.trim() === '') {
        alert('Name is required to create your trial account.');
        return;
    }
    
    const email = prompt('Please enter your email address:');
    if (!email || email.trim() === '') {
        alert('Email is required to create your trial account.');
        return;
    }
    
    // Validate email format
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert('Please enter a valid email address.');
        return;
    }
    
    // Show loading message
    const loadingMessage = 'Creating your trial account...';
    console.log(loadingMessage);
    
    // Submit trial signup
    submitTrialSignup(name.trim(), email.trim());
}

// Submit trial signup to backend
async function submitTrialSignup(name, email) {
    try {
        const config = window.CONFIG || WEBSITE_CONFIG;
        const response = await fetch(`${config.API_BASE_URL}/payment/trial-signup`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                customer: {
                    name: name,
                    email: email
                }
            }),
        });
        
        const result = await response.json();
        
        if (!response.ok) {
            throw new Error(result.error || 'Failed to create trial account');
        }
        
        // Show success modal with trial details
        showTrialSuccessModal(result);
        
    } catch (error) {
        console.error('Trial signup error:', error);
        alert(`Failed to create trial account: ${error.message}\n\nPlease try again or contact support@smartapplypro.com`);
    }
}

// Show trial success modal
function showTrialSuccessModal(result) {
    // Update the success modal with trial-specific content
    const modal = document.getElementById('success-modal');
    const modalBody = modal.querySelector('.modal-body .success-message');
    
    if (modalBody) {
        modalBody.innerHTML = `
            <p><strong>🎉 Your trial account has been created!</strong></p>
            <p>Check your email at <strong>${result.username ? result.username.split(/\d/)[0] + '...' : 'your inbox'}</strong> for:</p>
            <ul style="text-align: left; margin: 20px auto; max-width: 400px;">
                <li>Your username and password</li>
                <li>Your license key</li>
                <li>Activation instructions</li>
            </ul>
            <div style="background: #f0fdf4; border: 2px solid #10b981; padding: 15px; margin: 20px 0; border-radius: 8px;">
                <p style="margin: 0;"><strong>Order Number:</strong> ${result.orderNumber || 'N/A'}</p>
            </div>
            <p class="support-note">If you don't see the email, please check your spam folder. Need help? Contact us at <strong>support@smartapplypro.com</strong></p>
        `;
    }
    
    // Show the modal
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}


// Update payment modal with purchase details
function updatePaymentModal(planType, amountUSD, amountBDT) {
    const config = window.CONFIG || WEBSITE_CONFIG;
    const plan = config.PLANS[planType];
    
    // Update plan details in modal
    const planElement = document.getElementById('summary-plan');
    const totalElement = document.getElementById('summary-total');
    
    if (planElement) {
        planElement.textContent = plan.name;
    }
    
    if (totalElement) {
        // Default to USD
        totalElement.textContent = `$${amountUSD.toFixed(2)}`;
        totalElement.setAttribute('data-usd', amountUSD.toFixed(2));
        totalElement.setAttribute('data-bdt', amountBDT);
    }
    
    // Update modal title
    const modalTitle = document.querySelector('#payment-modal .modal-title');
    if (modalTitle) {
        modalTitle.textContent = `Complete Your ${plan.name} Purchase`;
    }
}

// Initialize Stripe
async function initializeStripe() {
    try {
        // In production, replace with your actual Stripe publishable key
        const config = window.CONFIG || WEBSITE_CONFIG;
        stripe = Stripe(config.STRIPE_PUBLISHABLE_KEY);
        
        elements = stripe.elements();
        
        // Create card element
        cardElement = elements.create('card', {
            style: {
                base: {
                    fontSize: '16px',
                    color: '#424770',
                    '::placeholder': {
                        color: '#aab7c4',
                    },
                },
            },
        });
        
        cardElement.mount('#card-element');
        
        // Handle real-time validation errors from the card Element
        cardElement.on('change', function(event) {
            const displayError = document.getElementById('payment-errors');
            if (displayError) {
                if (event.error) {
                    displayError.textContent = event.error.message;
                } else {
                    displayError.textContent = '';
                }
            }
        });
        
        // Handle form submission
        const form = document.getElementById('checkout-form');
        if (form) {
            form.addEventListener('submit', handleFormSubmit);
        }
        
        console.log('Stripe initialized successfully');
    } catch (error) {
        console.error('Error initializing Stripe:', error);
        // Fallback to mock payment for demo
        setupMockPayment();
    }
}

// Setup mock payment for demo purposes
function setupMockPayment() {
    console.log('Setting up mock payment system');
    
    const form = document.getElementById('payment-form');
    const cardElement = document.getElementById('card-element');
    
    // Replace card element with mock input
    cardElement.innerHTML = `
        <input type="text" placeholder="1234 5678 9012 3456" style="
            width: 100%;
            padding: 0.75rem;
            border: none;
            outline: none;
            background: transparent;
            font-size: 16px;
        ">
    `;
    
    form.addEventListener('submit', handleMockFormSubmit);
}

// Handle form submission with both Stripe and Bkash
async function handleFormSubmit(event) {
    event.preventDefault();
    
    const submitButton = document.getElementById('submit-payment');
    const buttonText = document.getElementById('button-text');
    const spinner = document.getElementById('spinner');
    
    // Show loading state
    submitButton.disabled = true;
    buttonText.textContent = 'Processing...';
    spinner.classList.remove('hidden');
    
    try {
        // Get form data
        const formData = new FormData(event.target);
        const paymentMethod = formData.get('paymentMethod');
        const customerData = {
            name: formData.get('customerName'),
            email: formData.get('customerEmail')
        };
        
        // Validate required fields
        if (!customerData.name || !customerData.email) {
            throw new Error('Please fill in all required fields');
        }
        
        if (paymentMethod === 'stripe') {
            await handleStripePayment(customerData);
        } else if (paymentMethod === 'bkash') {
            await handleBkashPayment(customerData, formData);
        } else if (paymentMethod === 'nagad') {
            await handleNagadPayment(customerData, formData);
        } else {
            throw new Error('Please select a payment method');
        }
        
    } catch (error) {
        console.error('Payment error:', error);
        handlePaymentError(error.message);
    } finally {
        // Restore button state
        submitButton.disabled = false;
        const selectedMethod = document.querySelector('input[name="paymentMethod"]:checked')?.value;
        buttonText.textContent = (selectedMethod === 'bkash' || selectedMethod === 'nagad') ? 'Submit Order' : 'Complete Purchase';
        spinner.classList.add('hidden');
    }
}

// Handle Stripe payment
async function handleStripePayment(customerData) {
    if (!stripe || !cardElement) {
        throw new Error('Stripe is not initialized');
    }
    
    // Create payment method
    const {error, paymentMethod} = await stripe.createPaymentMethod({
        type: 'card',
        card: cardElement,
        billing_details: {
            name: customerData.name,
            email: customerData.email,
        },
    });
    
    if (error) {
        throw error;
    }
    
// Send payment to backend - updated for PHP backend
    const config = window.CONFIG || WEBSITE_CONFIG;
    const response = await fetch(`${config.API_BASE_URL}/payment/create-intent`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            paymentMethodId: paymentMethod.id,
            amount: currentPurchase.amountUSD,
            currency: 'usd',
            planType: currentPurchase.plan,
            customer: customerData
        }),
    });
    
    const paymentResult = await response.json();
    
    if (!response.ok) {
        throw new Error(paymentResult.error || 'Payment failed');
    }
    
    // Handle successful payment
    handlePaymentSuccess(paymentResult);
}

// Handle Bkash payment
async function handleBkashPayment(customerData, formData) {
    const bkashData = {
        mobileNumber: formData.get('bkashMobile'),
        transactionId: formData.get('bkashTransaction')
    };
    
    // Validate Bkash fields
    if (!bkashData.mobileNumber || !bkashData.transactionId) {
        throw new Error('Please fill in all bKash payment details');
    }
    
    // Validate mobile number format (Bangladesh mobile number)
    const mobileRegex = /^(\+88)?01[3-9]\d{8}$/;
    if (!mobileRegex.test(bkashData.mobileNumber)) {
        throw new Error('Please enter a valid Bangladeshi mobile number');
    }
    
    // Send Bkash order to backend
    const config = window.CONFIG || WEBSITE_CONFIG;
    const response = await fetch(`${config.API_BASE_URL}/payment/bkash-order`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            amount: currentPurchase.amountBDT,
            currency: 'BDT',
            planType: currentPurchase.plan,
            customer: customerData,
            bkash: bkashData
        }),
    });
    
    const orderResult = await response.json();
    
    if (!response.ok) {
        throw new Error(orderResult.error || 'Order submission failed');
    }
    
    // Handle successful order submission
    handleBkashOrderSuccess(orderResult);
}

// Handle Nagad payment
async function handleNagadPayment(customerData, formData) {
    const nagadData = {
        mobileNumber: formData.get('nagadMobile'),
        transactionId: formData.get('nagadTransaction')
    };
    
    // Validate Nagad fields
    if (!nagadData.mobileNumber || !nagadData.transactionId) {
        throw new Error('Please fill in all Nagad payment details');
    }
    
    // Validate mobile number format (Bangladesh mobile number)
    const mobileRegex = /^(\+88)?01[3-9]\d{8}$/;
    if (!mobileRegex.test(nagadData.mobileNumber)) {
        throw new Error('Please enter a valid Bangladeshi mobile number');
    }
    
    // Send Nagad order to backend (using same endpoint as bkash with different payment method)
    const config = window.CONFIG || WEBSITE_CONFIG;
    const response = await fetch(`${config.API_BASE_URL}/payment/nagad-order`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            amount: currentPurchase.amountBDT,
            currency: 'BDT',
            planType: currentPurchase.plan,
            customer: customerData,
            nagad: nagadData
        }),
    });
    
    const orderResult = await response.json();
    
    if (!response.ok) {
        throw new Error(orderResult.error || 'Order submission failed');
    }
    
    // Handle successful order submission (reuse bKash success handling)
    handleBkashOrderSuccess(orderResult);
}

// Handle successful Bkash order submission
function handleBkashOrderSuccess(result) {
    console.log('Bkash order successful:', result);
    
    // Close payment modal
    closePaymentModal();
    
    // Show custom success modal for Bkash
    showBkashSuccessModal(result);
}

// Show Bkash success modal
function showBkashSuccessModal(result) {
    const modal = document.getElementById('bkash-success-modal');
    const orderNumber = document.getElementById('bkash-order-number');
    const amount = document.getElementById('bkash-amount');
    
    if (orderNumber && result.orderNumber) {
        orderNumber.textContent = result.orderNumber;
    }
    
    if (amount && currentPurchase.amountBDT) {
        amount.textContent = `৳${currentPurchase.amountBDT}`;
    }
    
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

// Close Bkash success modal
function closeBkashSuccessModal() {
    const modal = document.getElementById('bkash-success-modal');
    if (modal) {
        modal.classList.remove('active');
    }
    document.body.style.overflow = '';
}

// Check order status - updated for PHP backend
async function checkOrderStatus(orderNumber) {
    try {
        const config = window.CONFIG || WEBSITE_CONFIG;
        const response = await fetch(`${config.API_BASE_URL}/payment/order-status/${orderNumber}`);
        const result = await response.json();
        
        if (response.ok) {
            alert(`Order Status: ${result.orderStatus}\nPayment Status: ${result.paymentStatus}`);
        } else {
            alert('Could not fetch order status. Please try again later.');
        }
    } catch (error) {
        console.error('Error checking order status:', error);
        alert('Could not fetch order status. Please try again later.');
    }
}

// Handle mock form submission (for demo)
async function handleMockFormSubmit(event) {
    event.preventDefault();
    
    const submitButton = document.getElementById('submit-payment');
    const buttonText = document.getElementById('button-text');
    const spinner = document.getElementById('spinner');
    
    // Show loading state
    submitButton.disabled = true;
    buttonText.textContent = 'Processing...';
    spinner.classList.remove('hidden');
    
    // Get form data
    const formData = new FormData(event.target);
    const customerData = {
        name: formData.get('customerName'),
        email: formData.get('customerEmail')
    };
    
    // Validate form
    if (!customerData.name || !customerData.email) {
        handlePaymentError('Please fill in all required fields');
        submitButton.disabled = false;
        buttonText.textContent = 'Complete Purchase';
        spinner.classList.add('hidden');
        return;
    }
    
    // Simulate API call
    setTimeout(async () => {
        try {
            // Simulate successful payment
            const mockResult = {
                success: true,
                licenseKey: generateMockLicenseKey(),
                customer: customerData,
                plan: currentPurchase.plan,
                amount: currentPurchase.amount
            };
            
            // Handle successful payment
            handlePaymentSuccess(mockResult);
        } catch (error) {
            handlePaymentError(error.message);
        } finally {
            // Restore button state
            submitButton.disabled = false;
            buttonText.textContent = 'Complete Purchase';
            spinner.classList.add('hidden');
        }
    }, 2000); // Simulate 2 second processing time
}

// Generate mock license key for demo
function generateMockLicenseKey() {
    const prefix = 'SMARTAPPLY-PRO-';
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let result = prefix;
    
    for (let i = 0; i < 16; i++) {
        if (i > 0 && i % 4 === 0) {
            result += '-';
        }
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    
    return result;
}

// Handle successful payment
function handlePaymentSuccess(result) {
    console.log('Payment successful:', result);
    
    // Close payment modal
    closePaymentModal();
    
    // Show success modal
    showSuccessModal();
    
    // In a real implementation, the license key would be sent via email
    // and stored in the database through your backend API
    console.log('License key generated:', result.licenseKey);
}

// Handle payment error
function handlePaymentError(errorMessage) {
    console.error('Payment error:', errorMessage);
    
    const errorElement = document.getElementById('payment-errors');
    if (errorElement) {
        errorElement.textContent = errorMessage;
    }
}

// Modal functions
function showPaymentModal() {
    const modal = document.getElementById('payment-modal');
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closePaymentModal() {
    const modal = document.getElementById('payment-modal');
    if (modal) {
        modal.classList.remove('active');
    }
    document.body.style.overflow = '';
    
    // Reset form
    const form = document.getElementById('checkout-form');
    if (form) {
        form.reset();
    }
    
    // Clear errors
    const errorElement = document.getElementById('payment-errors');
    if (errorElement) {
        errorElement.textContent = '';
    }
}

function showSuccessModal() {
    const modal = document.getElementById('success-modal');
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeSuccessModal() {
    const modal = document.getElementById('success-modal');
    modal.classList.remove('active');
    document.body.style.overflow = '';
}

// Utility functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

// Export functions for global access
window.scrollToSection = scrollToSection;
window.initiatePurchase = initiatePurchase;
window.closePaymentModal = closePaymentModal;
window.closeSuccessModal = closeSuccessModal;
window.closeBkashSuccessModal = closeBkashSuccessModal;
// Handle contact form submission
async function handleContactForm(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const contactData = {
        name: formData.get("name"),
        email: formData.get("email"),
        subject: formData.get("subject"),
        message: formData.get("message")
    };
    
    // Validate fields
    if (!contactData.name || !contactData.email || !contactData.subject || !contactData.message) {
        alert("Please fill in all fields");
        return;
    }
    
    try {
        const config = window.CONFIG || WEBSITE_CONFIG;
        const response = await fetch(`${config.API_BASE_URL}/contact`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(contactData),
        });
        
        const result = await response.json();
        
        if (response.ok) {
            alert("Thank you for your message! We will get back to you soon.");
            event.target.reset();
        } else {
            alert("Failed to send message. Please try again or email us directly at support@smartapplypro.com");
        }
    } catch (error) {
        console.error("Contact form error:", error);
        alert("Failed to send message. Please try again or email us directly at support@smartapplypro.com");
    }
}

window.handleContactForm = handleContactForm;
