<?php
session_start();
include 'db.php';

echo "<h2>Diagnosing Veterinary Page Issues</h2>";

// Test 1: Database connection
if ($conn->connect_error) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $conn->connect_error . "</p>";
    exit;
} else {
    echo "<p style='color: green;'>✓ Database connection successful</p>";
}

// Test 2: Check if products table exists
$tableCheck = "SHOW TABLES LIKE 'products'";
$result = $conn->query($tableCheck);
if ($result->num_rows > 0) {
    echo "<p style='color: green;'>✓ Products table exists</p>";
} else {
    echo "<p style='color: red;'>✗ Products table does not exist</p>";
    exit;
}

// Test 3: Check table structure
$structure = "DESCRIBE products";
$result = $conn->query($structure);
echo "<p>Products table structure:</p>";
echo "<ul>";
while ($row = $result->fetch_assoc()) {
    echo "<li>" . $row['Field'] . " - " . $row['Type'] . "</li>";
}
echo "</ul>";

// Test 4: Check if subcategory column exists
$checkColumn = "SHOW COLUMNS FROM products LIKE 'subcategory'";
$result = $conn->query($checkColumn);
if ($result->num_rows > 0) {
    echo "<p style='color: green;'>✓ subcategory column exists</p>";
} else {
    echo "<p style='color: red;'>✗ subcategory column does not exist</p>";
}

// Test 5: Check for veterinary products
$checkProducts = "SELECT COUNT(*) as count FROM products WHERE category = 'Veterinary'";
$result = $conn->query($checkProducts);
if ($result) {
    $row = $result->fetch_assoc();
    echo "<p>Veterinary products in database: " . $row['count'] . "</p>";
} else {
    echo "<p style='color: red;'>✗ Error checking veterinary products: " . $conn->error . "</p>";
}

// Test 6: Test the exact query from veterinary.php
$sql = "SELECT * FROM products WHERE category = 'Veterinary'";
$result = $conn->query($sql);
if ($result) {
    echo "<p style='color: green;'>✓ Main veterinary query works</p>";
    echo "<p>Found " . $result->num_rows . " veterinary products</p>";
} else {
    echo "<p style='color: red;'>✗ Error in main query: " . $conn->error . "</p>";
}

// Test 7: Test subcategory query
$subcatQuery = "SELECT DISTINCT subcategory FROM products WHERE category = 'Veterinary' AND subcategory IS NOT NULL AND subcategory != ''";
$subcatResult = $conn->query($subcatQuery);
if ($subcatResult) {
    echo "<p style='color: green;'>✓ Subcategory query works</p>";
    echo "<p>Found " . $subcatResult->num_rows . " unique subcategories</p>";
} else {
    echo "<p style='color: red;'>✗ Error in subcategory query: " . $conn->error . "</p>";
}

// Test 8: Check if header.php can be included
if (file_exists('header.php')) {
    echo "<p style='color: green;'>✓ header.php file exists</p>";
} else {
    echo "<p style='color: red;'>✗ header.php file does not exist</p>";
}

// Test 9: Check if get_product_details.php exists
if (file_exists('get_product_details.php')) {
    echo "<p style='color: green;'>✓ get_product_details.php file exists</p>";
} else {
    echo "<p style='color: red;'>✗ get_product_details.php file does not exist</p>";
}

echo "<br><a href='fix_veterinary.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Run Fix Script</a>";
echo "<br><br><a href='veterinary.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Try Veterinary Page</a>";
?>
