<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Simple admin credentials (in production, use proper authentication)
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin_dashboard.php');
        exit();
    } else {
        $error = "Invalid credentials!";
    }
}

$page_title = "Admin Login - Medico";
include 'header.php';
?>

<div class="auth-page">
    <div class="auth-container">
        <div class="auth-card card animate-fade-in-up">
            <div class="auth-header">
                <i class="fas fa-user-shield"></i>
                <h2>Admin Login</h2>
                <p>Access the admin dashboard to manage your store</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="username" class="form-label">
                        <i class="fas fa-user"></i> Username
                    </label>
                    <input type="text" id="username" name="username" class="form-input" placeholder="Enter username" required>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input type="password" id="password" name="password" class="form-input" placeholder="Enter password" required>
                </div>

                <button type="submit" class="btn btn-primary auth-btn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>

            <div class="auth-footer">
                <a href="index.php" class="back-home">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>
            </div>
        </div>
    </div>
</div>

<style>
/* Override purple colors with blue for admin login page */
.auth-page {
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

/* Blue color overrides for admin login */
.auth-page .auth-header i {
    color: #667eea !important;
}

.auth-page .form-label i {
    color: #667eea !important;
}

.auth-page .btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

.auth-page .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3) !important;
}

.auth-page .form-input:focus {
    border-color: #667eea !important;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
}

.auth-container {
    width: 100%;
    max-width: 400px;
    margin: 0 auto;
}

.auth-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: var(--border-radius-lg);
    padding: 2.5rem;
    box-shadow: var(--shadow-heavy);
    text-align: center;
    backdrop-filter: blur(15px);
}

.auth-header {
    margin-bottom: 2rem;
}

.auth-header i {
    font-size: 3rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.auth-header h2 {
    font-size: 2rem;
    color: var(--dark-text);
    margin-bottom: 0.5rem;
    font-weight: 700;
}

.auth-header p {
    color: var(--light-text);
    font-size: 1rem;
}

.auth-form {
    text-align: left;
}

.auth-btn {
    width: 100%;
    margin-top: 1rem;
}

.auth-footer {
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e2e8f0;
}

.back-home {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--light-text) !important;
    font-size: 0.9rem;
    text-decoration: none;
    transition: var(--transition);
}

.back-home:hover {
    color: var(--dark-text) !important;
}

@media (max-width: 480px) {
    .auth-card {
        padding: 2rem;
        margin: 1rem;
    }
    
    .auth-header h2 {
        font-size: 1.5rem;
    }
}
</style>

<?php include 'footer.php'; ?> 