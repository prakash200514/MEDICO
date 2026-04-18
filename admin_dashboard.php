<?php
session_start();
include 'db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

// Handle product deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM products WHERE id = $id");
    header('Location: admin_dashboard.php');
    exit();
}

// Handle prescription deletion
if (isset($_GET['delete_prescription'])) {
    $pid = (int)$_GET['delete_prescription'];
    $presRes = $conn->query("SELECT file_path FROM prescriptions WHERE id = $pid");
    if ($presRes && $presRes->num_rows > 0) {
        $prow = $presRes->fetch_assoc();
        $pfile = $prow['file_path'];
        if ($pfile && file_exists($pfile)) {
            @unlink($pfile);
        }
    }
    $conn->query("DELETE FROM prescriptions WHERE id = $pid");
    header('Location: admin_dashboard.php');
    exit();
}

// Build filters for admin product search
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$filter_cat = isset($_GET['category']) ? trim($_GET['category']) : '';
$filter_subcat = isset($_GET['subcategory']) ? trim($_GET['subcategory']) : '';

// Fetch distinct categories and subcategories for filter selects
$categories = [];
$catRes = $conn->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != '' ORDER BY category ASC");
if ($catRes) {
    while ($c = $catRes->fetch_assoc()) {
        $categories[] = $c['category'];
    }
}

$subcategories = [];
$subRes = $conn->query("SELECT DISTINCT subcategory FROM products WHERE subcategory IS NOT NULL AND subcategory != '' ORDER BY subcategory ASC");
if ($subRes) {
    while ($s = $subRes->fetch_assoc()) {
        $subcategories[] = $s['subcategory'];
    }
}

// Build SQL with simple escaping
$conditions = [];
if ($q !== '') {
    $q_esc = $conn->real_escape_string($q);
    $conditions[] = "(name LIKE '%$q_esc%' OR description LIKE '%$q_esc%')";
}
if ($filter_cat !== '') {
    $cat_esc = $conn->real_escape_string($filter_cat);
    $conditions[] = "category = '$cat_esc'";
}
if ($filter_subcat !== '') {
    $sub_esc = $conn->real_escape_string($filter_subcat);
    $conditions[] = "subcategory = '$sub_esc'";
}

$sql = "SELECT * FROM products";
if (count($conditions) > 0) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}
$sql .= " ORDER BY id DESC";
$result = $conn->query($sql);

// Prescriptions: support search (email, filename), date filter, and pagination
$p_q = isset($_GET['p_q']) ? trim($_GET['p_q']) : ''; // search term for email or filename
$p_from = isset($_GET['p_from']) ? trim($_GET['p_from']) : '';
$p_to = isset($_GET['p_to']) ? trim($_GET['p_to']) : '';
$page = isset($_GET['p_page']) ? max(1, (int)$_GET['p_page']) : 1;
$perPage = 12;

$pres_conditions = [];
if ($p_q !== '') {
    $pq = $conn->real_escape_string($p_q);
    $pres_conditions[] = "(user_email LIKE '%$pq%' OR file_path LIKE '%$pq%')";
}
if ($p_from !== '') {
    $from_esc = $conn->real_escape_string($p_from);
    $pres_conditions[] = "DATE(uploaded_at) >= '$from_esc'";
}
if ($p_to !== '') {
    $to_esc = $conn->real_escape_string($p_to);
    $pres_conditions[] = "DATE(uploaded_at) <= '$to_esc'";
}

$pres_sql = "SELECT COUNT(*) as cnt FROM prescriptions";
if (count($pres_conditions) > 0) $pres_sql .= " WHERE " . implode(' AND ', $pres_conditions);
$pres_count_res = $conn->query($pres_sql);
$total_pres = ($pres_count_res) ? (int)$pres_count_res->fetch_assoc()['cnt'] : 0;

$offset = ($page - 1) * $perPage;

$prescriptions = [];
$pr_sql = "SELECT * FROM prescriptions";
if (count($pres_conditions) > 0) $pr_sql .= " WHERE " . implode(' AND ', $pres_conditions);
$pr_sql .= " ORDER BY uploaded_at DESC LIMIT $perPage OFFSET $offset";
$pr = $conn->query($pr_sql);
if ($pr) {
    while ($r = $pr->fetch_assoc()) {
        $prescriptions[] = $r;
    }
}

$total_pages = max(1, (int)ceil($total_pres / $perPage));

$page_title = "Admin Dashboard - Medico";
include 'header.php';
?>

<div class="admin-container">
        <div class="admin-header card">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div class="prakash">
                    <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
                    <p>Manage your medicine store inventory and orders</p>
                </div>
                <a href="admin_logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>

        <div class="admin-nav">
            <a href="admin_dashboard.php" class="btn btn-primary">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="admin_add_product.php" class="btn btn-success">
                <i class="fas fa-plus"></i> Add Product
            </a>
            <a href="admin_add_veterinary.php" class="btn btn-info">
                <i class="fas fa-paw"></i> Add Veterinary
            </a>
            <a href="admin_add_injection.php" class="btn btn-warning">
                <i class="fas fa-syringe"></i> Add Injection
            </a>
            <a href="admin_add_baby.php" class="btn btn-success">
                <i class="fas fa-baby"></i> Add Baby Product
            </a>
            <a href="admin_prescriptions.php" class="btn btn-dark">
                <i class="fas fa-file-medical"></i> Prescriptions
            </a>
            <a href="products.php" class="btn btn-secondary">
                <i class="fas fa-store"></i> View Store
            </a>
        </div>

        <?php
        // Get statistics
        $total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
        $total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
        $total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
        ?>

        <div class="stats grid grid-3">
            <div class="stat-card card">
                <div class="stat-icon">
                    <i class="fas fa-pills"></i>
                </div>
                <div class="stat-number"><?php echo $total_products; ?></div>
                <div class="stat-label">Total Products</div>
            </div>
            <div class="stat-card card">
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-number"><?php echo $total_orders; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-card card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number"><?php echo $total_users; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
        </div>

        <div class="products-section">
            <h2><i class="fas fa-boxes"></i> Manage Products</h2>

            <div class="admin-filters" style="margin: 1rem 0 2rem 0; display:flex; gap:1rem; flex-wrap:wrap; align-items:center;">
                <form method="GET" style="display:flex; gap:.5rem; align-items:center;">
                    <input type="text" name="q" placeholder="Search by name or description" value="<?php echo htmlspecialchars($q); ?>" style="padding:.6rem 1rem; border-radius:8px; border:1px solid #ddd; width:320px;">
                    <select name="category" style="padding:.6rem 1rem; border-radius:8px; border:1px solid #ddd;">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo ($filter_cat === $cat) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="subcategory" style="padding:.6rem 1rem; border-radius:8px; border:1px solid #ddd;">
                        <option value="">All Subcategories</option>
                        <?php foreach ($subcategories as $sub): ?>
                            <option value="<?php echo htmlspecialchars($sub); ?>" <?php echo ($filter_subcat === $sub) ? 'selected' : ''; ?>><?php echo htmlspecialchars($sub); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="admin_dashboard.php" class="btn btn-secondary">Reset</a>
                </form>
            </div>

            <div class="products-grid grid grid-4">
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="product-card card animate-fade-in-up">
                        <div class="product-image-container">
                            <img src="img/<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>" class="product-image">
                        </div>
                        <div class="product-info">
                            <h3 class="product-title"><?php echo $row['name']; ?></h3>
                            <div class="product-details">
                                <p><strong>Price:</strong> ₹<?php echo $row['price']; ?></p>
                                <p><strong>Category:</strong> <?php echo $row['category']; ?></p>
                                <?php if (!empty($row['subcategory'])): ?>
                                    <p><strong>Subcategory:</strong> <?php echo $row['subcategory']; ?></p>
                                <?php endif; ?>
                                <p><strong>Stock:</strong> <?php echo $row['stock']; ?></p>
                            </div>
                            <div class="product-actions">
                                <a href="admin_edit_product.php?id=<?php echo $row['id']; ?>" class="btn btn-success">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="admin_dashboard.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger" 
                                   onclick="return confirm('Are you sure you want to delete this product?')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Prescriptions moved to admin_prescriptions.php -->
</div>

<style>
/* Override purple colors with blue for admin dashboard page */
.admin-container .admin-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}
.prakash h1{
    color: white;
}
.admin-container .btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

.admin-container .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3) !important;
}

.admin-container .btn-info {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%) !important;
}

.admin-container .btn-info:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(52, 152, 219, 0.3) !important;
}

.admin-container .form-label i {
    color: #667eea !important;
}

.admin-container .form-input:focus,
.admin-container .form-select:focus,
.admin-container .form-textarea:focus {
    border-color: #667eea !important;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
}

/* Blue background for the admin dashboard page */
body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%) !important;
}

/* Blue stat card icons */
.admin-container .stat-icon i {
    color: #667eea !important;
}
</style>

<?php include 'footer.php'; ?> 