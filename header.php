<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Medicine Store'; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="responsive.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            width: 100%;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        
        .header {
           background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        /* Top Bar with Centered Logo */
        .top-bar {
            background: rgba(255, 255, 255, 0.1);
            padding: 0.5rem 0;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .logo {
            font-size: 2.2rem;
            font-weight: 800;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: transform 0.3s ease;
        }
        
        .logo:hover {
            transform: scale(1.05);
        }
        
        .logo i {
            color: #ffd700;
            font-size: 2.4rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        /* Main Navigation */
        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            min-height: 60px;
        }
        
        /* Primary Navigation */
        .nav-menu {
            display: flex;
            list-style: none;
            gap: 0.3rem;
            align-items: center;
            margin: 0;
            padding: 0;
            flex-wrap: wrap;
        }
        
        .nav-menu a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 0.6rem 1rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.9rem;
            position: relative;
            overflow: hidden;
            white-space: nowrap;
        }
        
        .nav-menu a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s;
        }
        
        .nav-menu a:hover::before {
            left: 100%;
        }
        
        .nav-menu a:hover {
            background: rgba(255,255,255,0.15);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .nav-menu a.active {
            background: rgba(255,255,255,0.25);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transform: translateY(-1px);
        }
        
        .nav-menu a i {
            font-size: 1rem;
        }
        
        /* User Menu */
        .user-menu {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .user-menu a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 0.6rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.9rem;
            position: relative;
            overflow: hidden;
            white-space: nowrap;
        }
        
        .user-menu a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s;
        }
        
        .user-menu a:hover::before {
            left: 100%;
        }
        
        .user-menu a:hover {
            background: rgba(255,255,255,0.15);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .admin-link {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
            color: #333 !important;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(255, 215, 0, 0.3);
        }
        
        .admin-link:hover {
            background: linear-gradient(135deg, #ffed4e 0%, #ffd700 100%) !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 215, 0, 0.4);
        }
        
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .mobile-menu-btn:hover {
            background: rgba(255,255,255,0.15);
            transform: scale(1.1);
        }
        
        .cart-badge {
            background: linear-gradient(135deg, #ff4757 0%, #ff3742 100%);
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.7rem;
            font-weight: bold;
            margin-left: 5px;
            display: inline-block;
            min-width: 18px;
            text-align: center;
            animation: badge-pulse 2s infinite;
        }
        
        @keyframes badge-pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        /* Mobile Responsive Design */
        @media (max-width: 1200px) {
            .nav-container {
                padding: 0 1.5rem;
            }
            
            .nav-menu a,
            .user-menu a {
                padding: 0.5rem 0.8rem;
                font-size: 0.85rem;
            }
            
            .logo {
                font-size: 2rem;
            }
            
            .logo i {
                font-size: 2.2rem;
            }
        }
        
        @media (max-width: 992px) {
            .nav-menu a,
            .user-menu a {
                padding: 0.4rem 0.7rem;
                font-size: 0.8rem;
            }
            
            .nav-menu a i,
            .user-menu a i {
                font-size: 0.9rem;
            }
        }
        
        @media (max-width: 768px) {
            .nav-menu {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
                flex-direction: column;
                padding: 1rem;
                gap: 0.3rem;
                box-shadow: 0 8px 25px rgba(0,0,0,0.2);
                border-top: 1px solid rgba(255,255,255,0.1);
                backdrop-filter: blur(10px);
            }
            
            .nav-menu.active {
                display: flex;
                animation: slideDown 0.3s ease-out;
            }
            
            @keyframes slideDown {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .nav-menu a,
            .user-menu a {
                width: 100%;
                justify-content: center;
                padding: 0.8rem;
                border-radius: 8px;
                margin: 0.1rem 0;
                font-size: 0.9rem;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .nav-container {
                position: relative;
                padding: 0 1rem;
            }
            
            .user-menu {
                display: none;
            }
            
            .user-menu.mobile-visible {
                display: flex;
                flex-direction: column;
                position: absolute;
                top: 100%;
                right: 0;
                background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
                padding: 1rem;
                border-radius: 0 0 12px 12px;
                box-shadow: 0 8px 25px rgba(0,0,0,0.2);
                min-width: 180px;
                animation: slideDown 0.3s ease-out;
            }
        }
        
        @media (max-width: 480px) {
            .nav-container {
                padding: 0 0.8rem;
                min-height: 50px;
            }
            
            .logo {
                font-size: 1.8rem;
            }
            
            .logo i {
                font-size: 2rem;
            }
            
            .nav-menu a,
            .user-menu a {
                font-size: 0.8rem;
                padding: 0.7rem;
            }
            
            .mobile-menu-btn {
                font-size: 1.3rem;
            }
            
            .container {
                padding: 1rem;
                margin: 0.5rem;
            }
        }
        
        /* Background responsive adjustments */
        @media (max-width: 768px) {
            body {
                background-attachment: scroll;
            }
            
            .main-content {
                background: rgba(255, 255, 255, 0.95);
            }
            
            .container {
                width: 98%;
                padding: 1.5rem;
                margin: 0.5rem auto;
            }
        }
        
        @media (max-width: 480px) {
            .container {
                width: 100%;
                padding: 1rem;
                margin: 0;
                border-radius: 0;
            }
        }
        
        @media (min-width: 1200px) {
            .container {
                width: 90%;
                max-width: 1600px;
            }
        }
        
        .main-content {
            flex: 1;
            padding: 1rem 0;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            min-height: calc(100vh - 130px);
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 2rem;
            margin: 1rem auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(15px);
            max-width: 1400px;
            width: 95%;
        }
        
        /* Smooth transitions for all interactive elements */
        .nav-menu a,
        .user-menu a,
        .mobile-menu-btn,
        .logo {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
</head>
<body>
    <header class="header">
        <!-- Top Bar with Centered Logo -->
        <div class="top-bar">
            <a href="index.php" class="logo">
                <i class="fas fa-pills"></i>
                <span>Medico</span>
            </a>
        </div>
        
        <!-- Main Navigation -->
        <div class="nav-container">
            <nav>
                <ul class="nav-menu">
                    <li><a href="index.php" <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'class="active"' : ''; ?>>
                        <i class="fas fa-home"></i> Home
                    </a></li>
                    <li><a href="products.php" <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'class="active"' : ''; ?>>
                        <i class="fas fa-shopping-bag"></i> Products
                    </a></li>
                    <li><a href="baby_products.php" <?php echo basename($_SERVER['PHP_SELF']) == 'baby_products.php' ? 'class="active"' : ''; ?>>
                        <i class="fas fa-baby"></i> Baby
                    </a></li>
                    <li><a href="veterinary.php" <?php echo basename($_SERVER['PHP_SELF']) == 'veterinary.php' ? 'class="active"' : ''; ?>>
                        <i class="fas fa-paw"></i> Veterinary
                    </a></li>
                    <li><a href="injections.php" <?php echo basename($_SERVER['PHP_SELF']) == 'injections.php' ? 'class="active"' : ''; ?>>
                        <i class="fas fa-syringe"></i> Injections
                    </a></li>
                </ul>
            </nav>
            
            <div class="user-menu" id="userMenu">
                <a href="cart.php" <?php echo basename($_SERVER['PHP_SELF']) == 'cart.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-shopping-cart"></i> Cart
                </a>
                <a href="checkout.php" <?php echo basename($_SERVER['PHP_SELF']) == 'checkout.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-credit-card"></i> Checkout
                </a>
                <?php if(isset($_SESSION['user_email'])): ?>
                    <a href="order_history.php" <?php echo basename($_SERVER['PHP_SELF']) == 'order_history.php' ? 'class="active"' : ''; ?>>
                        <i class="fas fa-history"></i> Orders
                    </a>
                    <a href="logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="login.php">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="signup.php">
                        <i class="fas fa-user-plus"></i> Signup
                    </a>
                <?php endif; ?>
                <a href="admin_login.php" class="admin-link">
                    <i class="fas fa-user-shield"></i> Admin
                </a>
            </div>
            
            <button class="mobile-menu-btn" onclick="toggleMobileMenu()" aria-label="Toggle mobile menu">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>
    
    <div class="main-content">
        <div class="container">
            
            <script>
                function toggleMobileMenu() {
                    const navMenu = document.querySelector('.nav-menu');
                    const userMenu = document.getElementById('userMenu');
                    const mobileBtn = document.querySelector('.mobile-menu-btn i');
                    
                    navMenu.classList.toggle('active');
                    
                    // Toggle user menu on mobile
                    if (window.innerWidth <= 768) {
                        userMenu.classList.toggle('mobile-visible');
                    }
                    
                    // Change icon
                    if (navMenu.classList.contains('active')) {
                        mobileBtn.className = 'fas fa-times';
                    } else {
                        mobileBtn.className = 'fas fa-bars';
                    }
                }
                
                // Close mobile menu when clicking outside
                document.addEventListener('click', function(event) {
                    const navMenu = document.querySelector('.nav-menu');
                    const userMenu = document.getElementById('userMenu');
                    const mobileBtn = document.querySelector('.mobile-menu-btn');
                    
                    if (!navMenu.contains(event.target) && !mobileBtn.contains(event.target)) {
                        navMenu.classList.remove('active');
                        userMenu.classList.remove('mobile-visible');
                        mobileBtn.querySelector('i').className = 'fas fa-bars';
                    }
                });
                
                // Handle window resize
                window.addEventListener('resize', function() {
                    const navMenu = document.querySelector('.nav-menu');
                    const userMenu = document.getElementById('userMenu');
                    const mobileBtn = document.querySelector('.mobile-menu-btn i');
                    
                    if (window.innerWidth > 768) {
                        navMenu.classList.remove('active');
                        userMenu.classList.remove('mobile-visible');
                        mobileBtn.className = 'fas fa-bars';
                    }
                });
            </script> 