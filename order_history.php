<?php 
include 'db.php'; 
session_start();
$page_title = "Order History - Medico";
include 'header.php';

// Check if user is logged in
if (!isset($_SESSION['user_email'])) {
    header('Location: login.php');
    exit();
}

// Get user's orders
$stmt = $conn->prepare("SELECT o.*, p.name as product_name, p.image as product_image 
                       FROM orders o 
                       JOIN products p ON o.product_id = p.id 
                       WHERE o.customer_email = ? 
                       ORDER BY o.order_date DESC");
$stmt->bind_param("s", $_SESSION['user_email']);
$stmt->execute();
$orders = $stmt->get_result();
?>

<div class="order-history-page">
    <div class="page-header">
        <h1><i class="fas fa-history"></i> Order History</h1>
        <p>View your past orders and leave reviews</p>
    </div>

    <div class="orders-container">
        <?php if ($orders->num_rows > 0): ?>
            <div class="orders-grid">
                <?php while ($order = $orders->fetch_assoc()): ?>
                    <?php
                    // Check if review already exists for this order (only if reviews table exists)
                    $existing_review = null;
                    $reviews_table_exists = false;
                    
                    // Check if reviews table exists
                    $table_check = $conn->query("SHOW TABLES LIKE 'reviews'");
                    if ($table_check && $table_check->num_rows > 0) {
                        $reviews_table_exists = true;
                        $review_stmt = $conn->prepare("SELECT * FROM reviews WHERE order_id = ? AND product_id = ?");
                        $review_stmt->bind_param("ii", $order['id'], $order['product_id']);
                        $review_stmt->execute();
                        $existing_review = $review_stmt->get_result()->fetch_assoc();
                    }
                    ?>
                    
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-info">
                                <h3>Order #<?php echo $order['id']; ?></h3>
                                <p class="order-date"><?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></p>
                            </div>
                            <div class="order-status">
                                <span class="status-badge completed">
                                    <i class="fas fa-check-circle"></i> Completed
                                </span>
                            </div>
                        </div>
                        
                        <div class="order-content">
                            <div class="product-info">
                                <img src="img/<?php echo $order['product_image']; ?>" alt="<?php echo $order['product_name']; ?>" class="product-image">
                                <div class="product-details">
                                    <h4><?php echo $order['product_name']; ?></h4>
                                    <p class="quantity">Quantity: <?php echo $order['quantity']; ?></p>
                                    <p class="price">Total: ₹<?php echo $order['total_price']; ?></p>
                                </div>
                            </div>
                            
                            <div class="order-actions">
                                <?php if ($reviews_table_exists): ?>
                                    <?php if ($existing_review): ?>
                                        <?php if ($existing_review['is_verified_purchase']): ?>
                                            <div class="review-status">
                                                <span class="review-badge approved">
                                                    <i class="fas fa-star"></i> Review Submitted
                                                </span>
                                            </div>
                                        <?php else: ?>
                                            <div class="review-status">
                                                <span class="review-badge pending">
                                                    <i class="fas fa-clock"></i> Review Pending
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <a href="submit_review.php?order_id=<?php echo $order['id']; ?>&product_id=<?php echo $order['product_id']; ?>" class="btn btn-primary">
                                            <i class="fas fa-star"></i> Leave Review
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="review-status">
                                        <span class="review-badge disabled">
                                            <i class="fas fa-info-circle"></i> Reviews Coming Soon
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-orders">
                <i class="fas fa-shopping-cart"></i>
                <h3>No Orders Yet</h3>
                <p>You haven't placed any orders yet. Start shopping to see your order history here.</p>
                <a href="products.php" class="btn btn-primary">
                    <i class="fas fa-shopping-bag"></i> Start Shopping
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.order-history-page {
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
    color:  #764ba2;
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

.orders-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 0 2rem;
}

.orders-grid {
    display: grid;
    gap: 2rem;
}

.order-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s ease;
}

.order-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.order-info h3 {
    color: #2c3e50;
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    font-weight: 700;
}

.order-date {
    color: #7f8c8d;
    font-size: 0.95rem;
    margin: 0;
}

.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.status-badge.completed {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    color: #155724;
    border: 1px solid #c3e6cb;
}

.order-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1.5rem;
}

.product-info {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    flex: 1;
}

.product-image {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 12px;
    border: 2px solid #e1e8ed;
}

.product-details h4 {
    color: #2c3e50;
    font-size: 1.2rem;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.quantity, .price {
    color: #7f8c8d;
    font-size: 0.95rem;
    margin: 0.25rem 0;
}

.price {
    color: #27ae60;
    font-weight: 600;
    font-size: 1.1rem;
}

.order-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    cursor: pointer;
    font-size: 0.95rem;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4);
    color: white;
    text-decoration: none;
}

.review-status {
    display: flex;
    align-items: center;
}

.review-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.review-badge.approved {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    color: #155724;
    border: 1px solid #c3e6cb;
}

.review-badge.pending {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    color: #856404;
    border: 1px solid #ffeaa7;
}

.review-badge.disabled {
    background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
    color: #6c757d;
    border: 1px solid #dee2e6;
}

.no-orders {
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

.no-orders i {
    font-size: 4rem;
    color: #95a5a6;
    margin-bottom: 1.5rem;
}

.no-orders h3 {
    color: #2c3e50;
    font-size: 2rem;
    margin-bottom: 1rem;
    font-weight: 700;
}

.no-orders p {
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
    
    .orders-container {
        padding: 0 1rem;
    }
    
    .order-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .order-content {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .product-info {
        width: 100%;
    }
    
    .order-actions {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .page-header h1 {
        font-size: 1.8rem;
    }
    
    .order-card {
        padding: 1.5rem;
    }
    
    .product-info {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .product-image {
        width: 100px;
        height: 100px;
    }
}
</style>

<?php include 'footer.php'; ?>
