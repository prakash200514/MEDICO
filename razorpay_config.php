<?php
// Razorpay Configuration
// Replace these with your actual Razorpay credentials
define('RAZORPAY_KEY_ID', 'rzp_test_YOUR_KEY_ID'); // Your Razorpay Key ID
define('RAZORPAY_KEY_SECRET', 'YOUR_KEY_SECRET'); // Your Razorpay Key Secret

// For production, use live keys instead of test keys
// define('RAZORPAY_KEY_ID', 'rzp_live_YOUR_LIVE_KEY_ID');
// define('RAZORPAY_KEY_SECRET', 'YOUR_LIVE_KEY_SECRET');

// GPay UPI ID (your GPay UPI ID)
define('GPAY_UPI_ID', 'yourname@okicici'); // Replace with your actual GPay UPI ID

// Payment Gateway Settings
define('CURRENCY', 'INR');
define('COMPANY_NAME', 'Medico Medicine Store');

// Include Razorpay PHP SDK
// You need to install this via Composer: composer require razorpay/razorpay
// Or download from: https://github.com/razorpay/razorpay-php
require_once 'vendor/autoload.php'; // If using Composer
// require_once 'razorpay-php/Razorpay.php'; // If downloaded manually

use Razorpay\Api\Api;

// Initialize Razorpay API
function getRazorpayApi() {
    return new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);
}

// Create payment order
function createPaymentOrder($amount, $order_id, $customer_name, $customer_email, $customer_phone) {
    try {
        $api = getRazorpayApi();
        
        $orderData = [
            'receipt' => $order_id,
            'amount' => $amount * 100, // Razorpay expects amount in paise
            'currency' => CURRENCY,
            'notes' => [
                'customer_name' => $customer_name,
                'customer_email' => $customer_email,
                'customer_phone' => $customer_phone
            ]
        ];
        
        $razorpayOrder = $api->order->create($orderData);
        return $razorpayOrder;
        
    } catch (Exception $e) {
        error_log('Razorpay Order Creation Error: ' . $e->getMessage());
        return false;
    }
}

// Verify payment signature
function verifyPaymentSignature($payment_id, $order_id, $signature) {
    try {
        $api = getRazorpayApi();
        $attributes = [
            'razorpay_payment_id' => $payment_id,
            'razorpay_order_id' => $order_id,
            'razorpay_signature' => $signature
        ];
        
        $api->utility->verifyPaymentSignature($attributes);
        return true;
        
    } catch (Exception $e) {
        error_log('Payment Signature Verification Error: ' . $e->getMessage());
        return false;
    }
}

// Get payment details
function getPaymentDetails($payment_id) {
    try {
        $api = getRazorpayApi();
        $payment = $api->payment->fetch($payment_id);
        return $payment;
        
    } catch (Exception $e) {
        error_log('Payment Fetch Error: ' . $e->getMessage());
        return false;
    }
}
?> 