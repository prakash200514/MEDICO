<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db.php';

// Initialize cart as associative array: id => qty
if (!isset($_SESSION["cart"])) $_SESSION["cart"] = [];

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = array();
    
    if (isset($_POST['product_id'])) {
        $id = (int)$_POST['product_id'];

        // Fetch current stock from DB
        $stmt = $conn->prepare("SELECT stock, name FROM products WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if (!$result || $result->num_rows === 0) {
            $response = array(
                'success' => false,
                'message' => 'Product not found.'
            );
        } else {
            $prod = $result->fetch_assoc();
            $stock = (int)$prod['stock'];
            $name = $prod['name'];

            $currentQty = isset($_SESSION["cart"][$id]) ? (int)$_SESSION["cart"][$id] : 0;

            if ($stock <= 0) {
                $response = array(
                    'success' => false,
                    'message' => '"' . $name . '" is out of stock.',
                    'available' => 0
                );
            } elseif ($currentQty + 1 > $stock) {
                $response = array(
                    'success' => false,
                    'message' => 'Only ' . $stock . ' unit' . ($stock > 1 ? 's' : '') . ' available for "' . $name . '".',
                    'available' => $stock
                );
            } else {
                // Add to cart (increase quantity)
                if (isset($_SESSION["cart"][$id])) {
                    $_SESSION["cart"][$id]++;
                } else {
                    $_SESSION["cart"][$id] = 1;
                }

                // Calculate total items in cart
                $total_items = array_sum($_SESSION["cart"]);

                $response = array(
                    'success' => true,
                    'message' => 'Product added to cart!',
                    'cart_count' => $total_items,
                    'available' => $stock - $_SESSION["cart"][$id]
                );
            }
        }
    } else {
        $response = array(
            'success' => false,
            'message' => 'Product ID is required.'
        );
    }
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// If not POST request, return error
header('Content-Type: application/json');
echo json_encode(array('success' => false, 'message' => 'Invalid request method'));
?> 