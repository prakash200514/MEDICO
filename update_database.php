<?php
include 'db.php';

echo "<h2>Updating Database Structure</h2>";

// Check if columns exist and add them if they don't
$columns_to_add = [
    'address' => 'VARCHAR(255) DEFAULT NULL',
    'phone' => 'VARCHAR(20) DEFAULT NULL', 
    'delivery_date' => 'DATE DEFAULT NULL'
];

foreach ($columns_to_add as $column => $definition) {
    // Check if column exists
    $result = $conn->query("SHOW COLUMNS FROM orders LIKE '$column'");
    if ($result->num_rows == 0) {
        // Column doesn't exist, add it
        $sql = "ALTER TABLE orders ADD COLUMN $column $definition";
        if ($conn->query($sql)) {
            echo "<p>✅ Added column: $column</p>";
        } else {
            echo "<p>❌ Error adding column $column: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>✅ Column $column already exists</p>";
    }
}

echo "<h3>Database structure updated successfully!</h3>";
echo "<p><a href='checkout.php'>Go to Checkout</a></p>";
?> 