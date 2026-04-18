<?php
include 'db.php';

echo "<h2>🔧 Database Column Fix Script</h2>";
echo "<p>This script will add missing columns to your database tables.</p>";

$success_count = 0;
$error_count = 0;

// Function to safely add column
function addColumnIfNotExists($conn, $table, $column, $definition, $description) {
    global $success_count, $error_count;
    
    $check_sql = "SHOW COLUMNS FROM $table LIKE '$column'";
    $result = $conn->query($check_sql);
    
    if ($result && $result->num_rows == 0) {
        $sql = "ALTER TABLE $table ADD COLUMN $column $definition";
        if ($conn->query($sql)) {
            echo "<p style='color: green;'>✅ $description</p>";
            $success_count++;
        } else {
            echo "<p style='color: red;'>❌ Error adding $description: " . $conn->error . "</p>";
            $error_count++;
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ $description already exists</p>";
    }
}

echo "<h3>📋 Adding Missing Columns to Products Table</h3>";

// Add description column
addColumnIfNotExists($conn, 'products', 'description', 'TEXT', 'Added description column to products table');

// Add average_rating column
addColumnIfNotExists($conn, 'products', 'average_rating', 'DECIMAL(3,2) DEFAULT 0.00', 'Added average_rating column to products table');

// Add total_reviews column
addColumnIfNotExists($conn, 'products', 'total_reviews', 'INT DEFAULT 0', 'Added total_reviews column to products table');

echo "<h3>📋 Creating Reviews Table</h3>";

// Create reviews table if it doesn't exist
$table_check = $conn->query("SHOW TABLES LIKE 'reviews'");
if (!$table_check || $table_check->num_rows == 0) {
    $reviews_sql = "CREATE TABLE reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_email VARCHAR(100) NOT NULL,
        product_id INT NOT NULL,
        order_id INT,
        rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
        review_text TEXT,
        review_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_verified_purchase BOOLEAN DEFAULT FALSE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        INDEX idx_product_id (product_id),
        INDEX idx_user_email (user_email),
        INDEX idx_rating (rating)
    )";
    
    if ($conn->query($reviews_sql)) {
        echo "<p style='color: green;'>✅ Created reviews table</p>";
        $success_count++;
    } else {
        echo "<p style='color: red;'>❌ Error creating reviews table: " . $conn->error . "</p>";
        $error_count++;
    }
} else {
    echo "<p style='color: blue;'>ℹ️ Reviews table already exists</p>";
}

echo "<h3>📊 Summary</h3>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<p><strong>✅ Successful operations:</strong> $success_count</p>";
echo "<p><strong>❌ Errors:</strong> $error_count</p>";

if ($error_count == 0) {
    echo "<p style='color: green; font-weight: bold;'>🎉 All database updates completed successfully!</p>";
    echo "<p>You can now:</p>";
    echo "<ul>";
    echo "<li>✅ Edit products in admin panel without errors</li>";
    echo "<li>✅ Add new products with descriptions</li>";
    echo "<li>✅ Use the review system</li>";
    echo "<li>✅ View order history without errors</li>";
    echo "</ul>";
} else {
    echo "<p style='color: orange;'>⚠️ Some operations had errors. Please check the messages above.</p>";
}
echo "</div>";

echo "<h3>🔗 Next Steps</h3>";
echo "<p>After running this script, you should:</p>";
echo "<ol>";
echo "<li>Test editing a product in the admin panel</li>";
echo "<li>Test adding a new product</li>";
echo "<li>Test the order history page</li>";
echo "<li>Test the review system</li>";
echo "</ol>";

$conn->close();
?>
