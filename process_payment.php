<?php
session_start();
include 'db.php';
include 'razorpay_config.php';

// Check if payment data is available
if (!isset($_SESSION['pending_order']) || empty($_SESSION['pending_order'])) {
    header('Location: checkout.php');
    exit();
}

$orderData = $_SESSION['pending_order'];
$payment_success = false;
$error_message = '';

// Handle payment processing
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['process_payment'])) {
    $payment_method = $_POST['payment_method'];
    
    try {
        // Create a unique order ID
        $order_id = 'ORDER_' . time() . '_' . rand(1000, 9999);
        
        // Create Razorpay order
        $razorpayOrder = createPaymentOrder(
            $orderData['total'],
            $order_id,
            $orderData['customer_name'],
            $orderData['customer_email'],
            $orderData['customer_phone']
        );
        
        if ($razorpayOrder) {
            // Store order details in session for payment verification
            $_SESSION['razorpay_order'] = [
                'order_id' => $razorpayOrder->id,
                'amount' => $orderData['total'],
                'customer_name' => $orderData['customer_name'],
                'customer_email' => $orderData['customer_email'],
                'customer_phone' => $orderData['customer_phone'],
                'payment_method' => $payment_method
            ];
            
            // Redirect to Razorpay payment page
            header('Location: razorpay_payment.php?order_id=' . $razorpayOrder->id);
            exit();
        } else {
            $error_message = "Failed to create payment order. Please try again.";
        }
        
    } catch (Exception $e) {
        $error_message = "Payment processing error: " . $e->getMessage();
    }
}

include 'header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
body, .auth-page {
    font-family: 'Poppins', sans-serif;
}
.auth-page {
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.auth-container {
    width: 100%;
    max-width: 500px;
    margin: 0 auto;
}
.auth-card {
    background: white;
    border-radius: 20px;
    padding: 2.5rem;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    text-align: center;
}
.auth-header {
    margin-bottom: 2rem;
}
.auth-header i {
    font-size: 3rem;
    color: #667eea;
    margin-bottom: 1rem;
}
.auth-header h2 {
    font-size: 2rem;
    color: #333;
    margin-bottom: 0.5rem;
    font-weight: 700;
}
.auth-header p {
    color: #666;
    font-size: 1rem;
}
.payment-summary {
    background: #f8f9fa;
    border-radius: 15px;
    padding: 1.5rem;
    margin: 1.5rem 0;
    text-align: left;
}
.payment-summary h3 {
    color: #333;
    margin-bottom: 1rem;
    font-size: 1.2rem;
}
.payment-summary p {
    margin: 0.5rem 0;
    color: #666;
}
.payment-summary strong {
    color: #333;
}
.payment-methods {
    margin: 1.5rem 0;
}
.payment-method {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 1rem;
    margin: 0.5rem 0;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 1rem;
}
.payment-method:hover {
    border-color: #667eea;
    background: #f0f4ff;
}
.payment-method.selected {
    border-color: #667eea;
    background: #e3eafc;
}
.payment-method input[type="radio"] {
    margin: 0;
}
.payment-method-icon {
    font-size: 1.5rem;
    color: #667eea;
}
.payment-method-info h4 {
    margin: 0;
    color: #333;
    font-size: 1rem;
}
.payment-method-info p {
    margin: 0.2rem 0 0 0;
    color: #666;
    font-size: 0.9rem;
}
.auth-btn {
    width: 100%;
    padding: 1rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 1rem;
}
.auth-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
}
.auth-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}
.auth-footer {
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e2e8f0;
}
.auth-footer a {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}
.auth-footer a:hover {
    color: #5a6fd8;
}
.back-home {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #666 !important;
    font-size: 0.9rem;
}
.back-home:hover {
    color: #333 !important;
}
.gpay-info {
    background: #e8f5e8;
    border: 1px solid #4caf50;
    border-radius: 10px;
    padding: 1rem;
    margin: 1rem 0;
    text-align: left;
}
.gpay-info h4 {
    color: #2e7d32;
    margin: 0 0 0.5rem 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.gpay-info p {
    color: #388e3c;
    margin: 0;
    font-size: 0.9rem;
}
@media (max-width: 480px) {
    .auth-card {
        padding: 2rem;
        margin: 1rem;
    }
    .auth-header h2 {
        font-size: 1.5rem;
    }
}
</style>

<div class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-credit-card"></i>
                <h2>Secure Payment</h2>
                <p>Complete your payment to place your order</p>
            </div>
            
            <?php if (isset($error_message)): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid #f5c6cb;">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <div class="payment-summary">
                <h3><i class="fas fa-shopping-cart"></i> Order Summary</h3>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($orderData['customer_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($orderData['customer_email']); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($orderData['customer_address']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($orderData['customer_phone']); ?></p>
                <p><strong>Total Amount:</strong> ₹<?php echo number_format($orderData['total'], 2); ?></p>
            </div>
            
            <div class="gpay-info">
                <h4><i class="fas fa-mobile-alt"></i> GPay Payment</h4>
                <p>Your payment will be processed securely through GPay and credited to our account. You'll receive a payment confirmation once the transaction is complete.</p>
            </div>
            
            <form method="post" class="auth-form" id="paymentForm">
                <div class="payment-methods">
                    <h3 style="margin-bottom:1rem;"><i class="fas fa-credit-card"></i> Select Payment Method</h3>
                    
                    <div class="payment-method selected" data-method="gpay">
                        <input type="radio" name="payment_method" value="gpay" checked>
                        <div class="payment-method-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <div class="payment-method-info">
                            <h4>Google Pay (GPay)</h4>
                            <p>Pay securely with GPay UPI</p>
                        </div>
                    </div>
                    
                    <div class="payment-method" data-method="card">
                        <input type="radio" name="payment_method" value="card">
                        <div class="payment-method-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <div class="payment-method-info">
                            <h4>Credit/Debit Card</h4>
                            <p>Pay with your card</p>
                        </div>
                    </div>
                    
                    <div class="payment-method" data-method="upi">
                        <input type="radio" name="payment_method" value="upi">
                        <div class="payment-method-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <div class="payment-method-info">
                            <h4>Other UPI Apps</h4>
                            <p>Pay using any UPI app</p>
                        </div>
                    </div>
                </div>
                
                <button type="submit" name="process_payment" class="auth-btn" id="payButton">
                    <i class="fas fa-lock"></i> Pay ₹<?php echo number_format($orderData['total'], 2); ?> Securely
                </button>
            </form>
            
            <div class="auth-footer">
                <a href="checkout.php?step=form" class="back-home">
                    <i class="fas fa-arrow-left"></i> Back to Checkout
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Handle payment method selection
document.querySelectorAll('.payment-method').forEach(method => {
    method.addEventListener('click', function() {
        // Remove selected class from all methods
        document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('selected'));
        // Add selected class to clicked method
        this.classList.add('selected');
        // Check the radio button
        this.querySelector('input[type="radio"]').checked = true;
    });
});

// Form submission
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    const payButton = document.getElementById('payButton');
    payButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing Payment...';
    payButton.disabled = true;
});
</script>

<?php include 'footer.php'; ?> 