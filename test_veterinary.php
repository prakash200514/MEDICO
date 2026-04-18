<?php
session_start();
include 'db.php';

echo "<h2>Testing Veterinary Page</h2>";

// Check if subcategory column exists
$checkColumn = "SHOW COLUMNS FROM products LIKE 'subcategory'";
$result = $conn->query($checkColumn);

if ($result->num_rows > 0) {
    echo "<p style='color: green;'>✓ subcategory column exists</p>";
} else {
    echo "<p style='color: red;'>✗ subcategory column does not exist</p>";
    
    // Add the column if it doesn't exist
    $addColumn = "ALTER TABLE products ADD COLUMN subcategory VARCHAR(100) DEFAULT NULL";
    if ($conn->query($addColumn)) {
        echo "<p style='color: green;'>✓ subcategory column added successfully</p>";
    } else {
        echo "<p style='color: red;'>✗ Error adding subcategory column: " . $conn->error . "</p>";
    }
}

// Check for veterinary products
$checkProducts = "SELECT COUNT(*) as count FROM products WHERE category = 'Veterinary'";
$result = $conn->query($checkProducts);
$row = $result->fetch_assoc();

echo "<p>Veterinary products in database: " . $row['count'] . "</p>";

// Test the main query
$sql = "SELECT * FROM products WHERE category = 'Veterinary'";
$result = $conn->query($sql);

if ($result) {
    echo "<p style='color: green;'>✓ Main query works</p>";
    echo "<p>Found " . $result->num_rows . " veterinary products</p>";
} else {
    echo "<p style='color: red;'>✗ Error in main query: " . $conn->error . "</p>";
}

// Test subcategory query
$subcatQuery = "SELECT DISTINCT subcategory FROM products WHERE category = 'Veterinary' AND subcategory IS NOT NULL AND subcategory != ''";
$subcatResult = $conn->query($subcatQuery);

if ($subcatResult) {
    echo "<p style='color: green;'>✓ Subcategory query works</p>";
    echo "<p>Found " . $subcatResult->num_rows . " unique subcategories</p>";
} else {
    echo "<p style='color: red;'>✗ Error in subcategory query: " . $conn->error . "</p>";
}

echo "<br><a href='veterinary.php'>Go to Veterinary Page</a>";
?>
