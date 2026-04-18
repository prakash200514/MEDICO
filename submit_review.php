<?php
include 'db.php'; 
session_start();
$page_title = "Submit Review - Medico";
include 'header.php';

// Check if user is logged in
if (!isset($_SESSION['user_email'])) {
    header('Location: login.php');
    exit();
}

$order_id = $_GET['order_id'] ?? '';
$product_id = $_GET['product_id'] ?? '';

// Validate order belongs to user
if ($order_id && $product_id) {
    $stmt = $conn->prepare("SELECT o.*, p.name as product_name, p.image as product_image 
                           FROM orders o 
                           JOIN products p ON o.product_id = p.id 
                           WHERE o.id = ? AND o.customer_email = ?");
    $stmt->bind_param("is", $order_id, $_SESSION['user_email']);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    
    if (!$order) {
        echo "<div class='error-message'>Order not found or you don't have permission to review this order.</div>";
        include 'footer.php';
        exit();
    }
    
    // Check if review already exists
    $stmt = $conn->prepare("SELECT * FROM reviews WHERE order_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $order_id, $product_id);
    $stmt->execute();
    $existing_review = $stmt->get_result()->fetch_assoc();
    
    if ($existing_review) {
        echo "<div class='error-message'>You have already reviewed this product.</div>";
        include 'footer.php';
        exit();
    }
}

// Handle form submission
if ($_POST) {
    $order_id = $_POST['order_id'];
    $product_id = $_POST['product_id'];
    $rating = $_POST['rating'];
    $review_text = $_POST['review_text'];
    
    // Validate rating
    if ($rating < 1 || $rating > 5) {
        $error = "Please select a valid rating.";
    } else {
        // Insert review
        $stmt = $conn->prepare("INSERT INTO reviews (order_id, product_id, user_email, rating, review_text, is_verified_purchase) VALUES (?, ?, ?, ?, ?, ?)");
        $is_verified = 1; // Since they have an order, it's verified
        $stmt->bind_param("iisisi", $order_id, $product_id, $_SESSION['user_email'], $rating, $review_text, $is_verified);
        
        if ($stmt->execute()) {
            $success = "Thank you for your review! It will be displayed after admin approval.";
        } else {
            $error = "Error submitting review. Please try again.";
        }
    }
}
?>

<div class="review-page">
    <div class="page-header">
        <h1><i class="fas fa-star"></i> Submit Review</h1>
        <p>Share your experience with this product</p>
    </div>

    <?php if (isset($success)): ?>
        <div class="success-message">
            <i class="fas fa-check-circle"></i>
            <?php echo $success; ?>
            <a href="products.php" class="btn btn-primary">Continue Shopping</a>
        </div>
    <?php else: ?>
        <?php if (isset($order)): ?>
            <div class="product-review-section">
                <div class="product-info-card">
                    <div class="product-image">
                        <img src="img/<?php echo $order['product_image']; ?>" alt="<?php echo $order['product_name']; ?>">
                    </div>
                    <div class="product-details">
                        <h3><?php echo $order['product_name']; ?></h3>
                        <p class="order-info">Order #<?php echo $order['id']; ?> • <?php echo date('M d, Y', strtotime($order['order_date'])); ?></p>
                        <p class="quantity">Quantity: <?php echo $order['quantity']; ?></p>
                    </div>
                </div>

                <form method="POST" class="review-form">
                    <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                    
                    <div class="form-group">
                        <label>Rating *</label>
                        <div class="star-rating">
                            <input type="radio" name="rating" value="5" id="star5" required>
                            <label for="star5" class="star">★</label>
                            <input type="radio" name="rating" value="4" id="star4">
                            <label for="star4" class="star">★</label>
                            <input type="radio" name="rating" value="3" id="star3">
                            <label for="star3" class="star">★</label>
                            <input type="radio" name="rating" value="2" id="star2">
                            <label for="star2" class="star">★</label>
                            <input type="radio" name="rating" value="1" id="star1">
                            <label for="star1" class="star">★</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="review_text">Your Review</label>
                        <textarea name="review_text" id="review_text" rows="5" placeholder="Share your experience with this product..." maxlength="500"></textarea>
                        <div class="char-count">0/500 characters</div>
                    </div>

                    <?php if (isset($error)): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Submit Review
                        </button>
                        <a href="products.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="no-order-message">
                <i class="fas fa-shopping-cart"></i>
                <h3>No Order Found</h3>
                <p>Please make sure you're accessing this page from a valid order link.</p>
                <a href="products.php" class="btn btn-primary">Browse Products</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
.review-page {
    padding: 2rem 0;
    background: transparent;
    min-height: 100vh;
}

.page-header {
    text-align: center;
    margin-bottom: 3rem;
    padding: 2rem 0;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    margin: 0 2rem 3rem 2rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.page-header h1 {
    font-size: 3rem;
    color: white;
    margin-bottom: 0.5rem;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    font-weight: 800;
    letter-spacing: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
}

.page-header h1 i {
    color: #ffd700;
    font-size: 2.5rem;
}

.page-header p {
    color: white;
    font-size: 1.3rem;
    font-weight: 300;
}

.product-review-section {
    max-width: 800px;
    margin: 0 auto;
    padding: 0 2rem;
}

.product-info-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 20px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    gap: 2rem;
}

.product-image {
    width: 120px;
    height: 120px;
    border-radius: 15px;
    overflow: hidden;
    flex-shrink: 0;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-details h3 {
    color: #2c3e50;
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    font-weight: 700;
}

.order-info {
    color: #667eea;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.quantity {
    color: #7f8c8d;
    font-size: 0.95rem;
}

.review-form {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.form-group {
    margin-bottom: 2rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.8rem;
    font-weight: 600;
    color: #2c3e50;
    font-size: 1.1rem;
}

.star-rating {
    display: flex;
    flex-direction: row-reverse;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.star-rating input[type="radio"] {
    display: none;
}

.star-rating label {
    font-size: 2.5rem;
    color: #ddd;
    cursor: pointer;
    transition: color 0.2s ease;
    margin: 0;
    padding: 0;
}

.star-rating label:hover,
.star-rating label:hover ~ label,
.star-rating input[type="radio"]:checked ~ label {
    color: #ffd700;
}

.star-rating input[type="radio"]:checked ~ label {
    text-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
}

textarea {
    width: 100%;
    padding: 1rem 1.5rem;
    border: 2px solid #e1e8ed;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.9);
    resize: vertical;
    min-height: 120px;
    font-family: inherit;
}

textarea:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    background: white;
}

.char-count {
    text-align: right;
    color: #7f8c8d;
    font-size: 0.9rem;
    margin-top: 0.5rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
}

.btn {
    padding: 1rem 2rem;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    cursor: pointer;
    font-size: 1rem;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
    color: white;
}

.btn-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 35px rgba(149, 165, 166, 0.4);
}

.success-message, .error-message {
    max-width: 600px;
    margin: 2rem auto;
    padding: 2rem;
    border-radius: 20px;
    text-align: center;
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.success-message {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    color: #155724;
    border-color: #c3e6cb;
}

.error-message {
    background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
    color: #721c24;
    border-color: #f5c6cb;
}

.success-message i, .error-message i {
    font-size: 2rem;
    margin-bottom: 1rem;
    display: block;
}

.no-order-message {
    text-align: center;
    background: rgba(255, 255, 255, 0.95);
    padding: 3rem;
    border-radius: 25px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    max-width: 500px;
    margin: 2rem auto;
}

.no-order-message i {
    font-size: 4rem;
    color: #95a5a6;
    margin-bottom: 1.5rem;
}

.no-order-message h3 {
    color: #2c3e50;
    font-size: 2rem;
    margin-bottom: 1rem;
    font-weight: 700;
}

.no-order-message p {
    color: #7f8c8d;
    font-size: 1.1rem;
    margin-bottom: 2rem;
    line-height: 1.6;
}

/* Responsive Design */
@media (max-width: 768px) {
    .page-header {
        margin: 0 1rem 2rem 1rem;
        padding: 1.5rem 0;
    }
    
    .page-header h1 {
        font-size: 2rem;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .product-review-section {
        padding: 0 1rem;
    }
    
    .product-info-card {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .product-image {
        width: 100px;
        height: 100px;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .page-header h1 {
        font-size: 1.8rem;
    }
    
    .review-form, .product-info-card {
        padding: 1.5rem;
    }
    
    .star-rating label {
        font-size: 2rem;
    }
}
</style>

<script>
// Character counter for review text
document.getElementById('review_text').addEventListener('input', function() {
    const charCount = this.value.length;
    const maxLength = 500;
    const counter = document.querySelector('.char-count');
    
    counter.textContent = charCount + '/' + maxLength + ' characters';
    
    if (charCount > maxLength * 0.9) {
        counter.style.color = '#e74c3c';
    } else if (charCount > maxLength * 0.7) {
        counter.style.color = '#f39c12';
    } else {
        counter.style.color = '#7f8c8d';
    }
});

// Star rating interaction
document.querySelectorAll('.star-rating input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const rating = this.value;
        console.log('Selected rating:', rating);
    });
});
</script>

<?php include 'footer.php'; ?>