<?php 
include 'db.php'; 
session_start();
$page_title = "Products - Medico";
include 'header.php';

$category = $_GET['category'] ?? '';
$sql = "SELECT * FROM products";
if ($category) {
    $sql .= " WHERE category='$category'";
}
$res = $conn->query($sql);
// Get cart for badge counts
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
?>

<div class="products-page">
    <div class="page-header">
        <h1>Our Products</h1>
        <p>Browse our wide range of medicines and healthcare products</p>
    </div>

    <div class="filters-section">
        <form action="search.php" method="GET" class="search-form">
            <div class="search-input">
                <i class="fas fa-search"></i>
                <input type="text" name="query" placeholder="Search medicine..." required>
            </div>
            <button type="submit">Search</button>
        </form>
        
        <form method="GET" class="filter-form">
            <select name="category" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <option value="Tablets" <?php echo $category == 'Tablets' ? 'selected' : ''; ?>>Tablets</option>
                <option value="Syrups" <?php echo $category == 'Syrups' ? 'selected' : ''; ?>>Syrups</option>
                <option value="Supplements" <?php echo $category == 'Supplements' ? 'selected' : ''; ?>>Supplements</option>
                <option value="Creams" <?php echo $category == 'Creams' ? 'selected' : ''; ?>>Creams</option>
                <option value="Equipments" <?php echo $category == 'Equipments' ? 'selected' : ''; ?>>Equipments</option>
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
                </div>
                <div class="product-info">
                    <h3><?php echo $row['name']; ?></h3>
                    <p class="product-category"><?php echo $row['category']; ?></p>
                    
                    <!-- Product Rating Display -->
                    <?php if (isset($row['average_rating']) && $row['average_rating'] > 0): ?>
                        <div class="product-rating">
                            <div class="star-display">
                                <?php 
                                $rating = $row['average_rating'];
                                $full_stars = floor($rating);
                                $has_half_star = ($rating - $full_stars) >= 0.5;
                                
                                for ($i = 1; $i <= 5; $i++): 
                                    if ($i <= $full_stars): ?>
                                        <i class="fas fa-star"></i>
                                    <?php elseif ($i == $full_stars + 1 && $has_half_star): ?>
                                        <i class="fas fa-star-half-alt"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif;
                                endfor; ?>
                            </div>
                            <span class="rating-text"><?php echo number_format($rating, 1); ?> (<?php echo $row['total_reviews'] ?? 0; ?> reviews)</span>
                        </div>
                    <?php else: ?>
                        <div class="product-rating no-rating">
                            <div class="star-display">
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                            </div>
                            <span class="rating-text">No reviews yet</span>
                        </div>
                    <?php endif; ?>
                    
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
                
                <!-- Reviews Section -->
                <div class="reviews-section" id="reviews-<?php echo $productId; ?>">
                    <div class="reviews-header">
                        <h4><i class="fas fa-star"></i> Customer Reviews</h4>
                        <div class="reviews-stats" id="stats-<?php echo $productId; ?>">
                            <div class="loading-reviews">Loading reviews...</div>
                        </div>
                    </div>
                    <div class="reviews-container" id="reviews-container-<?php echo $productId; ?>">
                        <!-- Reviews will be loaded here via AJAX -->
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <div class="prescription-upload-section">
        <div class="prescription-upload-container">
            <h2><i class="fas fa-prescription-bottle-alt"></i> Upload Prescription</h2>
            <p>Upload your prescription and we'll get back to you with the medicines</p>
            <form action="prescription_upload.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="prescription_file">Choose Prescription (PDF/JPG/PNG):</label>
                    <input type="file" name="prescription_file" id="prescription_file" accept=".pdf,image/*" required>
                </div>
                <div class="form-group">
                    <label for="user_email">Your Email:</label>
                    <input type="email" name="user_email" id="user_email" required>
                </div>
                <button type="submit" class="upload-btn">
                    <i class="fas fa-upload"></i> Upload Prescription
                </button>
            </form>
        </div>
    </div>
</div>

<style>
/* Modern CSS Reset and Base Styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
    background-attachment: fixed;
    background-size: cover;
    background-position: center;
    min-height: 100vh;
    margin: 0;
    padding: 0;
}

.products-page {
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
    font-size: 3.5rem;
    color: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    margin-bottom: 0.5rem;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    font-weight: 800;
    letter-spacing: 2px;
}

.page-header p {
    color:purple;
    font-size: 1.3rem;
    font-weight: 300;
}

.filters-section {
    display: flex;
    gap: 2rem;
    margin-bottom: 3rem;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
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
}

.search-input input {
    padding: 1rem 1rem 1rem 3rem;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 25px;
    font-size: 1rem;
    width: 350px;
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

.search-input input::placeholder {
    color: #6b7280;
    font-style: italic;
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
    border-color: rgba(255, 255, 255, 0.5);
}

.filter-form select {
    padding: 1rem 2rem;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 25px;
    font-size: 1rem;
    background: rgba(255, 255, 255, 0.95);
    cursor: pointer;
    backdrop-filter: blur(10px);
    color: #333;
    font-weight: 500;
    transition: all 0.3s ease;
    box-shadow: 0 8px 32px rgba(0,0,0,0.1);
}

.filter-form select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 8px 32px rgba(102, 126, 234, 0.2);
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

.product-image {
    position: relative;
    overflow: hidden;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}



.product-description {
    color: #666;
    font-size: 0.9rem;
    line-height: 1.4;
    margin: 8px 0;
    max-height: 60px;
    overflow: hidden;
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

.product-rating {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.8rem;
}

.star-display {
    display: flex;
    gap: 2px;
}

.star-display i {
    font-size: 1rem;
    color: #f39c12;
}

.star-display .far {
    color: #ddd;
}

.rating-text {
    font-size: 0.9rem;
    color: #7f8c8d;
    font-weight: 500;
}

.product-rating.no-rating .star-display i {
    color: #ddd;
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

.add-to-cart-btn:active {
    transform: translateY(0);
}

.prescription-upload-section {
    background: rgba(255, 255, 255, 0.1);
    padding: 4rem 0;
    margin-top: 4rem;
    backdrop-filter: blur(10px);
    border-top: 1px solid rgba(255, 255, 255, 0.2);
}

.prescription-upload-container {
    max-width: 600px;
    margin: 0 auto;
    background: rgba(255, 255, 255, 0.95);
    padding: 3rem;
    border-radius: 25px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    text-align: center;
}

.prescription-upload-container h2 {
    color: #2c3e50;
    margin-bottom: 1rem;
    font-size: 2rem;
    font-weight: 700;
}

.prescription-upload-container h2 i {
    color: #667eea;
    margin-right: 0.5rem;
}

.prescription-upload-container p {
    color: #7f8c8d;
    margin-bottom: 2.5rem;
    font-size: 1.1rem;
    line-height: 1.6;
}

.form-group {
    margin-bottom: 2rem;
    text-align: left;
}

.form-group label {
    display: block;
    margin-bottom: 0.8rem;
    font-weight: 600;
    color: #2c3e50;
    font-size: 1.1rem;
}

.form-group input {
    width: 100%;
    padding: 1rem 1.5rem;
    border: 2px solid #e1e8ed;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.9);
}

.form-group input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    background: white;
}

.upload-btn {
    width: 100%;
    padding: 1.2rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 1.1rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.upload-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4);
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

/* Loading animation for add to cart button */
.add-to-cart-btn.loading {
    background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
    cursor: not-allowed;
}

.add-to-cart-btn.loading i {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

@keyframes success-pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

/* Responsive Design */
@media (max-width: 768px) {
    .page-header h1 {
        font-size: 2.5rem;
    }
    
    .page-header p {
        font-size: 1.1rem;
    }
    
    .filters-section {
        flex-direction: column;
        align-items: stretch;
        padding: 0 1rem;
    }
    
    .search-input input {
        width: 100%;
    }
    
    .products-grid {
        grid-template-columns: 1fr;
        padding: 0 1rem;
        gap: 2rem;
    }
    
    .prescription-upload-container {
        margin: 0 1rem;
        padding: 2rem;
    }
    

}

@media (max-width: 480px) {
    .page-header {
        margin: 0 1rem 2rem 1rem;
        padding: 1.5rem 0;
    }
    
    .page-header h1 {
        font-size: 2rem;
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
    

}

/* Reviews Section Styles */
.reviews-section {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid rgba(102, 126, 234, 0.2);
}

.reviews-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.reviews-header h4 {
    color: #2c3e50;
    font-size: 1.2rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0;
}

.reviews-header h4 i {
    color: #ffd700;
}

.reviews-stats {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.rating-summary {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: rgba(102, 126, 234, 0.1);
    padding: 0.5rem 1rem;
    border-radius: 20px;
    border: 1px solid rgba(102, 126, 234, 0.2);
}

.rating-summary .stars {
    display: flex;
    gap: 0.2rem;
}

.rating-summary .stars i {
    color: #ffd700;
    font-size: 1rem;
}

.rating-summary .rating-text {
    color: #2c3e50;
    font-weight: 600;
    font-size: 0.9rem;
}

.loading-reviews {
    color: #7f8c8d;
    font-style: italic;
    padding: 1rem;
    text-align: center;
}

.reviews-container {
    max-height: 300px;
    overflow-y: auto;
    padding-right: 0.5rem;
}

.review-item {
    background: rgba(255, 255, 255, 0.7);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    border: 1px solid rgba(102, 126, 234, 0.1);
    transition: all 0.3s ease;
}

.review-item:hover {
    background: rgba(255, 255, 255, 0.9);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.reviewer-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.reviewer-name {
    font-weight: 600;
    color: #2c3e50;
    font-size: 0.95rem;
}

.review-date {
    color: #7f8c8d;
    font-size: 0.85rem;
}

.review-rating {
    display: flex;
    gap: 0.2rem;
}

.review-rating i {
    color: #ffd700;
    font-size: 0.9rem;
}

.review-text {
    color: #2c3e50;
    line-height: 1.6;
    font-size: 0.95rem;
}

.no-reviews {
    text-align: center;
    color: #7f8c8d;
    font-style: italic;
    padding: 2rem;
    background: rgba(102, 126, 234, 0.05);
    border-radius: 12px;
    border: 1px dashed rgba(102, 126, 234, 0.2);
}

/* Custom scrollbar for reviews */
.reviews-container::-webkit-scrollbar {
    width: 6px;
}

.reviews-container::-webkit-scrollbar-track {
    background: rgba(102, 126, 234, 0.1);
    border-radius: 3px;
}

.reviews-container::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 3px;
}

.reviews-container::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a5acd 100%);
}

/* Responsive reviews */
@media (max-width: 768px) {
    .reviews-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .reviews-stats {
        width: 100%;
        justify-content: space-between;
    }
    
    .review-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .reviews-container {
        max-height: 250px;
    }
}

/* Smooth scrolling */
html {
    scroll-behavior: smooth;
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a5acd 100%);
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

// Load reviews for all products
function loadReviews() {
    const productCards = document.querySelectorAll('.product-card');
    productCards.forEach(card => {
        const productId = card.querySelector('[data-product-id]')?.getAttribute('data-product-id');
        if (productId) {
            loadProductReviews(productId);
        }
    });
}

function loadProductReviews(productId) {
    fetch(`get_reviews.php?product_id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error loading reviews:', data.error);
                // Hide reviews section if there's an error (likely table doesn't exist)
                const reviewsSection = document.getElementById(`reviews-${productId}`);
                if (reviewsSection) {
                    reviewsSection.style.display = 'none';
                }
                return;
            }
            
            updateReviewsDisplay(productId, data);
        })
        .catch(error => {
            console.error('Error loading reviews:', error);
            // Hide reviews section if there's an error
            const reviewsSection = document.getElementById(`reviews-${productId}`);
            if (reviewsSection) {
                reviewsSection.style.display = 'none';
            }
        });
}

function updateReviewsDisplay(productId, data) {
    const statsContainer = document.getElementById(`stats-${productId}`);
    const reviewsContainer = document.getElementById(`reviews-container-${productId}`);
    
    // Update stats
    if (data.stats.total_reviews > 0) {
        const stars = generateStars(data.stats.average_rating);
        statsContainer.innerHTML = `
            <div class="rating-summary">
                <div class="stars">${stars}</div>
                <span class="rating-text">${data.stats.average_rating}/5 (${data.stats.total_reviews} reviews)</span>
            </div>
        `;
    } else {
        statsContainer.innerHTML = '<div class="rating-summary"><span class="rating-text">No reviews yet</span></div>';
    }
    
    // Update reviews
    if (data.reviews.length > 0) {
        reviewsContainer.innerHTML = data.reviews.map(review => `
            <div class="review-item">
                <div class="review-header">
                    <div class="reviewer-info">
                        <span class="reviewer-name">${review.user_email}</span>
                        <span class="review-date">${formatDate(review.review_date)}</span>
                    </div>
                    <div class="review-rating">${generateStars(review.rating)}</div>
                </div>
                <div class="review-text">${review.review_text || 'No comment provided.'}</div>
            </div>
        `).join('');
    } else {
        reviewsContainer.innerHTML = '<div class="no-reviews">No reviews yet. Be the first to review this product!</div>';
    }
}

function generateStars(rating) {
    const fullStars = Math.floor(rating);
    const hasHalfStar = rating % 1 !== 0;
    const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
    
    let stars = '';
    
    // Full stars
    for (let i = 0; i < fullStars; i++) {
        stars += '<i class="fas fa-star"></i>';
    }
    
    // Half star
    if (hasHalfStar) {
        stars += '<i class="fas fa-star-half-alt"></i>';
    }
    
    // Empty stars
    for (let i = 0; i < emptyStars; i++) {
        stars += '<i class="far fa-star"></i>';
    }
    
    return stars;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}

// Load reviews when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadReviews();
});
</script>

<?php include 'footer.php'; ?>