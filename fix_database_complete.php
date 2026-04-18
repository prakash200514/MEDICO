<?php
include 'db.php';

echo "<h2>Fixing Database Structure</h2>";

// First, let's check if the orders table exists
$result = $conn->query("SHOW TABLES LIKE 'orders'");
if ($result->num_rows == 0) {
    echo "<p>❌ Orders table doesn't exist. Creating it...</p>";
    
    // Create the orders table with all required columns
    $create_table_sql = "
    CREATE TABLE orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_name VARCHAR(100),
        customer_email VARCHAR(100),
        product_id INT,
        quantity INT,
        total_price DECIMAL(10,2),
        payment_method VARCHAR(50),
        address VARCHAR(255),
        phone VARCHAR(20),
        order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        delivery_date DATE,
        prescription_path VARCHAR(255)
    )";
    
    if ($conn->query($create_table_sql)) {
        echo "<p>✅ Orders table created successfully!</p>";
    } else {
        echo "<p>❌ Error creating orders table: " . $conn->error . "</p>";
        exit;
    }
} else {
    echo "<p>✅ Orders table exists</p>";
    
    // Check and add missing columns
    $required_columns = [
        'customer_name' => 'VARCHAR(100)',
        'customer_email' => 'VARCHAR(100)',
        'product_id' => 'INT',
        'quantity' => 'INT',
        'total_price' => 'DECIMAL(10,2)',
        'payment_method' => 'VARCHAR(50)',
        'address' => 'VARCHAR(255)',
        'phone' => 'VARCHAR(20)',
        'order_date' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'delivery_date' => 'DATE',
        'prescription_path' => 'VARCHAR(255)'
    ];
    
    foreach ($required_columns as $column => $definition) {
        $result = $conn->query("SHOW COLUMNS FROM orders LIKE '$column'");
        if ($result->num_rows == 0) {
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
}

echo "<h3>Database structure updated successfully!</h3>";
echo "<p><a href='checkout.php'>Go to Checkout</a></p>";
echo "<p><a href='check_database.php'>View Database Structure</a></p>";
?>
