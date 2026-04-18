<?php
include 'db.php';

$product_id = $_GET['product_id'] ?? '';

if (!$product_id) {
    echo json_encode(['error' => 'Product ID required']);
    exit();
}

// Get verified purchase reviews for the product
$stmt = $conn->prepare("SELECT r.*, o.order_date 
                       FROM reviews r 
                       LEFT JOIN orders o ON r.order_id = o.id 
                       WHERE r.product_id = ? AND r.is_verified_purchase = 1 
                       ORDER BY r.review_date DESC");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate average rating
$stmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
                       FROM reviews 
                       WHERE product_id = ? AND is_verified_purchase = 1");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

$response = [
    'reviews' => $reviews,
    'stats' => [
        'average_rating' => round($stats['avg_rating'], 1),
        'total_reviews' => $stats['total_reviews']
    ]
];

header('Content-Type: application/json');
echo json_encode($response);
?>
