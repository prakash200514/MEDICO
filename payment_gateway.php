<?php
session_start();
include 'db.php';

// Check if payment data is available
if (!isset($_SESSION['pending_order']) || empty($_SESSION['pending_order'])) {
    header('Location: checkout.php');
    exit();
}

$orderData = $_SESSION['pending_order'];
$payment_success = false;

// Handle payment processing
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['process_payment'])) {
    $payment_method = $_POST['payment_method'];
    $payment_success = false;
    
    // Validate payment details based on method
    switch($payment_method) {
        case 'credit_card':
            $card_number = $_POST['card_number'] ?? '';
            $expiry_date = $_POST['expiry_date'] ?? '';
            $cvv = $_POST['cvv'] ?? '';
            $cardholder_name = $_POST['cardholder_name'] ?? '';
            
            // Basic validation
            if (strlen(str_replace(' ', '', $card_number)) >= 16 && 
                preg_match('/^\d{2}\/\d{2}$/', $expiry_date) && 
                strlen($cvv) >= 3 && 
                !empty($cardholder_name)) {
                $payment_success = true;
            }
            break;
            
        case 'upi':
            $upi_id = $_POST['upi_id'] ?? '';
            if (!empty($upi_id) && strpos($upi_id, '@') !== false) {
                $payment_success = true;
            }
            break;
            
        case 'net_banking':
            $bank_name = $_POST['bank_name'] ?? '';
            if (!empty($bank_name)) {
                $payment_success = true;
            }
            break;
    }
    
    if (!$payment_success) {
        $error_message = "Payment validation failed. Please check your details and try again.";
    }
    
    // Save order to database
    $customer_name = $orderData['customer_name'];
    $customer_email = $orderData['customer_email'];
    $customer_address = $orderData['customer_address'];
    $customer_phone = $orderData['customer_phone'];
    $payment = 'Online Payment';
    $order_date = date('Y-m-d');
    $delivery_date = date('Y-m-d', strtotime($order_date . ' +6 days'));
    
    foreach ($orderData['cart'] as $id => $qty) {
        $res = $conn->query("SELECT * FROM products WHERE id=$id");
        if ($row = $res->fetch_assoc()) {
            $subtotal = $row['price'] * $qty;
            $conn->query("INSERT INTO orders (customer_name, customer_email, product_id, quantity, total_price, payment_method, address, phone, order_date, delivery_date) VALUES ('$customer_name', '$customer_email', $id, $qty, $subtotal, '$payment', '$customer_address', '$customer_phone', '$order_date', '$delivery_date')");
        }
    }
    
    // Set order data for success page
    $_SESSION['last_order'] = [
        'customer_name' => $customer_name,
        'customer_email' => $customer_email,
        'customer_address' => $customer_address,
        'customer_phone' => $customer_phone,
        'payment' => $payment,
        'products' => $orderData['products'],
        'total' => $orderData['total'],
        'order_date' => $order_date,
        'delivery_date' => $delivery_date
    ];
    
    // Clear cart and pending order
    $_SESSION["cart"] = [];
    unset($_SESSION['pending_order']);
    
    // Redirect to success page
    header('Location: checkout.php?step=submit&payment=online');
    exit();
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
.payment-details-form {
    margin-top: 1.5rem;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 15px;
    border: 2px solid #e9ecef;
}
.form-row {
    display: flex;
    gap: 1rem;
}
.form-group input, .form-group select {
    width: 100%;
    padding: 0.8rem;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
    font-family: 'Poppins', sans-serif;
}
.form-group input:focus, .form-group select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}
.form-group input[type="password"] {
    letter-spacing: 0.3em;
}
.form-group select {
    background: white;
    cursor: pointer;
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
                <h2>Payment Gateway</h2>
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
            
            <form method="post" class="auth-form" id="paymentForm">
                <div class="payment-methods">
                    <h3 style="margin-bottom:1rem;"><i class="fas fa-credit-card"></i> Select Payment Method</h3>
                    
                    <div class="payment-method selected" data-method="card">
                        <input type="radio" name="payment_method" value="credit_card" checked>
                        <div class="payment-method-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <div class="payment-method-info">
                            <h4>Credit/Debit Card</h4>
                            <p>Pay securely with your card</p>
                        </div>
                    </div>
                    
                    <div class="payment-method" data-method="upi">
                        <input type="radio" name="payment_method" value="upi">
                        <div class="payment-method-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <div class="payment-method-info">
                            <h4>UPI Payment</h4>
                            <p>Pay using UPI apps</p>
                        </div>
                    </div>
                    
                    <div class="payment-method" data-method="net_banking">
                        <input type="radio" name="payment_method" value="net_banking">
                        <div class="payment-method-icon">
                            <i class="fas fa-university"></i>
                        </div>
                        <div class="payment-method-info">
                            <h4>Net Banking</h4>
                            <p>Pay through your bank</p>
                        </div>
                    </div>
                </div>
                
                <!-- Card Payment Form -->
                <div id="cardPaymentForm" class="payment-details-form">
                    <h3 style="margin:1.5rem 0 1rem 0;"><i class="fas fa-credit-card"></i> Card Details</h3>
                    
                    <div class="form-group">
                        <label>
                            <i class="fas fa-credit-card"></i> Card Number
                        </label>
                        <input type="text" name="card_number" id="cardNumber" placeholder="1234 5678 9012 3456" maxlength="19" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group" style="flex: 1; margin-right: 1rem;">
                            <label>
                                <i class="fas fa-calendar"></i> Expiry Date
                            </label>
                            <input type="text" name="expiry_date" id="expiryDate" placeholder="MM/YY" maxlength="5" required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>
                                <i class="fas fa-lock"></i> CVV
                            </label>
                            <input type="password" name="cvv" id="cvv" placeholder="123" maxlength="4" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <i class="fas fa-user"></i> Cardholder Name
                        </label>
                        <input type="text" name="cardholder_name" placeholder="Name on Card" required>
                    </div>
                </div>
                
                <!-- UPI Payment Form -->
                <div id="upiPaymentForm" class="payment-details-form" style="display: none;">
                    <h3 style="margin:1.5rem 0 1rem 0;"><i class="fas fa-mobile-alt"></i> UPI Details</h3>
                    
                    <div class="form-group">
                        <label>
                            <i class="fas fa-mobile-alt"></i> UPI ID
                        </label>
                        <input type="text" name="upi_id" placeholder="username@upi" required>
                    </div>
                </div>
                
                <!-- Net Banking Form -->
                <div id="netBankingForm" class="payment-details-form" style="display: none;">
                    <h3 style="margin:1.5rem 0 1rem 0;"><i class="fas fa-university"></i> Net Banking</h3>
                    
                    <div class="form-group">
                        <label>
                            <i class="fas fa-university"></i> Select Bank
                        </label>
                        <select name="bank_name" required>
                            <option value="">Select your bank</option>
                            <option value="sbi">State Bank of India</option>
                            <option value="hdfc">HDFC Bank</option>
                            <option value="icici">ICICI Bank</option>
                            <option value="axis">Axis Bank</option>
                            <option value="kotak">Kotak Mahindra Bank</option>
                            <option value="pnb">Punjab National Bank</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" name="process_payment" class="auth-btn" id="payButton">
                    <i class="fas fa-lock"></i> Pay ₹<?php echo number_format($orderData['total'], 2); ?>
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
        
        // Show/hide payment forms based on selection
        const selectedMethod = this.getAttribute('data-method');
        showPaymentForm(selectedMethod);
    });
});

// Function to show/hide payment forms
function showPaymentForm(method) {
    // Hide all payment forms
    document.getElementById('cardPaymentForm').style.display = 'none';
    document.getElementById('upiPaymentForm').style.display = 'none';
    document.getElementById('netBankingForm').style.display = 'none';
    
    // Show selected payment form
    switch(method) {
        case 'card':
            document.getElementById('cardPaymentForm').style.display = 'block';
            break;
        case 'upi':
            document.getElementById('upiPaymentForm').style.display = 'block';
            break;
        case 'net_banking':
            document.getElementById('netBankingForm').style.display = 'block';
            break;
    }
}

// Card number formatting
document.getElementById('cardNumber').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
    let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
    e.target.value = formattedValue;
});

// Expiry date formatting
document.getElementById('expiryDate').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
    }
    e.target.value = value;
});

// CVV validation
document.getElementById('cvv').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    e.target.value = value;
});

// Form validation
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    const selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;
    
    if (selectedMethod === 'credit_card') {
        const cardNumber = document.getElementById('cardNumber').value.replace(/\s/g, '');
        const expiryDate = document.getElementById('expiryDate').value;
        const cvv = document.getElementById('cvv').value;
        const cardholderName = document.querySelector('input[name="cardholder_name"]').value;
        
        if (cardNumber.length < 16) {
            alert('Please enter a valid 16-digit card number');
            e.preventDefault();
            return;
        }
        
        if (!expiryDate.match(/^\d{2}\/\d{2}$/)) {
            alert('Please enter expiry date in MM/YY format');
            e.preventDefault();
            return;
        }
        
        if (cvv.length < 3) {
            alert('Please enter a valid CVV');
            e.preventDefault();
            return;
        }
        
        if (cardholderName.trim() === '') {
            alert('Please enter cardholder name');
            e.preventDefault();
            return;
        }
    }
    
    // Show processing message
    const payButton = document.getElementById('payButton');
    payButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing Payment...';
    payButton.disabled = true;
});
</script>

<?php include 'footer.php'; ?> 