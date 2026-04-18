<?php
include 'db.php';

echo "<h2>Adding Product Description and Multiple Image Support</h2>";

// Check if description column exists
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

// Check if image2 column exists
$result = $conn->query("SHOW COLUMNS FROM products LIKE 'image2'");
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE products ADD COLUMN image2 VARCHAR(100) DEFAULT NULL";
    if ($conn->query($sql)) {
        echo "<p>✅ Added image2 column to products table</p>";
    } else {
        echo "<p>❌ Error adding image2 column: " . $conn->error . "</p>";
    }
} else {
    echo "<p>✅ Image2 column already exists</p>";
}

// Check if subcategory column exists
$result = $conn->query("SHOW COLUMNS FROM products LIKE 'subcategory'");
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE products ADD COLUMN subcategory VARCHAR(50) DEFAULT NULL";
    if ($conn->query($sql)) {
        echo "<p>✅ Added subcategory column to products table</p>";
    } else {
        echo "<p>❌ Error adding subcategory column: " . $conn->error . "</p>";
    }
} else {
    echo "<p>✅ Subcategory column already exists</p>";
}

// Update existing products with sample descriptions if they don't have one
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
echo "<p><a href='injections.php'>View Injections</a></p>";
?>
