<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_email'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to submit a review.']);
    exit();
}

// Get POST data
$order_id = $_POST['order_id'] ?? '';
$product_id = $_POST['product_id'] ?? '';
$rating = $_POST['rating'] ?? '';
$review_text = $_POST['review_text'] ?? '';

// Validate input
if (empty($order_id) || empty($product_id) || empty($rating)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit();
}

if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Please select a valid rating.']);
    exit();
}

// Check if reviews table exists
$table_check = $conn->query("SHOW TABLES LIKE 'reviews'");
if (!$table_check || $table_check->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Review system is not set up yet. Please contact administrator.']);
    exit();
}

// Check if review already exists for this order and product
$stmt = $conn->prepare("SELECT * FROM reviews WHERE order_id = ? AND product_id = ?");
$stmt->bind_param("ii", $order_id, $product_id);
$stmt->execute();
$existing_review = $stmt->get_result()->fetch_assoc();

if ($existing_review) {
    echo json_encode(['success' => false, 'message' => 'You have already reviewed this product.']);
    exit();
}

// Validate that the order belongs to the current user
$column_check = $conn->query("SHOW COLUMNS FROM orders LIKE 'customer_email'");
$email_column = ($column_check && $column_check->num_rows > 0) ? 'customer_email' : 'user_email';

$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND $email_column = ?");
$stmt->bind_param("is", $order_id, $_SESSION['user_email']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Order not found or you do not have permission to review this order.']);
    exit();
}

// Insert review
$stmt = $conn->prepare("INSERT INTO reviews (order_id, product_id, user_email, rating, review_text, is_verified_purchase) VALUES (?, ?, ?, ?, ?, ?)");
$is_verified = 1; // Since they have an order, it's verified
$stmt->bind_param("iisisi", $order_id, $product_id, $_SESSION['user_email'], $rating, $review_text, $is_verified);

if ($stmt->execute()) {
    // Update product average rating and total reviews count
    updateProductRating($conn, $product_id);
    
    echo json_encode(['success' => true, 'message' => 'Thank you for your review! It has been submitted successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error submitting review. Please try again.']);
}

function updateProductRating($conn, $product_id) {
    // Check if average_rating and total_reviews columns exist
    $column_check = $conn->query("SHOW COLUMNS FROM products LIKE 'average_rating'");
    $rating_column_exists = $column_check && $column_check->num_rows > 0;
    
    if ($rating_column_exists) {
        // Calculate new average rating and total reviews
        $stats_query = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
                       FROM reviews 
                       WHERE product_id = ? AND is_verified_purchase = 1";
        $stmt = $conn->prepare($stats_query);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stats = $stmt->get_result()->fetch_assoc();
        
        $avg_rating = round($stats['avg_rating'], 2);
        $total_reviews = $stats['total_reviews'];
        
        // Update product table
        $update_query = "UPDATE products SET average_rating = ?, total_reviews = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("dii", $avg_rating, $total_reviews, $product_id);
        $stmt->execute();
    }
}
?>
