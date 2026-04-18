<?php 
session_start();
include 'db.php';
$page_title = "Order Success - Medico";
include 'header.php';

// Get the latest order for the current user
$user_email = $_SESSION['user_email'] ?? '';
$latest_order = null;
$order_products = [];

if ($user_email) {
    // Check which column name is used in orders table
    $column_check = $conn->query("SHOW COLUMNS FROM orders LIKE 'customer_email'");
    $email_column = ($column_check && $column_check->num_rows > 0) ? 'customer_email' : 'user_email';
    
    $order_query = "SELECT * FROM orders WHERE $email_column = '$user_email' ORDER BY order_date DESC LIMIT 1";
    $order_result = $conn->query($order_query);
    
    if ($order_result && $order_result->num_rows > 0) {
        $latest_order = $order_result->fetch_assoc();
        
        // Get products from the order
        $product_query = "SELECT p.*, o.quantity, o.id as order_id FROM products p 
                         JOIN orders o ON p.id = o.product_id 
                         WHERE o.$email_column = '$user_email' 
                         AND o.order_date = '{$latest_order['order_date']}'";
        $product_result = $conn->query($product_query);
        
        if ($product_result) {
            while ($row = $product_result->fetch_assoc()) {
                $order_products[] = $row;
            }
        }
    }
}
?>

<div class="order-success-page">
    <div class="success-container">
        <div class="success-header">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>🎉 Order Placed Successfully!</h1>
            <p class="success-message">Thank you for your purchase from Medico. Your order has been confirmed and will be processed shortly.</p>
        </div>

        <?php if ($latest_order && !empty($order_products)): ?>
            <div class="order-details">
                <h2><i class="fas fa-receipt"></i> Order Details</h2>
                <div class="order-info">
                    <p><strong>Order ID:</strong> #<?php echo $latest_order['id']; ?></p>
                    <p><strong>Order Date:</strong> <?php echo date('F j, Y g:i A', strtotime($latest_order['order_time'])); ?></p>
                    <p><strong>Total Amount:</strong> ₹<?php echo $latest_order['total_price']; ?></p>
                </div>
                
                <div class="ordered-products">
                    <h3>Products Ordered:</h3>
                    <div class="products-list">
                        <?php foreach ($order_products as $product): ?>
                            <div class="product-item">
                                <img src="img/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                                <div class="product-details">
                                    <h4><?php echo $product['name']; ?></h4>
                                    <p>Quantity: <?php echo $product['quantity']; ?></p>
                                    <p>Price: ₹<?php echo $product['price']; ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        <?php endif; ?>

        <div class="action-buttons">
            <button class="continue-shopping-btn" onclick="showReviewModal()">
                <i class="fas fa-shopping-bag"></i> Continue Shopping
            </button>
            <button class="print-btn" onclick="showReviewModal()">
                <i class="fas fa-print"></i> Print Receipt
            </button>
            <a href="index.php" class="home-btn">
                <i class="fas fa-home"></i> Back to Home
            </a>
        </div>
    </div>
</div>

<!-- Review Modal -->
<div id="reviewModal" class="review-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-star"></i> Rate Your Purchase</h2>
            <p>We'd love to hear about your experience! Please take a moment to rate the products you purchased.</p>
            <button class="close-modal" onclick="closeReviewModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="modal-body">
            <?php if ($latest_order && !empty($order_products)): ?>
                <div class="modal-review-forms">
                    <?php foreach ($order_products as $product): 
                        // Check if review already exists for this product
                        $existing_review = null;
                        $reviews_table_exists = false;
                        
                        $table_check = $conn->query("SHOW TABLES LIKE 'reviews'");
                        if ($table_check && $table_check->num_rows > 0) {
                            $reviews_table_exists = true;
                            $review_stmt = $conn->prepare("SELECT * FROM reviews WHERE order_id = ? AND product_id = ?");
                            $review_stmt->bind_param("ii", $product['order_id'], $product['id']);
                            $review_stmt->execute();
                            $existing_review = $review_stmt->get_result()->fetch_assoc();
                        }
                    ?>
                        <div class="modal-review-item" data-order-id="<?php echo $product['order_id']; ?>" data-product-id="<?php echo $product['id']; ?>">
                            <div class="product-info">
                                <img src="img/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                                <div class="product-details">
                                    <h4><?php echo $product['name']; ?></h4>
                                    <p>Category: <?php echo $product['category']; ?></p>
                                </div>
                            </div>
                            
                            <?php if ($existing_review): ?>
                                <div class="review-already-submitted">
                                    <div class="submitted-rating">
                                        <span>Your Rating:</span>
                                        <div class="star-display">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <?php if ($i <= $existing_review['rating']): ?>
                                                    <i class="fas fa-star"></i>
                                                <?php else: ?>
                                                    <i class="far fa-star"></i>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                            <span class="rating-number"><?php echo $existing_review['rating']; ?>/5</span>
                                        </div>
                                    </div>
                                    <div class="review-status">
                                        <i class="fas fa-check-circle"></i> Already Reviewed
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="review-form">
                                    <div class="rating-section">
                                        <label>Your Rating:</label>
                                        <div class="star-rating">
                                            <input type="radio" name="rating_<?php echo $product['id']; ?>" value="5" id="modal_star5_<?php echo $product['id']; ?>">
                                            <label for="modal_star5_<?php echo $product['id']; ?>"><i class="fas fa-star"></i></label>
                                            <input type="radio" name="rating_<?php echo $product['id']; ?>" value="4" id="modal_star4_<?php echo $product['id']; ?>">
                                            <label for="modal_star4_<?php echo $product['id']; ?>"><i class="fas fa-star"></i></label>
                                            <input type="radio" name="rating_<?php echo $product['id']; ?>" value="3" id="modal_star3_<?php echo $product['id']; ?>">
                                            <label for="modal_star3_<?php echo $product['id']; ?>"><i class="fas fa-star"></i></label>
                                            <input type="radio" name="rating_<?php echo $product['id']; ?>" value="2" id="modal_star2_<?php echo $product['id']; ?>">
                                            <label for="modal_star2_<?php echo $product['id']; ?>"><i class="fas fa-star"></i></label>
                                            <input type="radio" name="rating_<?php echo $product['id']; ?>" value="1" id="modal_star1_<?php echo $product['id']; ?>">
                                            <label for="modal_star1_<?php echo $product['id']; ?>"><i class="fas fa-star"></i></label>
                                        </div>
                                    </div>
                                    
                                    <div class="review-text-section">
                                        <label for="modal_review_text_<?php echo $product['id']; ?>">Your Review (Optional):</label>
                                        <textarea name="review_text" id="modal_review_text_<?php echo $product['id']; ?>" 
                                                  placeholder="Share your experience with this product..." rows="3"></textarea>
                                    </div>
                                    
                                    <button type="button" class="submit-review-btn" onclick="submitModalReview(<?php echo $product['order_id']; ?>, <?php echo $product['id']; ?>)">
                                        <i class="fas fa-paper-plane"></i> Submit Review
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="modal-footer">
            <button class="skip-reviews-btn" onclick="skipReviews()">
                <i class="fas fa-forward"></i> Skip Reviews
            </button>
            <button class="continue-after-reviews-btn" onclick="continueAfterReviews()" style="display: none;">
                <i class="fas fa-check"></i> Continue
            </button>
        </div>
    </div>
</div>

<style>
.order-success-page {
    padding: 2rem 0;
    background: transparent;
    min-height: 100vh;
}

.success-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 0 2rem;
}

.success-header {
    text-align: center;
    margin-bottom: 3rem;
    padding: 3rem 2rem;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 25px;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.success-icon {
    margin-bottom: 1.5rem;
}

.success-icon i {
    font-size: 5rem;
    color: #27ae60;
    text-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
    animation: success-pulse 2s ease-in-out infinite;
}

@keyframes success-pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.success-header h1 {
    font-size: 3rem;
    color: white;
    margin-bottom: 1rem;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    font-weight: 800;
}

.success-message {
    color: white;
    font-size: 1.3rem;
    font-weight: 300;
    line-height: 1.6;
}

.order-details {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 20px;
    padding: 2.5rem;
    margin-bottom: 3rem;
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.order-details h2 {
    color: #2c3e50;
    font-size: 2rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.order-details h2 i {
    color: #667eea;
}

.order-info {
    background: rgba(102, 126, 234, 0.1);
    padding: 1.5rem;
    border-radius: 15px;
    margin-bottom: 2rem;
    border-left: 4px solid #667eea;
}

.order-info p {
    margin-bottom: 0.5rem;
    color: #2c3e50;
    font-size: 1.1rem;
}

.ordered-products h3 {
    color: #2c3e50;
    margin-bottom: 1.5rem;
    font-size: 1.5rem;
}

.products-list {
    display: grid;
    gap: 1.5rem;
}

.product-item {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 1.5rem;
    background: rgba(255, 255, 255, 0.8);
    border-radius: 15px;
    border: 1px solid rgba(102, 126, 234, 0.2);
    transition: all 0.3s ease;
}

.product-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.product-item img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 10px;
}

.product-details h4 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
    font-size: 1.2rem;
}

.product-details p {
    color: #7f8c8d;
    margin-bottom: 0.3rem;
}

.review-section {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 20px;
    padding: 2.5rem;
    margin-bottom: 3rem;
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.review-section h2 {
    color: #2c3e50;
    font-size: 2rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.review-section h2 i {
    color: #f39c12;
}

.review-section > p {
    color: #7f8c8d;
    font-size: 1.1rem;
    margin-bottom: 2rem;
    line-height: 1.6;
}

.review-forms {
    display: grid;
    gap: 2rem;
}

.review-form-container {
    background: rgba(255, 255, 255, 0.8);
    border-radius: 15px;
    padding: 2rem;
    border: 1px solid rgba(102, 126, 234, 0.2);
    transition: all 0.3s ease;
}

.review-form-container:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.product-review-header {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid rgba(102, 126, 234, 0.2);
}

.product-review-header img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 10px;
}

.product-info h4 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
    font-size: 1.3rem;
}

.product-info p {
    color: #7f8c8d;
    font-size: 1rem;
}

.rating-section {
    margin-bottom: 1.5rem;
}

.rating-section label {
    display: block;
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 1rem;
    font-size: 1.1rem;
}

.star-rating {
    display: flex;
    gap: 0.5rem;
    direction: rtl;
}

.star-rating input[type="radio"] {
    display: none;
}

.star-rating label {
    font-size: 2rem;
    color: #ddd;
    cursor: pointer;
    transition: color 0.2s ease;
    margin: 0;
}

.star-rating label:hover,
.star-rating label:hover ~ label,
.star-rating input[type="radio"]:checked ~ label {
    color: #f39c12;
}

.review-text-section {
    margin-bottom: 2rem;
}

.review-text-section label {
    display: block;
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 0.8rem;
    font-size: 1.1rem;
}

.review-text-section textarea {
    width: 100%;
    padding: 1rem;
    border: 2px solid #e1e8ed;
    border-radius: 12px;
    font-size: 1rem;
    font-family: 'Poppins', sans-serif;
    resize: vertical;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.9);
}

.review-text-section textarea:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    background: white;
}

.submit-review-btn {
    width: 100%;
    padding: 1rem 2rem;
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 1.1rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 8px 25px rgba(243, 156, 18, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.submit-review-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 35px rgba(243, 156, 18, 0.4);
}

.submit-review-btn:disabled {
    background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.review-submitted {
    padding: 1.5rem;
    background: rgba(39, 174, 96, 0.1);
    border-radius: 12px;
    border: 1px solid rgba(39, 174, 96, 0.3);
}

.submitted-rating {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.submitted-rating span {
    color: #2c3e50;
    font-weight: 600;
}

.star-display {
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.star-display i {
    color: #f39c12;
    font-size: 1.2rem;
}

.rating-number {
    color: #2c3e50;
    font-weight: 600;
    margin-left: 0.5rem;
}

.submitted-review {
    margin-bottom: 1rem;
}

.submitted-review strong {
    color: #2c3e50;
    display: block;
    margin-bottom: 0.5rem;
}

.submitted-review p {
    color: #7f8c8d;
    line-height: 1.6;
    margin: 0;
    background: rgba(255, 255, 255, 0.7);
    padding: 1rem;
    border-radius: 8px;
}

.review-status {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #27ae60;
    font-weight: 600;
    font-size: 1rem;
}

.review-status i {
    font-size: 1.2rem;
}

.reviews-not-available {
    margin: 2rem 0;
}

.info-message {
    background: rgba(52, 152, 219, 0.1);
    border: 1px solid rgba(52, 152, 219, 0.3);
    border-radius: 15px;
    padding: 2rem;
    text-align: center;
}

.info-message i {
    font-size: 3rem;
    color: #3498db;
    margin-bottom: 1rem;
    display: block;
}

.info-message strong {
    color: #2c3e50;
    font-size: 1.3rem;
    display: block;
    margin-bottom: 1rem;
}

.info-message p {
    color: #7f8c8d;
    line-height: 1.6;
    margin-bottom: 0.5rem;
}

/* Modal Styles */
.review-modal {
    display: none;
    position: fixed;
    z-index: 10000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(5px);
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-content {
    background: rgba(255, 255, 255, 0.98);
    margin: 2% auto;
    padding: 0;
    border-radius: 20px;
    width: 90%;
    max-width: 800px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: slideIn 0.3s ease;
    position: relative;
}

@keyframes slideIn {
    from { 
        opacity: 0;
        transform: translateY(-50px) scale(0.9);
    }
    to { 
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 20px 20px 0 0;
    position: relative;
}

.modal-header h2 {
    margin: 0 0 0.5rem 0;
    font-size: 2rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.modal-header p {
    margin: 0;
    opacity: 0.9;
    font-size: 1.1rem;
}

.close-modal {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    font-size: 1.5rem;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.close-modal:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.1);
}

.modal-body {
    padding: 2rem;
    max-height: 60vh;
    overflow-y: auto;
}

.modal-review-forms {
    display: grid;
    gap: 2rem;
}

.modal-review-item {
    background: rgba(255, 255, 255, 0.8);
    border-radius: 15px;
    padding: 1.5rem;
    border: 1px solid rgba(102, 126, 234, 0.2);
    transition: all 0.3s ease;
}

.modal-review-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.modal-review-item .product-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid rgba(102, 126, 234, 0.2);
}

.modal-review-item .product-info img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
}

.modal-review-item .product-details h4 {
    color: #2c3e50;
    margin: 0 0 0.25rem 0;
    font-size: 1.2rem;
}

.modal-review-item .product-details p {
    color: #7f8c8d;
    margin: 0;
    font-size: 0.9rem;
}

.review-already-submitted {
    text-align: center;
    padding: 1rem;
    background: rgba(39, 174, 96, 0.1);
    border-radius: 10px;
    border: 1px solid rgba(39, 174, 96, 0.3);
}

.review-already-submitted .submitted-rating {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.review-already-submitted .star-display {
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.review-already-submitted .star-display i {
    color: #f39c12;
    font-size: 1.1rem;
}

.review-already-submitted .review-status {
    color: #27ae60;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.modal-footer {
    padding: 1.5rem 2rem;
    background: rgba(248, 249, 250, 0.8);
    border-radius: 0 0 20px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}

.skip-reviews-btn,
.continue-after-reviews-btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1rem;
}

.skip-reviews-btn {
    background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
    color: white;
}

.skip-reviews-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(149, 165, 166, 0.3);
}

.continue-after-reviews-btn {
    background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
    color: white;
}

.continue-after-reviews-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(39, 174, 96, 0.3);
}

.print-btn {
    background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
    color: white;
    border: none;
    border-radius: 25px;
    padding: 1rem 2rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.print-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 35px rgba(230, 126, 34, 0.4);
    color: white;
    text-decoration: none;
}

/* Responsive Modal */
@media (max-width: 768px) {
    .modal-content {
        width: 95%;
        margin: 5% auto;
        max-height: 85vh;
    }
    
    .modal-header {
        padding: 1.5rem;
    }
    
    .modal-header h2 {
        font-size: 1.5rem;
    }
    
    .modal-body {
        padding: 1.5rem;
    }
    
    .modal-footer {
        flex-direction: column;
        gap: 1rem;
    }
    
    .skip-reviews-btn,
    .continue-after-reviews-btn {
        width: 100%;
        justify-content: center;
    }
}

.action-buttons {
    display: flex;
    gap: 2rem;
    justify-content: center;
    flex-wrap: wrap;
}

.continue-shopping-btn,
.home-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 2rem;
    text-decoration: none;
    border-radius: 25px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.continue-shopping-btn {
    background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
    color: white;
}

.continue-shopping-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 35px rgba(39, 174, 96, 0.4);
    color: white;
    text-decoration: none;
}

.home-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.home-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4);
    color: white;
    text-decoration: none;
}

/* Responsive Design */
@media (max-width: 768px) {
    .success-container {
        padding: 0 1rem;
    }
    
    .success-header {
        padding: 2rem 1rem;
    }
    
    .success-header h1 {
        font-size: 2rem;
    }
    
    .order-details,
    .review-section {
        padding: 1.5rem;
    }
    
    .product-item,
    .product-review-header {
        flex-direction: column;
        text-align: center;
    }
    
    .action-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .continue-shopping-btn,
    .home-btn {
        width: 100%;
        max-width: 300px;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .success-header h1 {
        font-size: 1.8rem;
    }
    
    .order-details h2,
    .review-section h2 {
        font-size: 1.5rem;
    }
    
    .star-rating label {
        font-size: 1.5rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const reviewForms = document.querySelectorAll('.review-form');
    
    reviewForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const productId = this.dataset.productId;
            const rating = this.querySelector(`input[name="rating_${productId}"]:checked`);
            const reviewText = this.querySelector('textarea[name="review_text"]').value;
            const submitBtn = this.querySelector('.submit-review-btn');
            
            if (!rating) {
                showNotification('Please select a rating before submitting.', 'error');
                return;
            }
            
            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
            
            // Submit review via AJAX
            const orderId = this.closest('.review-form-container').querySelector('[data-order-id]')?.dataset.orderId || '';
            fetch('submit_review_ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `order_id=${orderId}&product_id=${productId}&rating=${rating.value}&review_text=${encodeURIComponent(reviewText)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    // Hide the form after successful submission
                    this.closest('.review-form-container').style.opacity = '0.5';
                    this.closest('.review-form-container').style.pointerEvents = 'none';
                    submitBtn.innerHTML = '<i class="fas fa-check"></i> Review Submitted';
                } else {
                    showNotification(data.message, 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Review';
                }
            })
            .catch(error => {
                showNotification('Error submitting review. Please try again.', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Review';
            });
        });
    });
});

function showNotification(message, type) {
    let existing = document.querySelector('.notification');
    if (existing) existing.remove();
    
    let n = document.createElement('div');
    n.className = 'notification notification-' + type;
    n.innerHTML = '<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + '"></i> ' + message;
    n.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? 'linear-gradient(135deg, #27ae60 0%, #2ecc71 100%)' : 'linear-gradient(135deg, #e74c3c 0%, #c0392b 100%)'};
        color: #fff;
        padding: 15px 25px;
        border-radius: 12px;
        z-index: 1000;
        font-weight: 600;
        box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        max-width: 350px;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.2);
        transform: translateX(400px);
        transition: transform 0.3s ease;
        font-size: 1rem;
    `;
    document.body.appendChild(n);
    
    // Animate in
    setTimeout(() => {
        n.style.transform = 'translateX(0)';
    }, 100);
    
    // Animate out and remove
    setTimeout(() => {
        n.style.transform = 'translateX(400px)';
        setTimeout(() => n.remove(), 300);
    }, 3000);
}

// Modal Functions
function showReviewModal() {
    document.getElementById('reviewModal').style.display = 'block';
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
}

function closeReviewModal() {
    document.getElementById('reviewModal').style.display = 'none';
    document.body.style.overflow = 'auto'; // Restore scrolling
}

function skipReviews() {
    closeReviewModal();
    // Redirect to products page
    window.location.href = 'products.php';
}

function continueAfterReviews() {
    closeReviewModal();
    // Redirect to products page
    window.location.href = 'products.php';
}

function submitModalReview(orderId, productId) {
    const rating = document.querySelector(`input[name="rating_${productId}"]:checked`);
    const reviewText = document.querySelector(`#modal_review_text_${productId}`).value;
    const submitBtn = document.querySelector(`button[onclick="submitModalReview(${orderId}, ${productId})"]`);
    
    if (!rating) {
        showNotification('Please select a rating before submitting.', 'error');
        return;
    }
    
    // Disable button and show loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
    
    // Submit review via AJAX
    fetch('submit_review_ajax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `order_id=${orderId}&product_id=${productId}&rating=${rating.value}&review_text=${encodeURIComponent(reviewText)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            // Replace the form with success message
            const reviewItem = document.querySelector(`[data-product-id="${productId}"]`);
            reviewItem.innerHTML = `
                <div class="product-info">
                    <img src="${reviewItem.querySelector('img').src}" alt="${reviewItem.querySelector('h4').textContent}">
                    <div class="product-details">
                        <h4>${reviewItem.querySelector('h4').textContent}</h4>
                        <p>${reviewItem.querySelector('p').textContent}</p>
                    </div>
                </div>
                <div class="review-already-submitted">
                    <div class="submitted-rating">
                        <span>Your Rating:</span>
                        <div class="star-display">
                            ${generateStarsHTML(rating.value)}
                            <span class="rating-number">${rating.value}/5</span>
                        </div>
                    </div>
                    <div class="review-status">
                        <i class="fas fa-check-circle"></i> Review Submitted Successfully
                    </div>
                </div>
            `;
            
            // Check if all reviews are submitted
            checkAllReviewsSubmitted();
        } else {
            showNotification(data.message, 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Review';
        }
    })
    .catch(error => {
        showNotification('Error submitting review. Please try again.', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Review';
    });
}

function generateStarsHTML(rating) {
    let stars = '';
    for (let i = 1; i <= 5; i++) {
        if (i <= rating) {
            stars += '<i class="fas fa-star"></i>';
        } else {
            stars += '<i class="far fa-star"></i>';
        }
    }
    return stars;
}

function checkAllReviewsSubmitted() {
    const reviewItems = document.querySelectorAll('.modal-review-item');
    const submittedReviews = document.querySelectorAll('.review-already-submitted');
    
    if (submittedReviews.length === reviewItems.length) {
        // All reviews submitted, show continue button
        document.querySelector('.skip-reviews-btn').style.display = 'none';
        document.querySelector('.continue-after-reviews-btn').style.display = 'flex';
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('reviewModal');
    if (event.target === modal) {
        closeReviewModal();
    }
}

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeReviewModal();
    }
});
</script>

<?php include 'footer.php'; ?>