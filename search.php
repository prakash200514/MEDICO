<?php
include 'db.php';
session_start();
$page_title = "Search Results - Medico";
include 'header.php';

$term = $_GET['query'] ?? '';
$result = $conn->query("SELECT * FROM products WHERE name LIKE '%$term%'");
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
?>

<div class="search-page">
    <div class="page-header">
        <h1><i class="fas fa-search"></i> Search Results</h1>
        <p>Results for: "<strong><?php echo htmlspecialchars($term); ?></strong>"</p>
    </div>

    <div class="search-form-section">
        <form action="search.php" method="GET" class="search-form">
            <div class="search-input">
                <i class="fas fa-search"></i>
                <input type="text" name="query" placeholder="Search medicine..." value="<?php echo htmlspecialchars($term); ?>" required>
            </div>
            <button type="submit">Search</button>
        </form>
    </div>

    <div class="search-results">
        <?php if ($result->num_rows > 0): ?>
            <div class="results-count">
                <p><i class="fas fa-info-circle"></i> Found <?php echo $result->num_rows; ?> result(s) for "<?php echo htmlspecialchars($term); ?>"</p>
            </div>
            
            <div class="products-grid">
                <?php while($row = $result->fetch_assoc()): 
                    $productId = $row['id'];
                    $productCount = isset($cart[$productId]) ? $cart[$productId] : 0;
                ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="img/<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>">
                        </div>
                        <div class="product-info">
                            <h3><?php echo $row['name']; ?></h3>
                            <p class="product-category"><?php echo $row['category']; ?></p>
                            <?php if (!empty($row['description'])): ?>
                                <p class="product-description"><?php echo substr($row['description'], 0, 100); ?><?php echo strlen($row['description']) > 100 ? '...' : ''; ?></p>
                            <?php endif; ?>
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
        <?php else: ?>
            <div class="no-results">
                <div class="no-results-content">
                    <i class="fas fa-search"></i>
                    <h3>No Results Found</h3>
                    <p>Sorry, we couldn't find any products matching "<strong><?php echo htmlspecialchars($term); ?></strong>"</p>
                    <div class="suggestions">
                        <h4>Try these suggestions:</h4>
                        <ul>
                            <li>Check your spelling</li>
                            <li>Try different keywords</li>
                            <li>Use more general terms</li>
                            <li>Browse our <a href="products.php">product categories</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="back-navigation">
        <a href="products.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>
</div>

<style>
/* Search Page Specific Styles */
.search-page {
    padding: 2rem 0;
    background: transparent;
    min-height: 100vh;
}

.page-header {
    text-align: center;
    margin-bottom: 3rem;
    padding: 2rem 0;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    margin: 0 2rem 3rem 2rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.page-header h1 {
    font-size: 3rem;
    color: white;
    margin-bottom: 0.5rem;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    font-weight: 800;
    letter-spacing: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
}

.page-header h1 i {
    color: #ffd700;
    font-size: 2.5rem;
}

.page-header p {
    color: white;
    font-size: 1.3rem;
    font-weight: 300;
}

.search-form-section {
    display: flex;
    justify-content: center;
    margin-bottom: 3rem;
    padding: 0 2rem;
}

.search-form {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: linear-gradient(135deg, rgba(30, 144, 255, 0.9) 0%, rgba(65, 105, 225, 0.9) 100%);
    padding: 0.5rem;
    border-radius: 50px;
    box-shadow: 0 8px 32px rgba(30, 144, 255, 0.3);
    backdrop-filter: blur(10px);
    border: 2px solid rgba(135, 206, 250, 0.5);
}

.search-input {
    position: relative;
    display: flex;
    align-items: center;
}

.search-input i {
    position: absolute;
    left: 1rem;
    color: #ffffff;
    font-size: 1.2rem;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3);
    z-index: 1;
}

.search-input input {
    padding: 1rem 1rem 1rem 3rem;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 25px;
    font-size: 1rem;
    width: 400px;
    background: rgba(255, 255, 255, 0.9);
    color: #1e3a8a;
    font-weight: 500;
}

.search-input input:focus {
    outline: none;
    background: rgba(255, 255, 255, 1);
    border-color: #4169e1;
    box-shadow: 0 0 0 3px rgba(65, 105, 225, 0.2);
}

.search-form button {
    padding: 1rem 2rem;
    background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
    color: white;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 25px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(30, 64, 175, 0.4);
    text-shadow: 0 1px 2px rgba(0,0,0,0.2);
}

.search-form button:hover {
    transform: translateY(-2px);
    background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
    box-shadow: 0 8px 25px rgba(30, 64, 175, 0.5);
}

.results-count {
    text-align: center;
    margin-bottom: 2rem;
    padding: 0 2rem;
}

.results-count p {
    background: rgba(255, 255, 255, 0.9);
    padding: 1rem 2rem;
    border-radius: 25px;
    display: inline-block;
    color: #2c3e50;
    font-weight: 600;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    backdrop-filter: blur(10px);
}

.results-count i {
    color: #667eea;
    margin-right: 0.5rem;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 2.5rem;
    margin-bottom: 4rem;
    padding: 0 2rem;
}

.product-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    position: relative;
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

.product-image {
    height: 250px;
    overflow: hidden;
    position: relative;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.product-card:hover .product-image img {
    transform: scale(1.1);
}

.product-info {
    padding: 2rem;
    position: relative;
}

.product-info h3 {
    margin-bottom: 0.8rem;
    color: #2c3e50;
    font-size: 1.4rem;
    font-weight: 700;
    line-height: 1.3;
}

.product-category {
    color: #667eea;
    font-weight: 600;
    margin-bottom: 0.8rem;
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.product-description {
    color: #666;
    font-size: 0.9rem;
    line-height: 1.4;
    margin: 8px 0;
    max-height: 60px;
    overflow: hidden;
}

.product-price {
    font-size: 1.8rem;
    font-weight: 800;
    color: #27ae60;
    margin-bottom: 0.8rem;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
}

.product-stock {
    color: #7f8c8d;
    font-size: 0.95rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.product-stock::before {
    content: '📦';
    font-size: 1.1rem;
}

.product-stock.out-of-stock {
    color: #e74c3c;
    font-weight: 600;
}

.product-stock.out-of-stock::before {
    content: '❌';
    font-size: 1.1rem;
}

.add-to-cart-btn {
    display: inline-block;
    width: 100%;
    padding: 1rem;
    background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
    color: white;
    text-decoration: none;
    text-align: center;
    border-radius: 12px;
    font-weight: 700;
    font-size: 1.1rem;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    border: none;
    cursor: pointer;
    box-shadow: 0 8px 25px rgba(39, 174, 96, 0.3);
}

.add-to-cart-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.add-to-cart-btn:hover::before {
    left: 100%;
}

.add-to-cart-btn:hover {
    background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
    transform: translateY(-2px);
    box-shadow: 0 12px 35px rgba(39, 174, 96, 0.4);
}

.add-to-cart-btn.disabled {
    background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
    cursor: not-allowed;
    opacity: 0.7;
    box-shadow: none;
}

.add-to-cart-btn.disabled:hover {
    transform: none;
    box-shadow: none;
    background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
}

.product-cart-badge {
    background: linear-gradient(135deg, #ff4757 0%, #ff3742 100%);
    color: white;
    border-radius: 50%;
    padding: 4px 10px;
    font-size: 0.9rem;
    font-weight: bold;
    margin-left: 10px;
    display: inline-block;
    min-width: 26px;
    text-align: center;
    vertical-align: middle;
    box-shadow: 0 4px 12px rgba(255, 71, 87, 0.3);
    animation: badge-pulse 2s infinite;
}

@keyframes badge-pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.no-results {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 400px;
    padding: 2rem;
}

.no-results-content {
    text-align: center;
    background: rgba(255, 255, 255, 0.95);
    padding: 3rem;
    border-radius: 25px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    max-width: 500px;
}

.no-results-content i {
    font-size: 4rem;
    color: #95a5a6;
    margin-bottom: 1.5rem;
}

.no-results-content h3 {
    color: #2c3e50;
    font-size: 2rem;
    margin-bottom: 1rem;
    font-weight: 700;
}

.no-results-content p {
    color: #7f8c8d;
    font-size: 1.1rem;
    margin-bottom: 2rem;
    line-height: 1.6;
}

.suggestions {
    text-align: left;
    background: rgba(102, 126, 234, 0.1);
    padding: 1.5rem;
    border-radius: 15px;
    border-left: 4px solid #667eea;
}

.suggestions h4 {
    color: #667eea;
    margin-bottom: 1rem;
    font-size: 1.1rem;
}

.suggestions ul {
    list-style: none;
    padding: 0;
}

.suggestions li {
    color: #2c3e50;
    margin-bottom: 0.5rem;
    padding-left: 1.5rem;
    position: relative;
}

.suggestions li::before {
    content: '•';
    color: #667eea;
    position: absolute;
    left: 0;
    font-weight: bold;
}

.suggestions a {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
}

.suggestions a:hover {
    text-decoration: underline;
}

.back-navigation {
    text-align: center;
    margin-top: 3rem;
    padding: 0 2rem;
}

.back-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 2rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-decoration: none;
    border-radius: 25px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.back-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4);
    color: white;
    text-decoration: none;
}

.back-btn i {
    font-size: 1.1rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .page-header {
        margin: 0 1rem 2rem 1rem;
        padding: 1.5rem 0;
    }
    
    .page-header h1 {
        font-size: 2rem;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .page-header p {
        font-size: 1.1rem;
    }
    
    .search-form-section {
        padding: 0 1rem;
    }
    
    .search-input input {
        width: 100%;
        min-width: 250px;
    }
    
    .search-form {
        flex-direction: column;
        width: 100%;
        max-width: 400px;
    }
    
    .search-form button {
        width: 100%;
    }
    
    .products-grid {
        grid-template-columns: 1fr;
        padding: 0 1rem;
        gap: 2rem;
    }
    
    .no-results-content {
        margin: 0 1rem;
        padding: 2rem;
    }
    
    .back-navigation {
        padding: 0 1rem;
    }
}

@media (max-width: 480px) {
    .page-header h1 {
        font-size: 1.8rem;
    }
    
    .product-info {
        padding: 1.5rem;
    }
    
    .product-info h3 {
        font-size: 1.2rem;
    }
    
    .product-price {
        font-size: 1.5rem;
    }
    
    .no-results-content {
        padding: 1.5rem;
    }
    
    .no-results-content i {
        font-size: 3rem;
    }
    
    .no-results-content h3 {
        font-size: 1.5rem;
    }
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
        productCard.style.transform = '';
        
        if (data.success) {
            showNotification(data.message, 'success');
            updateCartCount(data.cart_count);
            updateProductBadge(productId);
            
            // Add success animation to the product card
            productCard.style.animation = 'success-pulse 0.6s ease-in-out';
            setTimeout(() => {
                productCard.style.animation = '';
            }, 600);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.classList.remove('loading');
        btn.innerHTML = original;
        productCard.style.transform = '';
        showNotification('Error adding to cart.', 'error');
    });
}

function showNotification(message, type) {
    let existing = document.querySelector('.notification');
    if (existing) existing.remove();
    let n = document.createElement('div');
    n.className = 'notification notification-' + type;
    n.innerHTML = '<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + '"></i> ' + message;
    n.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? 'linear-gradient(135deg, #27ae60 0%, #2ecc71 100%)' : 'linear-gradient(135deg, #e74c3c 0%, #c0392b 100%)'};
        color: #fff;
        padding: 15px 25px;
        border-radius: 12px;
        z-index: 1000;
        font-weight: 600;
        box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        max-width: 350px;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.2);
        transform: translateX(400px);
        transition: transform 0.3s ease;
        font-size: 1rem;
    `;
    document.body.appendChild(n);
    
    // Animate in
    setTimeout(() => {
        n.style.transform = 'translateX(0)';
    }, 100);
    
    // Animate out and remove
    setTimeout(() => {
        n.style.transform = 'translateX(400px)';
        setTimeout(() => n.remove(), 300);
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

@keyframes success-pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}
</script>

<?php include 'footer.php'; ?>