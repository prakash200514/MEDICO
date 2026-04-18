<?php
include 'db.php';

echo "<h2>Adding Product Description Support</h2>";

// Check and add description column
$result = $conn->query("SHOW COLUMNS FROM products LIKE 'description'");
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE products ADD COLUMN description TEXT DEFAULT NULL";
    if ($conn->query($sql)) {
        echo "<p>✅ Added description column to products table</p>";
    } else {
        echo "<p>❌ Error adding description column: " . $conn->error . "</p>";
    }
} else {
    echo "<p>✅ Description column already exists</p>";
}

// Note: image2 column support has been removed

// Update existing products with sample descriptions
$update_sql = "UPDATE products SET description = 'High-quality medicine for effective treatment.' WHERE description IS NULL";
if ($conn->query($update_sql)) {
    $affected = $conn->affected_rows;
    echo "<p>✅ Updated $affected products with sample descriptions</p>";
} else {
    echo "<p>❌ Error updating descriptions: " . $conn->error . "</p>";
}

echo "<h3>Database updated successfully!</h3>";
echo "<p><a href='admin_add_product.php'>Go to Add Product</a></p>";
echo "<p><a href='products.php'>View Products</a></p>";
?>
