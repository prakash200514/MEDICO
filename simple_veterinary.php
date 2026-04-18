<?php
session_start();
include 'db.php';

// Simple test - just check if we can connect and query
echo "<h1>Simple Veterinary Test</h1>";

// Test database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "<p style='color: green;'>✓ Database connected</p>";

// Test basic query
$sql = "SELECT * FROM products WHERE category = 'Veterinary' LIMIT 5";
$result = $conn->query($sql);

if ($result) {
    echo "<p style='color: green;'>✓ Query successful</p>";
    echo "<p>Found " . $result->num_rows . " veterinary products</p>";
    
    if ($result->num_rows > 0) {
        echo "<h3>Products found:</h3>";
        while ($row = $result->fetch_assoc()) {
            echo "<p>- " . htmlspecialchars($row['name']) . " (₹" . $row['price'] . ")</p>";
        }
    } else {
        echo "<p style='color: orange;'>No veterinary products found in database</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Query failed: " . $conn->error . "</p>";
}

// Test if subcategory column exists
$checkColumn = "SHOW COLUMNS FROM products LIKE 'subcategory'";
$colResult = $conn->query($checkColumn);
if ($colResult->num_rows > 0) {
    echo "<p style='color: green;'>✓ subcategory column exists</p>";
} else {
    echo "<p style='color: red;'>✗ subcategory column missing</p>";
}

echo "<br><a href='veterinary.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Try Full Veterinary Page</a>";
?>
