<?php
session_start();
include 'db.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $subcategory = $_POST['subcategory'];

    // Handle image upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg','jpeg','png','gif'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $safe = preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['image']['name']);
            $image = time() . '_' . $safe;
            $dest = 'img/' . $image;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                $message = 'Error uploading image.';
            }
        } else {
            $message = 'Only JPG, JPEG, PNG & GIF files are allowed.';
        }
    }

    if (empty($message)) {
        $sql = "INSERT INTO products (name, description, price, stock, category, subcategory, image) VALUES (?, ?, ?, ?, 'Baby Products', ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssdiss', $name, $description, $price, $stock, $subcategory, $image);
        if ($stmt->execute()) {
            $message = 'Baby product added successfully!';
            $name = $description = $price = $stock = $subcategory = '';
        } else {
            $message = 'Error adding product: ' . $conn->error;
        }
    }
}

$page_title = 'Add Baby Product - Admin';
include 'header.php';
?>

<div class="auth-page">
    <div class="container">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <h1><i class="fas fa-baby"></i> Add Baby Product</h1>
                    <p>Add new baby products to the store</p>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="alert <?php echo strpos($message, 'successfully') !== false ? 'alert-success' : 'alert-danger'; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="auth-form">
                    <div class="form-group">
                        <label for="name" class="form-label">Product Name</label>
                        <input type="text" id="name" name="name" class="form-input" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-input" rows="4" required><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="price" class="form-label">Price (₹)</label>
                        <input type="number" id="price" name="price" class="form-input" step="0.01" value="<?php echo isset($price) ? htmlspecialchars($price) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="stock" class="form-label">Stock Quantity</label>
                        <input type="number" id="stock" name="stock" class="form-input" value="<?php echo isset($stock) ? htmlspecialchars($stock) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="subcategory" class="form-label">Baby Category</label>
                        <select id="subcategory" name="subcategory" class="form-input" required>
                            <option value="">Select Category</option>
                            <option value="Baby Food" <?php echo (isset($subcategory) && $subcategory == 'Baby Food') ? 'selected' : ''; ?>>Baby Food</option>
                            <option value="Baby Care" <?php echo (isset($subcategory) && $subcategory == 'Baby Care') ? 'selected' : ''; ?>>Baby Care</option>
                            <option value="Baby Diapers" <?php echo (isset($subcategory) && $subcategory == 'Baby Diapers') ? 'selected' : ''; ?>>Baby Diapers</option>
                            <option value="Baby Toys" <?php echo (isset($subcategory) && $subcategory == 'Baby Toys') ? 'selected' : ''; ?>>Baby Toys</option>
                            <option value="Baby Clothing" <?php echo (isset($subcategory) && $subcategory == 'Baby Clothing') ? 'selected' : ''; ?>>Baby Clothing</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="image" class="form-label">Product Image</label>
                        <input type="file" id="image" name="image" class="form-input" accept="image/*" required>
                        <small class="form-text">Upload JPG, JPEG, PNG or GIF file (max 5MB)</small>
                    </div>

                    <button type="submit" class="btn auth-btn">
                        <i class="fas fa-plus"></i> Add Baby Product
                    </button>
                </form>

                <div class="auth-footer">
                    <a href="admin_dashboard.php" class="back-home">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.auth-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem 0; }
.auth-container { width: 100%; max-width: 600px; }
.auth-card { background: rgba(255, 255, 255, 0.95); border-radius: 20px; padding: 2.5rem; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); backdrop-filter: blur(15px); }
.auth-header { text-align: center; margin-bottom: 2rem; }
.auth-header h1 { font-size: 2rem; color: #333; margin-bottom: 0.5rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem; }
.auth-header p { color: #666; font-size: 1rem; }
.auth-form { display: flex; flex-direction: column; gap: 1.5rem; }
.form-group { display: flex; flex-direction: column; gap: 0.5rem; }
.form-label { font-weight: 600; color: #333; font-size: 0.95rem; }
.form-input { padding: 0.8rem 1rem; border: 2px solid #e1e5e9; border-radius: 10px; font-size: 1rem; transition: all 0.3s ease; background: white; }
.form-input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
.form-input[type="file"] { padding: 0.6rem; border: 2px dashed #e1e5e9; background: #f8f9fa; }
.form-input[type="file"]:focus { border-color: #667eea; background: white; }
.form-text { font-size: 0.85rem; color: #666; margin-top: 0.25rem; }
.auth-btn { background: linear-gradient(135deg, #ffb6c1, #ff8fa3); color: white; padding: 1rem 2rem; border: none; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 0.5rem; }
.auth-btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(255, 182, 193, 0.3); }
.auth-footer { text-align: center; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #eee; }
.back-home { color: #667eea; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s ease; }
.back-home:hover { color: #764ba2; transform: translateX(-5px); }
.alert { padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; font-weight: 500; }
.alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
.alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
@media (max-width: 768px) { .auth-card { padding: 2rem; margin: 1rem; } .auth-header h1 { font-size: 1.8rem; } }
</style>

<?php include 'footer.php'; ?>


