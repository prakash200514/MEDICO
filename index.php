<?php 
session_start();
$page_title = "Home - Medico";
include 'header.php'; 
?>

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

.main-content {
    background: transparent;
}

.container {
    background: transparent;
}

.hero-section {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 20px;
    margin: 2rem auto;
    max-width: 1200px;
    padding: 3rem 2rem;
}

.features-section {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    margin: 2rem auto;
    max-width: 1200px;
    padding: 3rem 2rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.categories-section {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    margin: 2rem auto;
    max-width: 1200px;
    padding: 3rem 2rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.feature-card, .category-card {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.feature-card:hover, .category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

@media (max-width: 768px) {
    .hero-section, .features-section, .categories-section {
        margin: 1rem;
        padding: 2rem 1rem;
    }
}
</style>

<div class="hero-section">
    <div class="hero-content">
        <h1>Welcome to Medico</h1>
        <p>Your trusted online pharmacy for quality medicines and healthcare products</p>
        <div class="hero-buttons">
            <a href="products.php" class="btn-primary">Shop Now</a>
            <a href="login.php" class="btn-secondary">Login</a>
        </div>
    </div>
</div>

<div class="features-section">
    <div class="features-grid">
        <div class="feature-card">
            <i class="fas fa-shipping-fast"></i>
            <h3>Fast Delivery</h3>
            <p>Get your medicines delivered to your doorstep within 24 hours</p>
        </div>
        <div class="feature-card">
            <i class="fas fa-shield-alt"></i>
            <h3>Safe & Secure</h3>
            <p>All our products are genuine and FDA approved</p>
        </div>
        <div class="feature-card">
            <i class="fas fa-phone-alt"></i>
            <h3>24/7 Support</h3>
            <p>Round the clock customer support for all your queries</p>
        </div>
        <div class="feature-card">
            <i class="fas fa-prescription-bottle-alt"></i>
            <h3>Prescription Upload</h3>
            <p>Upload your prescription and get medicines delivered</p>
        </div>
    </div>
</div>

<div class="categories-section">
    <h2>Shop by Category</h2>
    <div class="categories-grid">
        <a href="products.php?category=Tablets" class="category-card">
            <i class="fas fa-tablets"></i>
            <h3>Tablets</h3>
        </a>
        <a href="products.php?category=Syrups" class="category-card">
            <i class="fas fa-prescription-bottle"></i>
            <h3>Syrups</h3>
        </a>
        <a href="products.php?category=Supplements" class="category-card">
            <i class="fas fa-capsules"></i>
            <h3>Supplements</h3>
        </a>
        <a href="products.php?category=Creams" class="category-card">
            <i class="fas fa-tube"></i>
            <h3>Creams</h3>
        </a>
    </div>
</div>

<style>
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 4rem 0;
    text-align: center;
    margin-bottom: 3rem;
}

.hero-content h1 {
    font-size: 3rem;
    margin-bottom: 1rem;
    font-weight: 700;
    color :white;
}

.hero-content p {
    font-size: 1.2rem;
    margin-bottom: 2rem;
    opacity: 0.9;
}

.hero-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-primary, .btn-secondary {
    padding: 1rem 2rem;
    text-decoration: none;
    border-radius: 50px;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-block;
}

.btn-primary {
    background: #ffd700;
    color: #333;
}

.btn-primary:hover {
    background: #ffed4e;
    transform: translateY(-2px);
}

.btn-secondary {
    background: transparent;
    color: white;
    border: 2px solid white;
}

.btn-secondary:hover {
    background: white;
    color: #667eea;
}

.features-section {
    padding: 3rem 0;
    background: white;
    margin-bottom: 3rem;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.feature-card {
    text-align: center;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.feature-card:hover {
    transform: translateY(-5px);
}

.feature-card i {
    font-size: 3rem;
    color: #667eea;
    margin-bottom: 1rem;
}

.feature-card h3 {
    margin-bottom: 1rem;
    color: #333;
}

.feature-card p {
    color: #666;
    line-height: 1.6;
}

.categories-section {
    padding: 3rem 0;
    background: #f8f9fa;
}

.categories-section h2 {
    text-align: center;
    margin-bottom: 2rem;
    color: #333;
    font-size: 2rem;
}

.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}

.category-card {
    background: white;
    padding: 2rem;
    text-align: center;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    text-decoration: none;
    color: #333;
    transition: all 0.3s ease;
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.category-card i {
    font-size: 3rem;
    color: #667eea;
    margin-bottom: 1rem;
}

.category-card h3 {
    margin: 0;
    font-weight: 600;
}

@media (max-width: 768px) {
    .hero-content h1 {
        font-size: 2rem;
    }
    
    .hero-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .features-grid {
        grid-template-columns: 1fr;
    }
    
    .categories-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<?php include 'footer.php'; ?>
