<?php
session_start();
include 'db.php';

// Check if user is logged in; if not, redirect to signup with redirect param
if (!isset($_SESSION['user_id'])) {
    header('Location: signup.php?redirect=checkout');
    exit;
}

// Check if orders table has required columns
$required_columns = ['customer_name', 'customer_email', 'product_id', 'quantity', 'total_price', 'payment_method', 'address', 'phone', 'order_date', 'delivery_date', 'prescription_path'];
$missing_columns = [];

foreach ($required_columns as $column) {
    $result = $conn->query("SHOW COLUMNS FROM orders LIKE '$column'");
    if ($result->num_rows == 0) {
        $missing_columns[] = $column;
    }
}

if (!empty($missing_columns)) {
    echo "<div style='text-align: center; padding: 50px; font-family: Arial, sans-serif;'>";
    echo "<h2>Database Configuration Required</h2>";
    echo "<p>The database needs to be updated. Please run the database fix script first.</p>";
    echo "<p><a href='fix_database_complete.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Fix Database</a></p>";
    echo "</div>";
    exit;
}

$cartIsEmpty = empty($_SESSION['cart']) || count($_SESSION['cart']) === 0;

$orderPlaced = false;
$invoice = [];

$step = isset($_GET['step']) ? $_GET['step'] : 'cart';

// Check if user came from cart page (proper flow)
$fromCart = isset($_GET['from_cart']) && $_GET['from_cart'] === 'true';

// Clear existing prescriptions for this user (only allow new uploads)
if (isset($_SESSION['user_email'])) {
    $user_email = $_SESSION['user_email'];
    
    // Get existing prescription files to delete them
    $prescriptions_result = $conn->query("SELECT file_path FROM prescriptions WHERE user_email = '$user_email'");
    while ($row = $prescriptions_result->fetch_assoc()) {
        $file_path = $row['file_path'];
        if (file_exists($file_path)) {
            unlink($file_path); // Delete the physical file
        }
    }
    
    // Delete all prescription records for this user
    $conn->query("DELETE FROM prescriptions WHERE user_email = '$user_email'");
}

// If user directly accesses checkout without going through cart, redirect to cart
if (!$fromCart && $step === 'cart' && !$cartIsEmpty) {
    header("Location: cart.php");
    exit;
}

// If user directly accesses checkout form without going through cart, redirect to cart
if (!$fromCart && $step === 'form' && !$cartIsEmpty) {
    header("Location: cart.php");
    exit;
}

if ($step === 'submit' && $_SERVER["REQUEST_METHOD"] == "POST" && !$cartIsEmpty) {
    $customer_name = $_POST['customer_name'];
    $customer_email = $_POST['customer_email'];
    $customer_address = $_POST['customer_address'];
    $customer_phone = $_POST['customer_phone'];
    $payment = $_POST['payment'];
    $total = 0;
    $products = [];
    $order_date = date('Y-m-d');
    $delivery_date = date('Y-m-d', strtotime($order_date . ' +6 days'));
    
    // Handle new prescription upload
    $new_prescription_path = null;
    if (isset($_FILES['new_prescription']) && $_FILES['new_prescription']['error'] == 0) {
        $file = $_FILES['new_prescription'];
        $filename = $file['name'];
        $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $filesize = $file['size'];
        
        // Validate file type
        $allowed_types = ['jpg', 'jpeg', 'png', 'pdf'];
        if (in_array($filetype, $allowed_types) && $filesize <= 5 * 1024 * 1024) {
            // Create uploads directory if it doesn't exist
            $upload_dir = "uploads/prescriptions/";
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generate unique filename
            $unique_filename = time() . '_' . $customer_email . '_' . $filename;
            $filepath = $upload_dir . $unique_filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Save to database
                $conn->query("INSERT INTO prescriptions (user_email, file_path) VALUES ('$customer_email', '$filepath')");
                $new_prescription_path = $filepath;
                // If image, attempt to create a thumbnail to speed up previews
                $image_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                if (in_array($image_ext, ['jpg','jpeg','png'])) {
                    // Create thumbnail file path
                    $thumb_path = $upload_dir . 'thumb_' . $unique_filename;
                    // Use GD if available
                    if (function_exists('imagecreatefromjpeg') || function_exists('imagecreatefrompng')) {
                        try {
                            if (in_array($image_ext, ['jpg','jpeg'])) {
                                $src_img = imagecreatefromjpeg($filepath);
                            } else {
                                $src_img = imagecreatefrompng($filepath);
                            }

                            if ($src_img) {
                                $src_w = imagesx($src_img);
                                $src_h = imagesy($src_img);
                                $max = 200; // max thumbnail dimension
                                if ($src_w > $src_h) {
                                    $thumb_w = $max;
                                    $thumb_h = intval($src_h * ($max / $src_w));
                                } else {
                                    $thumb_h = $max;
                                    $thumb_w = intval($src_w * ($max / $src_h));
                                }

                                $thumb_img = imagecreatetruecolor($thumb_w, $thumb_h);
                                // Preserve PNG transparency
                                if ($image_ext === 'png') {
                                    imagealphablending($thumb_img, false);
                                    imagesavealpha($thumb_img, true);
                                    $transparent = imagecolorallocatealpha($thumb_img, 255, 255, 255, 127);
                                    imagefilledrectangle($thumb_img, 0, 0, $thumb_w, $thumb_h, $transparent);
                                }

                                imagecopyresampled($thumb_img, $src_img, 0, 0, 0, 0, $thumb_w, $thumb_h, $src_w, $src_h);

                                if ($image_ext === 'png') {
                                    imagepng($thumb_img, $thumb_path, 6);
                                } else {
                                    imagejpeg($thumb_img, $thumb_path, 80);
                                }

                                imagedestroy($thumb_img);
                                imagedestroy($src_img);
                            }
                        } catch (Exception $e) {
                            // silently ignore thumbnail creation errors
                        }
                    }
                }
            }
        }
    }

    // Calculate total and prepare products array
    foreach ($_SESSION['cart'] as $id => $qty) {
        $res = $conn->query("SELECT * FROM products WHERE id=$id");
        if ($row = $res->fetch_assoc()) {
            $subtotal = $row['price'] * $qty;
            $total += $subtotal;
            $products[] = [
                'name' => $row['name'],
                'price' => $row['price'],
                'qty' => $qty,
                'subtotal' => $subtotal
            ];
        }
    }

    // Check stock availability and decrease stock quantities
    $stock_errors = [];
    foreach ($_SESSION['cart'] as $id => $qty) {
        $res = $conn->query("SELECT * FROM products WHERE id=$id");
        if ($row = $res->fetch_assoc()) {
            if ($row['stock'] < $qty) {
                $stock_errors[] = "Insufficient stock for {$row['name']}. Available: {$row['stock']}, Requested: $qty";
            }
        }
    }
    
    // If there are stock errors, don't process the order
    if (!empty($stock_errors)) {
        $error = implode("<br>", $stock_errors);
    } else {
        // Process order for all payment methods
        foreach ($_SESSION['cart'] as $id => $qty) {
            $res = $conn->query("SELECT * FROM products WHERE id=$id");
            if ($row = $res->fetch_assoc()) {
                $subtotal = $row['price'] * $qty;
                
                // Decrease stock quantity
                $new_stock = $row['stock'] - $qty;
                $conn->query("UPDATE products SET stock = $new_stock WHERE id = $id");
                
                // Determine prescription path (only new uploads)
                $prescription_path = $new_prescription_path;
                
                $insert_sql = "INSERT INTO orders (customer_name, customer_email, product_id, quantity, total_price, payment_method, address, phone, order_date, delivery_date, prescription_path) VALUES ('$customer_name', '$customer_email', $id, $qty, $subtotal, '$payment', '$customer_address', '$customer_phone', '$order_date', '$delivery_date', " . ($prescription_path ? "'$prescription_path'" : "NULL") . ")";
                
                if (!$conn->query($insert_sql)) {
                    $stock_errors[] = "Database error: " . $conn->error . ". Please contact support.";
                }
            }
        }
        
        $invoice = [
            'customer_name' => $customer_name,
            'customer_email' => $customer_email,
            'customer_address' => $customer_address,
            'customer_phone' => $customer_phone,
            'payment' => $payment,
            'products' => $products,
            'total' => $total,
            'order_date' => $order_date,
            'delivery_date' => $delivery_date
        ];
        $_SESSION["cart"] = [];
        $orderPlaced = true;
    }
    
    $invoice = [
        'customer_name' => $customer_name,
        'customer_email' => $customer_email,
        'customer_address' => $customer_address,
        'customer_phone' => $customer_phone,
        'payment' => $payment,
        'products' => $products,
        'total' => $total,
        'order_date' => $order_date,
        'delivery_date' => $delivery_date
    ];
    $_SESSION["cart"] = [];
    $orderPlaced = true;
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
    max-width: 400px;
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
.auth-form {
    text-align: left;
}
.form-group {
    margin-bottom: 1.5rem;
}
.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #333;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.form-group label i {
    color: #667eea;
}
.form-group input[type="radio"] {
    margin-right: 8px;
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

.prescription-section {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1rem;
    margin-top: 0.5rem;
}

.prescription-options {
    margin-bottom: 1rem;
}

.prescription-option {
    background: white;
    border-radius: 8px;
    padding: 0.8rem;
    margin-bottom: 0.5rem;
    border: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
}

.prescription-option:hover {
    background: #f0f4ff;
}

.upload-new-prescription {
    border-top: 1px solid #e2e8f0;
    padding-top: 1rem;
}

.btn-upload-prescription {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: #e3eafc;
    color: #667eea;
    text-decoration: none;
    border-radius: 5px;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-upload-prescription:hover {
    background: #c3dafe;
    color: #5a6fd8;
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
.invoice-container {
    max-width: 500px;
    margin: 50px auto;
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.12);
    padding: 2.5rem 2rem;
    text-align: center;
    font-family: 'Poppins', sans-serif;
    position: relative;
}

.invoice-header {
    color: #1e90ff;
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    letter-spacing: 1px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.invoice-container .shop-name {
    font-size: 1.5rem;
    font-weight: 800;
    color: #4e54c8;
    margin-bottom: 0.5rem;
    letter-spacing: 2px;
}

.invoice-info {
    margin-bottom: 1.5rem;
    color: #444;
    font-size: 1rem;
    text-align: left;
    background: #f7f9fb;
    border-radius: 10px;
    padding: 1rem 1.5rem;
    box-shadow: 0 2px 8px rgba(30,144,255,0.04);
}

.invoice-info strong {
    color: #222;
    font-weight: 600;
}

.invoice-info span {
    display: block;
    margin-top: 0.5rem;
    color: #28a745;
    font-weight: 600;
    font-size: 1.1rem;
}

.invoice-table {
    width: 100%;
    margin: 1.5rem 0;
    border-collapse: collapse;
    background: #f7f9fb;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(30,144,255,0.04);
}

.invoice-table th, .invoice-table td {
    padding: 12px 10px;
    border-bottom: 1px solid #eaeaea;
    text-align: center;
    font-size: 1rem;
}

.invoice-table th {
    background: #e3eafc;
    color: #333;
    font-weight: 700;
    border-bottom: 2px solid #c3dafe;
}

.invoice-table tr:last-child td {
    border-bottom: none;
}

.invoice-total {
    font-size: 1.3rem;
    font-weight: bold;
    color: #28a745;
    margin-top: 1rem;
    margin-bottom: 1.5rem;
    letter-spacing: 1px;
}

.invoice-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 1rem 2rem;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    margin-top: 1.2rem;
    text-decoration: none;
    display: inline-block;
    transition: background 0.3s, box-shadow 0.3s;
    box-shadow: 0 4px 16px rgba(102, 126, 234, 0.08);
}

.invoice-btn:hover {
    background: #5a6fd8;
    box-shadow: 0 8px 24px rgba(102, 126, 234, 0.18);
}

@media (max-width: 600px) {
    .invoice-container {
        padding: 1.2rem 0.5rem;
    }
    .invoice-info {
        padding: 0.7rem 0.5rem;
    }
}
@media print {
    body * {
        visibility: hidden;
    }
    .invoice-container, .invoice-container * {
        visibility: visible;
    }
    .invoice-container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100vw;
        box-shadow: none;
        margin: 0;
        padding: 0;
    }
    .invoice-btn, .invoice-btn * {
        display: none !important;
    }
}
</style>

<?php if ($cartIsEmpty): ?>
    <div class="invoice-container">
        <div style="color: #dc3545; font-size: 1.2rem; font-weight: bold; margin-bottom: 1rem;">
            Your cart is empty. Please add products before checking out.
        </div>
        <a href="products.php" class="invoice-btn"><i class="fas fa-arrow-left"></i> Go to Products</a>
    </div>
<?php elseif (!$fromCart && !$cartIsEmpty): ?>
    <div class="invoice-container">
        <div style="color: #ffc107; font-size: 1.2rem; font-weight: bold; margin-bottom: 1rem;">
            <i class="fas fa-exclamation-triangle"></i> Please review your cart first before proceeding to checkout.
        </div>
        <a href="cart.php" class="invoice-btn"><i class="fas fa-shopping-cart"></i> Go to Cart</a>
    </div>
<?php elseif ($orderPlaced): ?>
    <!-- Order Success Sound -->
    <audio id="orderSuccessSound" preload="auto">
        <source src="https://www.soundjay.com/misc/sounds/fail-buzzer-02.wav" type="audio/wav">
        <source src="https://www.soundjay.com/misc/sounds/fail-buzzer-02.mp3" type="audio/mpeg">
    </audio>
    <audio id="successChime" preload="auto">
        <source src="https://www.soundjay.com/misc/sounds/bell-ringing-05.wav" type="audio/wav">
        <source src="https://www.soundjay.com/misc/sounds/bell-ringing-05.mp3" type="audio/mpeg">
    </audio>
    
    <!-- Celebration Confetti Container -->
    <div id="confetti-container" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 9999; overflow: hidden;"></div>
    <script>
        // Play powerful success sound and celebration effects when order is placed
        window.addEventListener('load', function() {
            // Create a more powerful audio experience
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            
            // Function to create a powerful success sound
            function playPowerfulSuccessSound() {
                try {
                    // Create multiple oscillators for a rich sound
                    const oscillator1 = audioContext.createOscillator();
                    const oscillator2 = audioContext.createOscillator();
                    const oscillator3 = audioContext.createOscillator();
                    const gainNode = audioContext.createGain();
                    
                    // Connect all oscillators to gain node
                    oscillator1.connect(gainNode);
                    oscillator2.connect(gainNode);
                    oscillator3.connect(gainNode);
                    gainNode.connect(audioContext.destination);
                    
                    // Set different frequencies for a chord-like effect
                    oscillator1.frequency.setValueAtTime(523.25, audioContext.currentTime); // C5
                    oscillator2.frequency.setValueAtTime(659.25, audioContext.currentTime); // E5
                    oscillator3.frequency.setValueAtTime(783.99, audioContext.currentTime); // G5
                    
                    // Set oscillator types
                    oscillator1.type = 'sine';
                    oscillator2.type = 'triangle';
                    oscillator3.type = 'sine';
                    
                    // Create a dramatic volume envelope
                    gainNode.gain.setValueAtTime(0, audioContext.currentTime);
                    gainNode.gain.linearRampToValueAtTime(0.8, audioContext.currentTime + 0.1);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 1.5);
                    
                    // Start all oscillators
                    oscillator1.start(audioContext.currentTime);
                    oscillator2.start(audioContext.currentTime);
                    oscillator3.start(audioContext.currentTime);
                    
                    // Stop oscillators
                    oscillator1.stop(audioContext.currentTime + 1.5);
                    oscillator2.stop(audioContext.currentTime + 1.5);
                    oscillator3.stop(audioContext.currentTime + 1.5);
                    
                    // Add a second chord after a short delay
                    setTimeout(function() {
                        const osc4 = audioContext.createOscillator();
                        const osc5 = audioContext.createOscillator();
                        const gain2 = audioContext.createGain();
                        
                        osc4.connect(gain2);
                        osc5.connect(gain2);
                        gain2.connect(audioContext.destination);
                        
                        osc4.frequency.setValueAtTime(659.25, audioContext.currentTime); // E5
                        osc5.frequency.setValueAtTime(783.99, audioContext.currentTime); // G5
                        
                        osc4.type = 'sine';
                        osc5.type = 'triangle';
                        
                        gain2.gain.setValueAtTime(0, audioContext.currentTime);
                        gain2.gain.linearRampToValueAtTime(0.6, audioContext.currentTime + 0.1);
                        gain2.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 1.0);
                        
                        osc4.start(audioContext.currentTime);
                        osc5.start(audioContext.currentTime);
                        
                        osc4.stop(audioContext.currentTime + 1.0);
                        osc5.stop(audioContext.currentTime + 1.0);
                    }, 200);
                    
                    console.log('Powerful order success sound played!');
                    
                } catch (error) {
                    console.log('Powerful sound generation failed:', error);
                    // Fallback to simple audio
                    playFallbackSound();
                }
            }
            
            // Fallback function for simple audio
            function playFallbackSound() {
                const audio = document.getElementById('orderSuccessSound');
                if (audio) {
                    audio.volume = 0.7;
                    const playPromise = audio.play();
                    
                    if (playPromise !== undefined) {
                        playPromise.then(function() {
                            console.log('Fallback order success sound played');
                        }).catch(function(error) {
                            console.log('All audio methods failed:', error);
                        });
                    }
                }
            }
            
            // Confetti celebration effect
            function createConfetti() {
                const confettiContainer = document.getElementById('confetti-container');
                const colors = ['#ff6b6b', '#4ecdc4', '#45b7d1', '#96ceb4', '#feca57', '#ff9ff3', '#54a0ff', '#5f27cd'];
                const emojis = ['🎉', '🎊', '✨', '💫', '🌟', '🎈', '🎁', '🏆'];
                
                // Create multiple confetti pieces
                for (let i = 0; i < 150; i++) {
                    setTimeout(() => {
                        const confetti = document.createElement('div');
                        confetti.style.position = 'absolute';
                        confetti.style.left = Math.random() * 100 + '%';
                        confetti.style.top = '-20px';
                        confetti.style.width = Math.random() * 10 + 5 + 'px';
                        confetti.style.height = Math.random() * 10 + 5 + 'px';
                        confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                        confetti.style.borderRadius = Math.random() > 0.5 ? '50%' : '0';
                        confetti.style.transform = 'rotate(' + Math.random() * 360 + 'deg)';
                        confetti.style.animation = 'confetti-fall 3s linear forwards';
                        confetti.style.zIndex = '10000';
                        
                        // Add some emoji confetti
                        if (Math.random() > 0.7) {
                            confetti.style.fontSize = '20px';
                            confetti.style.backgroundColor = 'transparent';
                            confetti.textContent = emojis[Math.floor(Math.random() * emojis.length)];
                        }
                        
                        confettiContainer.appendChild(confetti);
                        
                        // Remove confetti after animation
                        setTimeout(() => {
                            if (confetti.parentNode) {
                                confetti.parentNode.removeChild(confetti);
                            }
                        }, 3000);
                    }, i * 20);
                }
            }
            
            // Add confetti animation CSS
            const style = document.createElement('style');
            style.textContent = `
                @keyframes confetti-fall {
                    0% {
                        transform: translateY(-20px) rotate(0deg);
                        opacity: 1;
                    }
                    100% {
                        transform: translateY(100vh) rotate(720deg);
                        opacity: 0;
                    }
                }
                
                @keyframes celebration-bounce {
                    0%, 20%, 50%, 80%, 100% {
                        transform: translateY(0);
                    }
                    40% {
                        transform: translateY(-30px);
                    }
                    60% {
                        transform: translateY(-15px);
                    }
                }
                
                .celebration-text {
                    animation: celebration-bounce 2s ease-in-out;
                    color: #28a745;
                    font-weight: bold;
                    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
                }
            `;
            document.head.appendChild(style);
            
            // Add celebration text effect
            function addCelebrationText() {
                const successText = document.querySelector('.invoice-container div[style*="color: #28a745"]');
                if (successText) {
                    successText.classList.add('celebration-text');
                    successText.innerHTML = '🎉 Order Placed Successfully! 🎉';
                }
            }
            
            // Start celebration effects
            setTimeout(() => {
                createConfetti();
                addCelebrationText();
            }, 500);
            
            // Try to play the powerful sound
            playPowerfulSuccessSound();
        });
    </script>
    <div class="invoice-container">
        <div style="color: #28a745; font-size: 1.2rem; font-weight: bold; margin-bottom: 1rem;">
            ✅ Order Placed Successfully!
        </div>
        <div class="invoice-header" style="margin-bottom:0.5rem;"><i class="fas fa-receipt"></i> Invoice</div>
        <div class="shop-name">Medico</div>
        <div class="invoice-info">
            <strong>Name:</strong> <?php echo htmlspecialchars($invoice['customer_name']); ?><br>
            <strong>Email:</strong> <?php echo htmlspecialchars($invoice['customer_email']); ?><br>
            <strong>Address:</strong> <?php echo htmlspecialchars($invoice['customer_address']); ?><br>
            <strong>Phone:</strong> <?php echo htmlspecialchars($invoice['customer_phone']); ?><br>
            <strong>Payment Method:</strong> <?php echo htmlspecialchars($invoice['payment']); ?><br>
            <strong>Order Date:</strong> <?php echo date('d M Y', strtotime($invoice['order_date'])); ?><br>
            <strong>Home Delivery Date:</strong> <?php echo date('d M Y', strtotime($invoice['delivery_date'])); ?> <br>
            <span>Coming to your home safely</span>
        </div>
        <table class="invoice-table">
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Qty</th>
                <th>Subtotal</th>
            </tr>
            <?php foreach ($invoice['products'] as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['name']); ?></td>
                <td>₹<?php echo number_format($item['price'], 2); ?></td>
                <td><?php echo $item['qty']; ?></td>
                <td>₹<?php echo number_format($item['subtotal'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <div class="invoice-total">Total: ₹<?php echo number_format($invoice['total'], 2); ?></div>
        <a href="products.php" class="invoice-btn"><i class="fas fa-arrow-left"></i> Continue Shopping</a>
        <a href="#" class="invoice-btn" onclick="window.print(); return false;">
            <i class="fas fa-download"></i> Download/Print Invoice
        </a>
    </div>
<?php elseif ($step === 'form'): ?>
<div class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-credit-card"></i>
                <h2>Checkout</h2>
                <p>Enter your details and payment method to complete your order</p>
            </div>
            
            <?php if (isset($error) && !empty($error)): ?>
                <div class="alert alert-error" style="background: #fee; color: #c33; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid #fcc;">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            <form method="post" action="checkout.php?step=submit&from_cart=true" class="auth-form">
                <div class="form-group">
                    <label>
                        <i class="fas fa-user"></i> Name
                    </label>
                    <input type="text" name="customer_name" required>
                </div>
                <div class="form-group">
                    <label>
                        <i class="fas fa-envelope"></i> Email
                    </label>
                    <input type="email" name="customer_email" required>
                </div>
                <div class="form-group">
                    <label>
                        <i class="fas fa-map-marker-alt"></i> Address
                    </label>
                    <input type="text" name="customer_address" required>
                </div>
                <div class="form-group">
                    <label>
                        <i class="fas fa-phone"></i> Phone
                    </label>
                    <input type="text" name="customer_phone" required>
                </div>
                <div class="form-group">
                    <label>
                        <i class="fas fa-money-bill-wave"></i> Select Payment Method
                    </label>
                    <input type="radio" name="payment" value="COD" required> Cash on Delivery<br>
                    <input type="radio" name="payment" value="Online"> Online Payment
                </div>
                
                <!-- Prescription Upload Section -->
                <div class="form-group">
                    <label>
                        <i class="fas fa-file-medical"></i> Prescription (Optional)
                    </label>
                    <div class="prescription-section">
                        <div class="upload-new-prescription">
                            <h4 style="margin: 0 0 0.5rem 0; color: #333;">Upload your prescription:</h4>
                            <input type="file" name="new_prescription" accept=".jpg,.jpeg,.png,.pdf" style="margin-bottom: 0.5rem;">
                            <small style="color: #666; font-size: 0.8rem;">Accepted formats: JPG, PNG, PDF (Max size: 5MB)</small>
                            <p style="color: #28a745; font-size: 0.9rem; margin-top: 0.5rem;">
                                <i class="fas fa-info-circle"></i> 
                                <strong>Note:</strong> Only fresh prescriptions are accepted for each order. Previous prescriptions have been cleared.
                            </p>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="auth-btn">
                    <i class="fas fa-check"></i> Place Order
                </button>
            </form>
            <div class="auth-footer">
                <a href="cart.php" class="back-home">
                    <i class="fas fa-arrow-left"></i> Back to Cart
                </a>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-credit-card"></i>
                <h2>Checkout</h2>
                <p>Review your cart and proceed to place your order</p>
            </div>
            <?php
            // Show cart summary before the form
            if (!empty($_SESSION['cart'])):
                $cart_total = 0;
            ?>
                <h3 style="margin-bottom:1rem;">Your Cart</h3>
                <table class="invoice-table">
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                    </tr>
                    <?php
                    foreach ($_SESSION['cart'] as $id => $qty):
                        $res = $conn->query("SELECT * FROM products WHERE id=$id");
                        if ($row = $res->fetch_assoc()):
                            $subtotal = $row['price'] * $qty;
                            $cart_total += $subtotal;
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td>₹<?php echo number_format($row['price'], 2); ?></td>
                        <td><?php echo $qty; ?></td>
                        <td>₹<?php echo number_format($subtotal, 2); ?></td>
                    </tr>
                    <?php endif; endforeach; ?>
                </table>
                <div class="invoice-total" style="margin-bottom:2rem;">Total: ₹<?php echo number_format($cart_total, 2); ?></div>
                <a href="checkout.php?step=form&from_cart=true" class="auth-btn" style="margin-top:1.5rem;">
                    <i class="fas fa-arrow-right"></i> Place Order
                </a>
            <?php endif; ?>
            <div class="auth-footer">
                <a href="cart.php" class="back-home">
                    <i class="fas fa-arrow-left"></i> Back to Cart
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?php include 'footer.php'; ?>