<?php
include 'db.php';

echo "<h2>Setting up Reviews System</h2>";

// Create reviews table with updated schema
$sql = "CREATE TABLE IF NOT EXISTS reviews (
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

if ($conn->query($sql) === TRUE) {
    echo "<p style='color: green;'>✓ Reviews table created successfully!</p>";
} else {
    echo "<p style='color: red;'>Error creating reviews table: " . $conn->error . "</p>";
}

// Add description column to products table if it doesn't exist
$alter_products = [
    "ALTER TABLE products ADD COLUMN IF NOT EXISTS description TEXT",
    "ALTER TABLE products ADD COLUMN IF NOT EXISTS average_rating DECIMAL(3,2) DEFAULT 0.00",
    "ALTER TABLE products ADD COLUMN IF NOT EXISTS total_reviews INT DEFAULT 0"
];

foreach ($alter_products as $alter_sql) {
    if ($conn->query($alter_sql) === TRUE) {
        echo "<p style='color: green;'>✓ Products table updated successfully!</p>";
    } else {
        echo "<p style='color: orange;'>Products table update warning: " . $conn->error . "</p>";
    }
}

echo "<h3>Reviews System Setup Complete!</h3>";
echo "<p>You can now:</p>";
echo "<ul>";
echo "<li>Customers can submit reviews from their order history</li>";
echo "<li>Reviews are displayed on product pages</li>";
echo "<li>Admins can approve/reject reviews from the admin panel</li>";
echo "<li>Only approved reviews are shown to customers</li>";
echo "</ul>";

echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Run this setup script once: <code>setup_reviews_system.php</code></li>";
echo "<li>Customers can access order history from the navigation menu</li>";
echo "<li>Admins can manage reviews from the admin dashboard</li>";
echo "</ol>";

$conn->close();
?>
