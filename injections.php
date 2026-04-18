<?php
session_start();
include 'db.php';

// Get search parameter
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build the SQL query
$sql = "SELECT * FROM products WHERE category = 'Injections'";
$params = [];
$types = "";

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR description LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ss";
}

$sql .= " ORDER BY name ASC";

// Prepare and execute the query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            background-attachment: fixed;
            background-size: cover;
            background-position: center;
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
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
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
            color:purple;
        }
        
        .safety-notice {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.3);
            text-align: center;
        }
        
        .safety-notice h3 {
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
        }
        
        .safety-notice p {
            margin: 0;
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
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 2.5rem;
            margin-top: 2rem;
        }
        
        .product-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .product-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2, #667eea);
            background-size: 200% 100%;
            animation: gradient-shift 3s ease-in-out infinite;
        }

        @keyframes gradient-shift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        
        .product-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 25px 50px rgba(0,0,0,0.2);
        }
        
        .prescription-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            z-index: 10;
        }
        
        .product-image {
            position: relative;
            width: 100%;
            height: 250px;
            overflow: hidden;
        }

        .product-image::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .product-card:hover .product-image::after {
            opacity: 1;
        }
        
        .product-image img,
        .primary-image,
        .secondary-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease, opacity 0.3s ease;
        }
        
        .secondary-image {
            position: absolute;
            top: 0;
            left: 0;
            opacity: 0;
        }
        
        .image-toggle {
            position: absolute;
            bottom: 15px;
            right: 15px;
            display: flex;
            gap: 8px;
            background: rgba(0, 0, 0, 0.8);
            padding: 8px 12px;
            border-radius: 25px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            z-index: 10;
        }
        
        .toggle-btn {
            width: 32px;
            height: 32px;
            border: none;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .toggle-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .toggle-btn:hover::before {
            left: 100%;
        }
        
        .toggle-btn.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            transform: scale(1.15);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .toggle-btn:hover {
            background: linear-gradient(135deg, #667eea, #764ba2);
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .image-indicator {
            position: absolute;
            bottom: 15px;
            left: 15px;
            display: flex;
            gap: 6px;
            z-index: 10;
        }
        
        .indicator-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.4);
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid rgba(255, 255, 255, 0.6);
        }
        
        .indicator-dot.active {
            background: #667eea;
            transform: scale(1.3);
            border-color: #667eea;
            box-shadow: 0 0 10px rgba(102, 126, 234, 0.5);
        }
        
        .indicator-dot:hover {
            background: #667eea;
            transform: scale(1.2);
        }
        
        .product-info {
            padding: 2rem;
            position: relative;
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
            font-size: 1.8rem;
            font-weight: 800;
            color: #27ae60;
            margin-bottom: 0.8rem;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
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
        
        .injection-types {
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 15px;
            margin-top: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .injection-types h2 {
            color: #333;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .types-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        
        .type-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .type-card i {
            font-size: 2.5rem;
            color: #667eea;
            margin-bottom: 1rem;
        }
        
        .type-card h3 {
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .type-card p {
            color: #666;
            line-height: 1.5;
        }
        
        .safety-guidelines {
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 15px;
            margin-top: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .safety-guidelines h2 {
            color: #333;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .guidelines-list {
            list-style: none;
            padding: 0;
        }
        
        .guidelines-list li {
            padding: 0.8rem 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .guidelines-list li:last-child {
            border-bottom: none;
        }
        
        .guidelines-list i {
            color: #ff6b6b;
            font-size: 1.2rem;
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
            
            .types-grid {
                grid-template-columns: 1fr;
            }
            
            /* Mobile-specific image toggle improvements */
            .image-toggle {
                bottom: 10px;
                right: 10px;
                padding: 6px 10px;
                gap: 6px;
            }
            
            .toggle-btn {
                width: 28px;
                height: 28px;
                font-size: 12px;
            }
            
            .image-indicator {
                bottom: 10px;
                left: 10px;
                gap: 5px;
            }
            
            .indicator-dot {
                width: 6px;
                height: 6px;
            }
        }
        
        @media (max-width: 480px) {
            /* Extra small screen image toggle improvements */
            .image-toggle {
                bottom: 8px;
                right: 8px;
                padding: 5px 8px;
                gap: 5px;
            }
            
            .toggle-btn {
                width: 24px;
                height: 24px;
                font-size: 10px;
            }
            
            .image-indicator {
                bottom: 8px;
                left: 8px;
                gap: 4px;
            }
            
            .indicator-dot {
                width: 5px;
                height: 5px;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="hero-section">
        <h1><i class="fas fa-syringe"></i> Injections</h1>
        <p>Professional injection products for medical treatment</p>
    </div>
    
    <div class="safety-notice">
        <h3><i class="fas fa-exclamation-triangle"></i> Important Safety Notice</h3>
        <p>All injection products require a valid prescription from a licensed medical professional. Please consult your healthcare provider before use.</p>
    </div>
    
    <div class="search-filter">
        <form method="GET">
            <input type="text" name="search" placeholder="Search injection products..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
        </form>
    </div>
    
    <?php if ($result->num_rows > 0): ?>
        <div class="products-grid">
            <?php while ($product = $result->fetch_assoc()): ?>
                <div class="product-card">
                    <div class="prescription-badge">Prescription Required</div>
                    <div class="product-image">
                        <img src="img/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    </div>
                    <div class="product-info">
                        <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <?php if (!empty($product['description'])): ?>
                            <p class="product-description"><?php echo substr(htmlspecialchars($product['description']), 0, 100); ?><?php echo strlen($product['description']) > 100 ? '...' : ''; ?></p>
                        <?php endif; ?>
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
                        </div>
    <?php else: ?>
        <div class="no-products">
            <i class="fas fa-search" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
            <h3>No injection products found</h3>
            <p>Try adjusting your search criteria or check back later for new products.</p>
        </div>
    <?php endif; ?>
    
    <div class="injection-types">
        <h2><i class="fas fa-syringe"></i> Injection Types</h2>
        <div class="types-grid">
            <div class="type-card">
                <i class="fas fa-tint"></i>
                <h3>Intramuscular</h3>
                <p>Injections administered into muscle tissue for systemic medication delivery.</p>
            </div>
            <div class="type-card">
                <i class="fas fa-droplet"></i>
                <h3>Intravenous</h3>
                <p>Injections administered directly into veins for rapid medication delivery.</p>
            </div>
            <div class="type-card">
                <i class="fas fa-layer-group"></i>
                <h3>Subcutaneous</h3>
                <p>Injections administered under the skin for slower, sustained medication release.</p>
            </div>
            <div class="type-card">
                <i class="fas fa-crosshairs"></i>
                <h3>Intradermal</h3>
                <p>Injections administered into the skin layer for diagnostic and therapeutic purposes.</p>
            </div>
        </div>
    </div>
    
    <div class="safety-guidelines">
        <h2><i class="fas fa-shield-alt"></i> Safety Guidelines</h2>
        <ul class="guidelines-list">
            <li>
                <i class="fas fa-check-circle"></i>
                <span>Always verify the prescription and dosage before administration</span>
            </li>
            <li>
                <i class="fas fa-check-circle"></i>
                <span>Use sterile needles and syringes for each injection</span>
            </li>
            <li>
                <i class="fas fa-check-circle"></i>
                <span>Follow proper injection site rotation techniques</span>
            </li>
            <li>
                <i class="fas fa-check-circle"></i>
                <span>Dispose of used needles in appropriate sharps containers</span>
            </li>
            <li>
                <i class="fas fa-check-circle"></i>
                <span>Monitor for adverse reactions and seek medical attention if needed</span>
            </li>
            <li>
                <i class="fas fa-check-circle"></i>
                <span>Store medications according to manufacturer guidelines</span>
            </li>
        </ul>
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
                .then(function(product) {
                    if (!product || product.error) {
                        alert(product && product.error ? product.error : 'Unable to load product.');
                        return;
                    }
                    var imagePath = prefixImagePath(product.image);
                    var imageHtml = imagePath ? '<img src="' + imagePath + '" alt="' + (product.name||'') + '" style="width: 220px;height: 220px;object-fit: cover;border-radius: 10px;">' : '';
                    document.getElementById('quickViewContent').innerHTML = '' +
                        '<div style="display:flex; gap:1rem; align-items:flex-start;">' +
                            imageHtml +
                            '<div style="flex:1;">' +
                                '<h2 style="margin:0 0 .5rem 0;">' + (product.name || '') + '</h2>' +
                                '<div style="color:#667eea;font-weight:700;font-size:1.2rem;">₹' + Number(product.price||0).toFixed(2) + '</div>' +
                                '<div style="margin:.5rem 0;color:#7f8c8d;">Stock: ' + (product.stock || 0) + ' units</div>' +
                                '<p style="line-height:1.6;">' + (product.description || '') + '</p>' +
                                '<div style="background:#ff6b6b;color:#fff;padding:.4rem .8rem;border-radius:20px;display:inline-block;margin:.5rem 0;">' +
                                '<i class="fas fa-exclamation-triangle"></i> Prescription Required</div>' +
                                '<button onclick="addToCart(' + product.id + ', this)" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 0.8rem 1.2rem; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">' +
                                    '<i class="fas fa-cart-plus"></i> Add to Cart' +
                                '</button>' +
                            '</div>' +
                        '</div>';
                    document.getElementById('quickViewModal').style.display = 'block';
                })
                .catch(function(){ alert('Unable to load product.'); });
        }
        

        
        function addToCart(productId, btn) {
            if (btn) {
                btn.disabled = true;
                var original = btn.innerHTML;
                btn.setAttribute('data-original', original);
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            }

            fetch('add_to_cart_ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'product_id=' + encodeURIComponent(productId)
            })
            .then(function(res){ return res.json(); })
            .then(function(data){
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = btn.getAttribute('data-original') || '<i class="fas fa-cart-plus"></i> Add to Cart';
                }
                if (data && data.success) {
                    showNotification(data.message || 'Product added to cart!', 'success');
                    updateCartCount(data.cart_count || 0);

                    if (typeof data.available !== 'undefined' && data.available <= 0 && btn) {
                        btn.classList.add('btn-disabled');
                        btn.disabled = true;
                        btn.innerHTML = '<i class="fas fa-times-circle"></i> Out of Stock';
                        var card = btn.closest('.product-card');
                        var stockLabel = card ? card.querySelector('.product-stock') : null;
                        if (stockLabel) stockLabel.textContent = 'Stock: 0 units';
                    }
                } else {
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
            .catch(function(){
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
        
        span.onclick = function() {
            modal.style.display = 'none';
        }
        
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
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
    
    <?php include 'footer.php'; ?>
</body>
</html>
