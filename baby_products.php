<?php 
include 'db.php'; 
session_start();
$page_title = "Baby Products - Medico";
include 'header.php';

$category = $_GET['category'] ?? '';
$sql = "SELECT * FROM products WHERE category='Baby Products'";
if ($category && $category !== 'Baby Products') {
    $sql .= " AND subcategory='$category'";
}
$res = $conn->query($sql);
// Get cart for badge counts
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
?>

<div class="baby-products-page">
    <div class="page-header">
        <h1>Baby Products</h1>
        <p>Essential products for your little ones - Safe, gentle, and trusted</p>
    </div>

    <div class="filters-section">
        <form action="search.php" method="GET" class="search-form">
            <div class="search-input">
                <i class="fas fa-search"></i>
                <input type="text" name="query" placeholder="Search baby products..." required>
            </div>
            <button type="submit">Search</button>
        </form>
        
        <form method="GET" class="filter-form">
            <select name="category" onchange="this.form.submit()">
                <option value="">All Baby Products</option>
                <option value="Baby Food" <?php echo $category == 'Baby Food' ? 'selected' : ''; ?>>Baby Food</option>
                <option value="Baby Care" <?php echo $category == 'Baby Care' ? 'selected' : ''; ?>>Baby Care</option>
                <option value="Baby Diapers" <?php echo $category == 'Baby Diapers' ? 'selected' : ''; ?>>Baby Diapers</option>
                <option value="Baby Toys" <?php echo $category == 'Baby Toys' ? 'selected' : ''; ?>>Baby Toys</option>
                <option value="Baby Clothing" <?php echo $category == 'Baby Clothing' ? 'selected' : ''; ?>>Baby Clothing</option>
            </select>
        </form>
    </div>

    <div class="products-grid">
        <?php while($row = $res->fetch_assoc()): 
            $productId = $row['id'];
            $productCount = isset($cart[$productId]) ? $cart[$productId] : 0;
        ?>
        
            <div class="product-card">
                <div class="product-image">
                    <img src="img/<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>">
                    <div class="baby-safe-badge">
                        <i class="fas fa-shield-alt"></i> Baby Safe
                    </div>
                </div>
                <div class="product-info">
                    <h3><?php echo $row['name']; ?></h3>
                    <p class="product-description"><?php echo htmlspecialchars($row['description']); ?></p>
                    <p class="product-category"><?php echo $row['category']; ?></p>
                    <p class="product-price">₹<?php echo $row['price']; ?></p>
                    <?php if ($row['stock'] <= 0): ?>
                        <p class="product-stock out-of-stock">Out of Stock</p>
                        <button class="add-to-cart-btn disabled" disabled>
                            <i class="fas fa-times-circle"></i> Out of Stock
                        </button>
                    <?php else: ?>
                        <p class="product-stock">Stock: <?php echo $row['stock']; ?> units</p>
                        <button class="add-to-cart-btn" onclick="addToCart(<?php echo $productId; ?>, this)" data-product-id="<?php echo $productId; ?>">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                            <span class="product-cart-badge" id="product-badge-<?php echo $productId; ?>" style="<?php echo $productCount > 0 ? '' : 'display:none;'; ?>">
                                <?php echo $productCount; ?>
                            </span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <div class="baby-care-tips-section">
        <div class="tips-container">
            <h2><i class="fas fa-heart"></i> Baby Care Tips</h2>
            <div class="tips-grid">
                <div class="tip-card">
                    <i class="fas fa-thermometer-half"></i>
                    <h3>Temperature Check</h3>
                    <p>Always check the temperature of baby food and bath water before use.</p>
                </div>
                <div class="tip-card">
                    <i class="fas fa-clock"></i>
                    <h3>Regular Feeding</h3>
                    <p>Maintain regular feeding schedules and monitor your baby's growth.</p>
                </div>
                <div class="tip-card">
                    <i class="fas fa-baby"></i>
                    <h3>Gentle Care</h3>
                    <p>Use gentle, hypoallergenic products designed specifically for babies.</p>
                </div>
                <div class="tip-card">
                    <i class="fas fa-shield-alt"></i>
                    <h3>Safety First</h3>
                    <p>Keep all baby products out of reach and always supervise during use.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Simple and Clean Baby Products Page */
body {
    background: linear-gradient(135deg, #ff69b4 0%, #ff1493 25%, #dc143c 50%, #ff69b4 75%, #ff1493 100%);
    min-height: 100vh;
}

.baby-products-page {
    padding: 2rem;
    max-width: 1200px;
    margin: 0 auto;
    background: rgba(255, 192, 203, 0.1);
    border-radius: 15px;
    box-shadow: 0 0 30px rgba(255, 182, 193, 0.3);
}

.page-header {
    text-align: center;
    margin-bottom: 2rem;
    padding: 2rem;
    background: rgba(255, 192, 203, 0.3);
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(255, 182, 193, 0.3);
    border: 1px solid rgba(255, 182, 193, 0.5);
}

.page-header h1 {
    font-size: 2.5rem;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.page-header p {
    color: #6c757d;
    font-size: 1.1rem;
}

.filters-section {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
}

.search-form {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: rgba(255, 255, 255, 0.9);
    padding: 0.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(255, 182, 193, 0.3);
    border: 1px solid rgba(255, 192, 203, 0.5);
}

.search-input {
    position: relative;
    display: flex;
    align-items: center;
}

.search-input i {
    position: absolute;
    left: 0.8rem;
    color: #6c757d;
    font-size: 1rem;
}

.search-input input {
    padding: 0.8rem 0.8rem 0.8rem 2.5rem;
    border: none;
    border-radius: 5px;
    font-size: 1rem;
    width: 300px;
    background: rgba(255, 192, 203, 0.1);
}

.search-input input:focus {
    outline: none;
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(255, 182, 193, 0.5);
}

.search-form button {
    padding: 0.8rem 1.5rem;
    background: linear-gradient(135deg, #ffb6c1, #ffa0b4);
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 600;
    transition: background 0.3s ease;
    text-shadow: 0 1px 2px rgba(0,0,0,0.2);
}

.search-form button:hover {
    background: linear-gradient(135deg, #ffa0b4, #ff8fa3);
}

.filter-form select {
    padding: 0.8rem 1rem;
    border: 1px solid rgba(255, 192, 203, 0.5);
    border-radius: 5px;
    font-size: 1rem;
    background: rgba(255, 255, 255, 0.9);
    cursor: pointer;
    box-shadow: 0 2px 5px rgba(255, 182, 193, 0.2);
}

.filter-form select:focus {
    outline: none;
    border-color: rgba(255, 182, 193, 0.8);
    background: white;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.product-card {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(255, 182, 193, 0.2);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: 1px solid rgba(255, 192, 203, 0.3);
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(255, 182, 193, 0.4);
}

.product-image {
    position: relative;
    margin-bottom: 1rem;
    border-radius: 8px;
    overflow: hidden;
    height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.baby-safe-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #28a745;
    color: white;
    padding: 0.3rem 0.8rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
}

.product-info h3 {
    font-size: 1.2rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.product-description {
    color: #6c757d;
    margin: 0.4rem 0 0.8rem 0;
    line-height: 1.5;
    font-size: 0.95rem;
}

.product-category {
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.product-price {
    font-size: 1.4rem;
    font-weight: 700;
    color: #e74c3c;
    margin-bottom: 0.5rem;
}

.product-stock {
    font-size: 0.9rem;
    color: #28a745;
    font-weight: 500;
    margin-bottom: 1rem;
}

.product-stock.out-of-stock {
    color: #dc3545;
}

.add-to-cart-btn {
    width: 100%;
    padding: 0.8rem 1rem;
    background: linear-gradient(135deg, #ffb6c1, #ffa0b4);
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 600;
    font-size: 1rem;
    transition: background 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    position: relative;
    text-shadow: 0 1px 2px rgba(0,0,0,0.2);
    box-shadow: 0 2px 5px rgba(255, 182, 193, 0.3);
}

.add-to-cart-btn:hover {
    background: linear-gradient(135deg, #ffa0b4, #ff8fa3);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(255, 182, 193, 0.4);
}

.add-to-cart-btn.disabled {
    background: #6c757d;
    cursor: not-allowed;
}

.add-to-cart-btn.disabled:hover {
    background: #6c757d;
}

.product-cart-badge {
    background: #dc3545;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    font-weight: 700;
    position: absolute;
    top: -8px;
    right: -8px;
}

.baby-care-tips-section {
    background: rgba(255, 192, 203, 0.2);
    margin: 2rem 0;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(255, 182, 193, 0.3);
    border: 1px solid rgba(255, 182, 193, 0.4);
}

.tips-container h2 {
    text-align: center;
    font-size: 2rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 2rem;
}

.tips-container h2 i {
    color: #e74c3c;
    margin-right: 0.5rem;
}

.tips-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.tip-card {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    text-align: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
    border: 1px solid #e9ecef;
}

.tip-card:hover {
    transform: translateY(-3px);
}

.tip-card i {
    font-size: 2.5rem;
    color: #007bff;
    margin-bottom: 1rem;
}

.tip-card h3 {
    font-size: 1.2rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.8rem;
}

.tip-card p {
    color: #6c757d;
    line-height: 1.5;
}

/* Responsive Design */
@media (max-width: 768px) {
    .baby-products-page {
        padding: 1rem;
    }
    
    .page-header h1 {
        font-size: 2rem;
    }
    
    .filters-section {
        flex-direction: column;
        gap: 1rem;
    }
    
    .search-input input {
        width: 250px;
    }
    
    .products-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .tips-grid {
        grid-template-columns: 1fr;
    }
}

/* Loading and Success States */
.add-to-cart-btn.loading {
    background: #6c757d;
    cursor: not-allowed;
}

.add-to-cart-btn.success {
    background: #28a745;
}

/* Notification */
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: #28a745;
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 5px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    z-index: 1000;
    transform: translateX(400px);
    transition: transform 0.3s ease;
    font-weight: 600;
}

.notification.show {
    transform: translateX(0);
}

/* Cart Badge */
.cart-badge {
    background: #dc3545;
    color: white;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    font-weight: 700;
    position: absolute;
    top: -8px;
    right: -8px;
}
</style>

<script>
function addToCart(productId, btn) {
    btn.disabled = true;
    btn.classList.add('loading');
    var original = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    
    // Add a subtle animation to the product card
    const productCard = btn.closest('.product-card');
    productCard.style.transform = 'scale(0.98)';
    
    fetch('add_to_cart_ajax.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'product_id=' + productId
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.classList.remove('loading');
        btn.innerHTML = original;

        // Reset card animation
        productCard.style.transform = '';

        if (data.success) {
            btn.classList.add('success');
            setTimeout(() => btn.classList.remove('success'), 1000);

            // Update cart count in header
            updateCartCount(data.cart_count);

            // Update product badge
            updateProductBadge(productId);

            // If server returned available = 0, disable button and show out-of-stock
            if (typeof data.available !== 'undefined' && data.available <= 0) {
                // Replace button with disabled out-of-stock state
                btn.classList.add('disabled');
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-times-circle"></i> Out of Stock';
                // Update stock label if present
                const stockLabel = productCard.querySelector('.product-stock');
                if (stockLabel) stockLabel.textContent = 'Stock: 0 units';
            }

            // Show notification
            showNotification(data.message || 'Product added to cart!');
        } else {
            // If available is provided, optionally update UI
            if (typeof data.available !== 'undefined' && data.available == 0) {
                // Disable button
                btn.classList.add('disabled');
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-times-circle"></i> Out of Stock';
                const stockLabel = productCard.querySelector('.product-stock');
                if (stockLabel) stockLabel.textContent = 'Stock: 0 units';
            }

            showNotification(data.message || 'Error adding product to cart', 'error');
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.classList.remove('loading');
        btn.innerHTML = original;
        productCard.style.transform = '';
        showNotification('Error adding product to cart', 'error');
    });
}

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}`;
    
    if (type === 'error') {
        notification.style.background = '#dc3545';
    }
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Animate out and remove
    setTimeout(() => {
        notification.style.transform = 'translateX(400px)';
        setTimeout(() => notification.remove(), 300);
    }, 2500);
}

function updateCartCount(count) {
    const cartLink = document.querySelector('a[href="cart.php"]');
    let badge = cartLink.querySelector('.cart-badge');
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

function updateProductBadge(productId) {
    // Find the badge for this product
    var badge = document.getElementById('product-badge-' + productId);
    if (badge) {
        let current = parseInt(badge.textContent) || 0;
        badge.textContent = current + 1;
        badge.style.display = '';
    }
}
</script>

<?php include 'footer.php'; ?> 