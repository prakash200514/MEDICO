<?php
session_start();
include 'db.php';
include 'header.php';
// Get search and filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$subcategory = isset($_GET['subcategory']) ? $_GET['subcategory'] : '';

// Build the SQL query
$sql = "SELECT * FROM products WHERE category = 'Veterinary'";
$params = [];
$types = "";

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR description LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ss";
}

if (!empty($subcategory)) {
    $sql .= " AND subcategory = ?";
    $params[] = $subcategory;
    $types .= "s";
}

$sql .= " ORDER BY name ASC";

// Prepare and execute the query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Get unique subcategories for filter
$subcategories = [];
// Only attempt to read `subcategory` if the column exists to avoid SQL errors
$checkCol = $conn->query("SHOW COLUMNS FROM products LIKE 'subcategory'");
if ($checkCol && $checkCol->num_rows > 0) {
    $subcatQuery = "SELECT DISTINCT subcategory FROM products WHERE category = 'Veterinary' AND subcategory IS NOT NULL AND subcategory != ''";
    $subcatResult = $conn->query($subcatQuery);
    if ($subcatResult) {
        while ($row = $subcatResult->fetch_assoc()) {
            $subcategories[] = $row['subcategory'];
        }
    }
} else {
    // Column missing: leave $subcategories empty (UI will show "All Categories")
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
            background-attachment: fixed;
            background-size: cover;
            background-position: center;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .hero-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            margin-bottom: 2rem;
            padding: 2rem;
            color: white;
        }
        
        .search-filter {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            margin-bottom: 2rem;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .services-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            margin-top: 2rem;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
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
            color: white;
        }
        
        .hero-section p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .search-filter {
            background: rgba(255, 255, 255, 0.95);
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .search-filter form {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .search-filter input,
        .search-filter select {
            padding: 0.8rem 1rem;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .search-filter input:focus,
        .search-filter select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .search-filter button {
            padding: 0.8rem 1.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .search-filter button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
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
            position: relative;
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
        
        .product-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn {
            padding: 0.6rem 1rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-secondary {
            background: #f8f9fa;
            color: #333;
            border: 1px solid #e1e5e9;
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
        
        @media (max-width: 768px) {
            .hero-section h1 {
                font-size: 2rem;
            }
            
            .search-filter form {
                flex-direction: column;
            }
            
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 1rem;
            }
            
            .services-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="hero-section">
            <h1><i class="fas fa-paw"></i> Veterinary Products</h1>
            <p>Professional veterinary care products for your beloved pets</p>
        </div>
        
        <div class="search-filter">
            <form method="GET">
                <input type="text" name="search" placeholder="Search veterinary products..." value="<?php echo htmlspecialchars($search); ?>">
                <select name="subcategory">
                    <option value="">All Categories</option>
                    <?php foreach ($subcategories as $subcat): ?>
                        <option value="<?php echo htmlspecialchars($subcat); ?>" <?php echo $subcategory === $subcat ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($subcat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Search</button>
            </form>
        </div>
        
        <?php if ($result && $result->num_rows > 0): ?>
            <div class="products-grid">
                <?php while ($product = $result->fetch_assoc()): ?>
                    <div class="product-card">
                        <?php
                            $rawImage = isset($product['image']) ? $product['image'] : '';
                            $isAbsolute = (strpos($rawImage, 'http://') === 0) || (strpos($rawImage, 'https://') === 0);
                            $hasImgPrefix = (strpos($rawImage, 'img/') === 0) || (strpos($rawImage, '/img/') !== false);
                            $imagePath = $isAbsolute || $hasImgPrefix || $rawImage === '' ? $rawImage : ('img/' . $rawImage);
                        ?>
                        <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                        <div class="product-info">
                            <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                            <div class="product-price">₹<?php echo number_format($product['price'], 2); ?></div>
                            <?php if ($product['stock'] <= 0): ?>
                                <div class="product-stock out-of-stock">Out of Stock</div>
                                <div class="product-actions">
                                    <button class="btn btn-secondary" onclick="quickView(<?php echo $product['id']; ?>)">
                                        <i class="fas fa-eye"></i> Quick View
                                    </button>
                                    <button class="btn btn-disabled" disabled>
                                        <i class="fas fa-times-circle"></i> Out of Stock
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="product-stock">Stock: <?php echo $product['stock']; ?> units</div>
                                <div class="product-actions">
                                    <button class="btn btn-secondary" onclick="quickView(<?php echo $product['id']; ?>)">
                                        <i class="fas fa-eye"></i> Quick View
                                    </button>
                                    <button class="btn btn-primary" onclick="addToCart(<?php echo $product['id']; ?>, this)">
                                        <i class="fas fa-cart-plus"></i> Add to Cart
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-products">
                <i class="fas fa-search" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                <h3>No veterinary products found</h3>
                <p>Try adjusting your search criteria or check back later for new products.</p>
                <br>
                <a href="admin_login.php" class="btn btn-primary">Admin Login</a>
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
    
    <!-- Quick View Modal -->
    <div id="quickViewModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="quickViewContent"></div>
        </div>
    </div>
    
    <script>
        function prefixImagePath(image) {
            if (!image) return '';
            var isAbsolute = image.startsWith('http://') || image.startsWith('https://');
            var hasImgPrefix = image.startsWith('img/') || image.indexOf('/img/') !== -1;
            return (isAbsolute || hasImgPrefix) ? image : ('img/' + image);
        }

        function quickView(productId) {
            fetch('get_product_details.php?id=' + encodeURIComponent(productId))
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (data && !data.error) {
                        var imagePath = prefixImagePath(data.image);
                        var html = '' +
                            '<div style="display:flex; gap:1rem; align-items:flex-start;">' +
                                (imagePath ? '<img src="' + imagePath + '" alt="' + (data.name||'') + '" style="width:220px;height:220px;object-fit:cover;border-radius:10px;">' : '') +
                                '<div style="flex:1;">' +
                                    '<h2 style="margin:0 0 .5rem 0;">' + (data.name || '') + '</h2>' +
                                    '<div style="color:#667eea;font-weight:700;font-size:1.2rem;">₹' + Number(data.price||0).toFixed(2) + '</div>' +
                                    '<div style="margin:.5rem 0;color:#7f8c8d;">Stock: ' + (data.stock || 0) + ' units</div>' +
                                    '<p style="line-height:1.6;">' + (data.description || '') + '</p>' +
                                '</div>' +
                            '</div>';
                        document.getElementById('quickViewContent').innerHTML = html;
                        document.getElementById('quickViewModal').style.display = 'block';
                    } else {
                        alert(data && data.error ? data.error : 'Unable to load product.');
                    }
                })
                .catch(function() { alert('Unable to load product.'); });
        }
        
        function addToCart(productId, btn) {
            if (btn) {
                btn.disabled = true;
                var originalHtml = btn.innerHTML;
                btn.setAttribute('data-original', originalHtml);
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            }

            fetch('add_to_cart_ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'product_id=' + encodeURIComponent(productId)
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = btn.getAttribute('data-original') || '<i class="fas fa-cart-plus"></i> Add to Cart';
                    }

                    if (data && data.success) {
                        showNotification(data.message || 'Added to cart', 'success');
                        updateCartCount(data.cart_count || 0);

                        // If no more stock available, disable the button and update stock label
                        if (typeof data.available !== 'undefined' && data.available <= 0) {
                            btn.classList.add('btn-disabled');
                            btn.disabled = true;
                            btn.innerHTML = '<i class="fas fa-times-circle"></i> Out of Stock';
                            var card = btn.closest('.product-card');
                            var stockLabel = card ? card.querySelector('.product-stock') : null;
                            if (stockLabel) stockLabel.textContent = 'Stock: 0 units';
                        }
                    } else {
                        // If server says available == 0, update UI
                        if (data && typeof data.available !== 'undefined' && data.available == 0 && btn) {
                            btn.classList.add('btn-disabled');
                            btn.disabled = true;
                            btn.innerHTML = '<i class="fas fa-times-circle"></i> Out of Stock';
                            var card = btn.closest('.product-card');
                            var stockLabel = card ? card.querySelector('.product-stock') : null;
                            if (stockLabel) stockLabel.textContent = 'Stock: 0 units';
                        }
                        showNotification((data && data.message) ? data.message : 'Unable to add to cart', 'error');
                    }
                })
            .catch(function() {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = btn.getAttribute('data-original') || '<i class="fas fa-cart-plus"></i> Add to Cart';
                }
                showNotification('Error adding to cart.', 'error');
            });
        }

        function showNotification(message, type) {
            var existing = document.querySelector('.notification');
            if (existing) existing.remove();
            var n = document.createElement('div');
            n.className = 'notification notification-' + type;
            n.innerHTML = '<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + '"></i> ' + message;
            n.style.cssText = [
                'position:fixed',
                'top:20px',
                'right:20px',
                'background:' + (type === 'success' ? 'linear-gradient(135deg,#27ae60 0%,#2ecc71 100%)' : 'linear-gradient(135deg,#e74c3c 0%,#c0392b 100%)'),
                'color:#fff',
                'padding:14px 22px',
                'border-radius:12px',
                'z-index:1000',
                'font-weight:600',
                'box-shadow:0 8px 25px rgba(0,0,0,0.2)',
                'max-width:340px',
                'backdrop-filter:blur(10px)',
                'border:1px solid rgba(255,255,255,0.2)',
                'transform:translateX(400px)',
                'transition:transform .3s ease',
                'font-size:1rem'
            ].join(';');
            document.body.appendChild(n);
            setTimeout(function(){ n.style.transform = 'translateX(0)'; }, 80);
            setTimeout(function(){
                n.style.transform = 'translateX(400px)';
                setTimeout(function(){ if (n && n.parentNode) n.parentNode.removeChild(n); }, 300);
            }, 2400);
        }

        function updateCartCount(count) {
            var cartLink = document.querySelector('a[href="cart.php"]');
            if (!cartLink) return;
            var badge = cartLink.querySelector('.cart-badge');
            if (count > 0) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'cart-badge';
                    cartLink.appendChild(badge);
                }
                badge.textContent = count;
            } else {
                if (badge) badge.remove();
            }
        }
        
        // Modal functionality
        const modal = document.getElementById('quickViewModal');
        const span = document.getElementsByClassName('close')[0];
        
        if (span) {
            span.onclick = function() {
                modal.style.display = 'none';
            }
        }
        
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
        
        window.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                modal.style.display = 'none';
            }
        });
    </script>
    
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 2rem;
            border-radius: 15px;
            width: 80%;
            max-width: 600px;
            position: relative;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: #000;
        }
        
        .product-stock {
            color: #7f8c8d;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .product-stock::before {
            content: '📦';
            font-size: 1rem;
        }
        
        .product-stock.out-of-stock {
            color: #e74c3c;
            font-weight: 600;
        }
        
        .product-stock.out-of-stock::before {
            content: '❌';
            font-size: 1rem;
        }
        
        .btn-disabled {
            background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
            cursor: not-allowed;
            opacity: 0.7;
            box-shadow: none;
        }
        
        .btn-disabled:hover {
            transform: none;
            box-shadow: none;
            background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
        }
    </style>
</body>
</html>
