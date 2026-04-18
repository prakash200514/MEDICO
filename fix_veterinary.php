<?php
session_start();
include 'db.php';

echo "<h2>Fixing Veterinary Page Issues</h2>";

// 1. Check and add subcategory column if it doesn't exist
$checkColumn = "SHOW COLUMNS FROM products LIKE 'subcategory'";
$result = $conn->query($checkColumn);

if ($result->num_rows == 0) {
    echo "<p>Adding subcategory column...</p>";
    $addColumn = "ALTER TABLE products ADD COLUMN subcategory VARCHAR(100) DEFAULT NULL";
    if ($conn->query($addColumn)) {
        echo "<p style='color: green;'>✓ subcategory column added successfully</p>";
    } else {
        echo "<p style='color: red;'>✗ Error adding subcategory column: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: green;'>✓ subcategory column already exists</p>";
}

// 2. Check if there are any veterinary products
$checkProducts = "SELECT COUNT(*) as count FROM products WHERE category = 'Veterinary'";
$result = $conn->query($checkProducts);
$row = $result->fetch_assoc();

echo "<p>Veterinary products in database: " . $row['count'] . "</p>";

// 3. If no veterinary products exist, add some sample products
if ($row['count'] == 0) {
    echo "<p>Adding sample veterinary products...</p>";
    
    $sampleProducts = [
        [
            'name' => 'Pet Vitamin C',
            'description' => 'Essential vitamin C supplement for pets to boost immunity',
            'price' => 299.00,
            'stock' => 50,
            'subcategory' => 'Health Supplements',
            'image' => 'img/vitamin_c.jpg'
        ],
        [
            'name' => 'Flea Treatment',
            'description' => 'Effective flea and tick treatment for dogs and cats',
            'price' => 450.00,
            'stock' => 30,
            'subcategory' => 'Flea & Tick',
            'image' => 'img/flea_treatment.jpg'
        ],
        [
            'name' => 'Pet Dental Care',
            'description' => 'Dental hygiene products for pets to maintain oral health',
            'price' => 199.00,
            'stock' => 40,
            'subcategory' => 'Dental Care',
            'image' => 'img/dental_care.jpg'
        ]
    ];
    
    $insertSql = "INSERT INTO products (name, description, price, stock, category, subcategory, image) VALUES (?, ?, ?, ?, 'Veterinary', ?, ?)";
    $stmt = $conn->prepare($insertSql);
    
    foreach ($sampleProducts as $product) {
        $stmt->bind_param("ssdss", $product['name'], $product['description'], $product['price'], $product['stock'], $product['subcategory'], $product['image']);
        if ($stmt->execute()) {
            echo "<p style='color: green;'>✓ Added: " . $product['name'] . "</p>";
        } else {
            echo "<p style='color: red;'>✗ Error adding " . $product['name'] . ": " . $conn->error . "</p>";
        }
    }
}

// 4. Test the main veterinary query
$sql = "SELECT * FROM products WHERE category = 'Veterinary'";
$result = $conn->query($sql);

if ($result) {
    echo "<p style='color: green;'>✓ Main veterinary query works</p>";
    echo "<p>Found " . $result->num_rows . " veterinary products</p>";
} else {
    echo "<p style='color: red;'>✗ Error in main query: " . $conn->error . "</p>";
}

// 5. Test subcategory query
$subcatQuery = "SELECT DISTINCT subcategory FROM products WHERE category = 'Veterinary' AND subcategory IS NOT NULL AND subcategory != ''";
$subcatResult = $conn->query($subcatQuery);

if ($subcatResult) {
    echo "<p style='color: green;'>✓ Subcategory query works</p>";
    echo "<p>Found " . $subcatResult->num_rows . " unique subcategories</p>";
} else {
    echo "<p style='color: red;'>✗ Error in subcategory query: " . $conn->error . "</p>";
}

echo "<br><p style='color: green; font-weight: bold;'>Veterinary page should now work correctly!</p>";
echo "<a href='veterinary.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Veterinary Page</a>";
?>
