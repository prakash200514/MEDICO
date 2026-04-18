<?php
session_start();
include 'db.php';

// Simple query without complex features
$sql = "SELECT * FROM products WHERE category = 'Veterinary' ORDER BY name ASC";
$result = $conn->query($sql);

// Get subcategories (simplified)
$subcategories = [];
$subcatQuery = "SELECT DISTINCT subcategory FROM products WHERE category = 'Veterinary' AND subcategory IS NOT NULL AND subcategory != ''";
$subcatResult = $conn->query($subcatQuery);
if ($subcatResult) {
    while ($row = $subcatResult->fetch_assoc()) {
        $subcategories[] = $row['subcategory'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veterinary Products - Medico</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="responsive.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .hero-section {
            text-align: center;
            margin-bottom: 2rem;
            padding: 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            color: white;
        }
        
        .hero-section h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .hero-section p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .product-info {
            padding: 1.5rem;
        }
        
        .product-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .product-description {
            color: #666;
            margin-bottom: 1rem;
            line-height: 1.5;
        }
        
        .product-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 1rem;
        }
        
        .btn {
            padding: 0.8rem 1.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .no-products {
            text-align: center;
            padding: 3rem;
            color: #666;
            background: white;
            border-radius: 15px;
            margin-top: 2rem;
        }
        
        .services-section {
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 15px;
            margin-top: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .services-section h2 {
            color: #333;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        
        .service-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .service-card i {
            font-size: 2.5rem;
            color: #667eea;
            margin-bottom: 1rem;
        }
        
        .service-card h3 {
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .service-card p {
            color: #666;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="hero-section">
            <h1><i class="fas fa-paw"></i> Veterinary Products</h1>
            <p>Professional veterinary care products for your beloved pets</p>
        </div>
        
        <?php if ($result && $result->num_rows > 0): ?>
            <div class="products-grid">
                <?php while ($product = $result->fetch_assoc()): ?>
                    <div class="product-card">
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                        <div class="product-info">
                            <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                            <div class="product-price">₹<?php echo number_format($product['price'], 2); ?></div>
                            <button class="btn" onclick="addToCart(<?php echo $product['id']; ?>)">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-products">
                <i class="fas fa-search" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                <h3>No veterinary products found</h3>
                <p>No veterinary products are currently available. Please check back later or contact the administrator to add products.</p>
                <br>
                <a href="admin_login.php" class="btn">Admin Login</a>
            </div>
        <?php endif; ?>
        
        <div class="services-section">
            <h2><i class="fas fa-stethoscope"></i> Veterinary Services</h2>
            <div class="services-grid">
                <div class="service-card">
                    <i class="fas fa-pills"></i>
                    <h3>Medications</h3>
                    <p>Professional veterinary medications and treatments for various pet conditions.</p>
                </div>
                <div class="service-card">
                    <i class="fas fa-syringe"></i>
                    <h3>Vaccinations</h3>
                    <p>Essential vaccines and immunization products for pet health and safety.</p>
                </div>
                <div class="service-card">
                    <i class="fas fa-band-aid"></i>
                    <h3>First Aid</h3>
                    <p>Emergency first aid supplies and wound care products for pets.</p>
                </div>
                <div class="service-card">
                    <i class="fas fa-heartbeat"></i>
                    <h3>Health Supplements</h3>
                    <p>Nutritional supplements and vitamins to support your pet's health.</p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function addToCart(productId) {
            alert('Product added to cart! Product ID: ' + productId);
        }
    </script>
</body>
</html>
