<?php
session_start();
include 'db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $stock = $_POST['stock'];
    $description = isset($_POST['description']) ? $_POST['description'] : '';
    
    // Handle first image upload
    $image_name = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            // Sanitize filename to prevent issues
            $safe_filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
            $image_name = time() . '_' . $safe_filename;
            
            // Ensure filename is not too long
            if (strlen($image_name) > 200) {
                $image_name = time() . '_' . substr($safe_filename, 0, 50) . '.' . $ext;
            }
            
            $upload_path = 'img/' . $image_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Image uploaded successfully
            } else {
                $message = "Error uploading first image!";
            }
        } else {
            $message = "Invalid image format! Only JPG, JPEG, PNG, GIF allowed.";
        }
    }
    

    
    if (empty($message)) {
        // Check if description column exists
        $column_check = $conn->query("SHOW COLUMNS FROM products LIKE 'description'");
        $description_exists = $column_check && $column_check->num_rows > 0;
        
        if ($description_exists) {
        $sql = "INSERT INTO products (name, price, category, stock, image, description) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdssss", $name, $price, $category, $stock, $image_name, $description);
        } else {
            $sql = "INSERT INTO products (name, price, category, stock, image) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sdsss", $name, $price, $category, $stock, $image_name);
        }
        
        if ($stmt->execute()) {
            $message = "Product added successfully!";
            // Clear form data
            $name = $price = $category = $stock = $description = '';
        } else {
            $message = "Error adding product: " . $stmt->error;
        }
    }
}
?>

<?php
$page_title = "Add Product - Admin";
include 'header.php';
?>

<div class="admin-container">
    <div class="admin-header card">
        <h1><i class="fas fa-plus-circle"></i> Add New Product</h1>
        <p>Add a new product to your medicine store inventory</p>
    </div>

    <div class="admin-nav">
        <a href="admin_dashboard.php" class="btn btn-secondary">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="admin_add_product.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Product
        </a>
        <a href="admin_add_veterinary.php" class="btn btn-info">
            <i class="fas fa-paw"></i> Add Veterinary
        </a>
        <a href="admin_add_injection.php" class="btn btn-warning">
            <i class="fas fa-syringe"></i> Add Injection
        </a>
        <a href="admin_add_baby.php" class="btn btn-pink">
            <i class="fas fa-baby"></i> Add Baby Product
        </a>
        <a href="products.php" class="btn btn-success">
            <i class="fas fa-store"></i> View Store
        </a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert <?php echo (strpos($message, 'successfully') !== false) ? 'alert-success' : 'alert-error'; ?>">
            <i class="fas <?php echo (strpos($message, 'successfully') !== false) ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="card animate-fade-in-up">
        <form method="POST" enctype="multipart/form-data">
            <div class="grid grid-2">
                <div class="form-group">
                    <label for="name" class="form-label">
                        <i class="fas fa-pills"></i> Product Name
                    </label>
                    <input type="text" id="name" name="name" class="form-input" placeholder="Enter product name" required value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="price" class="form-label">
                        <i class="fas fa-rupee-sign"></i> Price
                    </label>
                    <input type="number" id="price" name="price" class="form-input" placeholder="Enter price" step="0.01" required value="<?php echo isset($price) ? htmlspecialchars($price) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="category" class="form-label">
                        <i class="fas fa-tags"></i> Category
                    </label>
                    <select id="category" name="category" class="form-select" required>
                        <option value="">Select Category</option>
                        <option value="Tablets" <?php echo (isset($category) && $category == 'Tablets') ? 'selected' : ''; ?>>Tablets</option>
                        <option value="Syrups" <?php echo (isset($category) && $category == 'Syrups') ? 'selected' : ''; ?>>Syrups</option>
                        <option value="Supplements" <?php echo (isset($category) && $category == 'Supplements') ? 'selected' : ''; ?>>Supplements</option>
                        <option value="Creams" <?php echo (isset($category) && $category == 'Creams') ? 'selected' : ''; ?>>Creams</option>
                        <option value="Equipments" <?php echo (isset($category) && $category == 'Equipments') ? 'selected' : ''; ?>>Equipments</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="stock" class="form-label">
                        <i class="fas fa-boxes"></i> Stock Quantity
                    </label>
                    <input type="number" id="stock" name="stock" class="form-input" placeholder="Enter stock quantity" required value="<?php echo isset($stock) ? htmlspecialchars($stock) : ''; ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">
                    <i class="fas fa-align-left"></i> Description
                </label>
                <textarea id="description" name="description" class="form-textarea" placeholder="Enter product description (optional)"><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
            </div>

            <div class="form-group">
                <label for="image" class="form-label">
                    <i class="fas fa-image"></i> Product Image
                </label>
                <input type="file" id="image" name="image" class="form-input" accept="image/*" required>
                <small style="color: var(--light-text); margin-top: 0.5rem; display: block;">
                    <i class="fas fa-info-circle"></i> Supported formats: JPG, JPEG, PNG, GIF
                </small>
            </div>

            <div style="text-align: center; margin-top: 2rem;">
                <button type="submit" class="btn btn-success" style="padding: 1rem 2rem; font-size: 1.1rem;">
                    <i class="fas fa-plus-circle"></i> Add Product
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>