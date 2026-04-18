<?php
session_start();
include 'db.php';

// Test the complete redirect flow
echo "<h2>Testing Redirect Flow</h2>";

// Step 1: Add a product to cart
if (!isset($_SESSION["cart"])) $_SESSION["cart"] = [];
$_SESSION["cart"][1] = 1; // Add product ID 1 to cart

echo "<p>✅ Step 1: Product added to cart</p>";
echo "<p>Cart contents: " . print_r($_SESSION["cart"], true) . "</p>";

// Step 2: Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<p>❌ Step 2: User not logged in - should redirect to signup.php?redirect=checkout</p>";
    echo "<p><a href='signup.php?redirect=checkout'>Go to Signup with redirect=checkout</a></p>";
} else {
    echo "<p>✅ Step 2: User is logged in</p>";
    echo "<p><a href='checkout.php?from_cart=true'>Go to Checkout</a></p>";
}

// Step 3: Test checkout access
echo "<p><a href='checkout.php?from_cart=true'>Test Checkout Access</a></p>";

// Step 4: Test login with redirect
echo "<p><a href='login.php?redirect=checkout'>Test Login with redirect=checkout</a></p>";

// Step 5: Test signup with redirect
echo "<p><a href='signup.php?redirect=checkout'>Test Signup with redirect=checkout</a></p>";

echo "<hr>";
echo "<h3>Current Session Data:</h3>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";
?>
