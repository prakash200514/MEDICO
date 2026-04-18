<?php
session_start();
include "db.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: signup.php?redirect=checkout');
    exit;
}

// Note: previously this page redirected to checkout. Keeping the upload form here
// allows users to manage their prescription uploads directly. If you prefer the
// redirect behavior, revert these lines.

$error = '';
$success = '';
$uploaded_files = [];

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_email = $_SESSION['user_email'];
    
    // Create uploads directory if it doesn't exist
    $upload_dir = "uploads/prescriptions/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Check if files were uploaded
    if (isset($_FILES['prescription']) && $_FILES['prescription']['error'] == 0) {
        $file = $_FILES['prescription'];
        $filename = $file['name'];
        $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $filesize = $file['size'];
        
        // Validate file type
        $allowed_types = ['jpg', 'jpeg', 'png', 'pdf'];
        if (!in_array($filetype, $allowed_types)) {
            $error = "Only JPG, PNG, and PDF files are allowed.";
        }
        // Validate file size (max 5MB)
        elseif ($filesize > 5 * 1024 * 1024) {
            $error = "File size must be less than 5MB.";
        }
        else {
            // Generate unique filename
            $unique_filename = time() . '_' . $user_email . '_' . $filename;
            $filepath = $upload_dir . $unique_filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Save to database
                $conn->query("INSERT INTO prescriptions (user_email, file_path) VALUES ('$user_email', '$filepath')");
                $success = "Prescription uploaded successfully!";
                
                // Get uploaded files for this user
                $result = $conn->query("SELECT * FROM prescriptions WHERE user_email = '$user_email' ORDER BY uploaded_at DESC");
                while ($row = $result->fetch_assoc()) {
                    $uploaded_files[] = $row;
                }
            } else {
                $error = "Failed to upload file. Please try again.";
            }
        }
    } else {
        $error = "Please select a file to upload.";
    }
}

// Get existing uploaded files
if (isset($_SESSION['user_email'])) {
    $user_email = $_SESSION['user_email'];
    $result = $conn->query("SELECT * FROM prescriptions WHERE user_email = '$user_email' ORDER BY uploaded_at DESC");
    while ($row = $result->fetch_assoc()) {
        $uploaded_files[] = $row;
    }
}

$page_title = "Upload Prescription - Medico";
include 'header.php';
?>

<div class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-file-medical"></i>
                <h2>Upload Prescription</h2>
                <p>Upload your prescription to order medicines</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data" class="auth-form">
                <div class="form-group">
                    <label for="prescription">
                        <i class="fas fa-upload"></i> Select Prescription File
                    </label>
                    <input type="file" id="prescription" name="prescription" 
                           accept=".jpg,.jpeg,.png,.pdf" required>
                    <small style="color: #666; font-size: 0.8rem;">
                        Accepted formats: JPG, PNG, PDF (Max size: 5MB)
                    </small>
                </div>

                <button type="submit" class="auth-btn">
                    <i class="fas fa-upload"></i> Upload Prescription
                </button>
            </form>

            <?php if (!empty($uploaded_files)): ?>
                <div style="margin-top: 2rem;">
                    <h3 style="margin-bottom: 1rem; color: #333;">Your Uploaded Prescriptions</h3>
                    <div class="prescription-list">
                        <?php foreach ($uploaded_files as $file): ?>
                            <?php $fname = basename($file['file_path']); $ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION)); $isImage = in_array($ext, ['jpg','jpeg','png']); ?>
                            <div class="prescription-item">
                                <div class="file-info">
                                    <?php if ($isImage): ?>
                                        <?php $thumb = dirname($file['file_path']) . '/thumb_' . basename($file['file_path']); ?>
                                        <?php if (file_exists($thumb)): ?>
                                            <img src="<?php echo $thumb; ?>" alt="<?php echo htmlspecialchars($fname); ?>" style="width:40px; height:40px; object-fit:cover; border-radius:6px; border:1px solid #eee; margin-right:.5rem; vertical-align:middle;">
                                        <?php else: ?>
                                            <i class="fas fa-file-medical"></i>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <i class="fas fa-file-medical"></i>
                                    <?php endif; ?>
                                    <span><?php echo $fname; ?></span>
                                    <small><?php echo date('M d, Y H:i', strtotime($file['uploaded_at'])); ?></small>
                                </div>
                                <div class="file-actions">
                                    <a href="<?php echo $file['file_path']; ?>" target="_blank" class="btn-view">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="checkout.php?prescription_id=<?php echo $file['id']; ?>&from_cart=true" class="btn-order">
                                        <i class="fas fa-shopping-cart"></i> Order with this
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="auth-footer">
                <a href="products.php" class="back-home">
                    <i class="fas fa-arrow-left"></i> Back to Products
                </a>
                <a href="checkout.php?from_cart=true" class="back-home">
                    <i class="fas fa-shopping-cart"></i> Go to Checkout
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.auth-page {
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.auth-container {
    width: 100%;
    max-width: 600px;
    margin: 0 auto;
}

.auth-card {
    background: white;
    border-radius: 20px;
    padding: 2.5rem;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    text-align: center;
}

.auth-header {
    margin-bottom: 2rem;
}

.auth-header i {
    font-size: 3rem;
    color: #667eea;
    margin-bottom: 1rem;
}

.auth-header h2 {
    font-size: 2rem;
    color: #333;
    margin-bottom: 0.5rem;
    font-weight: 700;
}

.auth-header p {
    color: #666;
    font-size: 1rem;
}

.alert {
    padding: 1rem;
    border-radius: 10px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
}

.alert-error {
    background: #fee;
    color: #c53030;
    border: 1px solid #fed7d7;
}

.alert-success {
    background: #f0fff4;
    color: #2f855a;
    border: 1px solid #c6f6d5;
}

.auth-form {
    text-align: left;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #333;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-group label i {
    color: #667eea;
}

.form-group input[type="file"] {
    width: 100%;
    padding: 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: #f8f9fa;
}

.form-group input[type="file"]:focus {
    outline: none;
    border-color: #667eea;
    background: white;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.auth-btn {
    width: 100%;
    padding: 1rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 1rem;
}

.auth-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
}

.prescription-list {
    margin-top: 1rem;
}

.prescription-item {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1rem;
    border: 1px solid #e2e8f0;
}

.file-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.file-info i {
    color: #667eea;
    font-size: 1.2rem;
}

.file-info span {
    font-weight: 600;
    color: #333;
}

.file-info small {
    color: #666;
    margin-left: auto;
}

.file-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.btn-view, .btn-order {
    padding: 0.5rem 1rem;
    border-radius: 5px;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}

.btn-view {
    background: #e3eafc;
    color: #667eea;
}

.btn-view:hover {
    background: #c3dafe;
}

.btn-order {
    background: #667eea;
    color: white;
}

.btn-order:hover {
    background: #5a6fd8;
}

.auth-footer {
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
}

.back-home {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #666 !important;
    font-size: 0.9rem;
    text-decoration: none;
}

.back-home:hover {
    color: #333 !important;
}

@media (max-width: 480px) {
    .auth-card {
        padding: 2rem;
        margin: 1rem;
    }
    
    .auth-header h2 {
        font-size: 1.5rem;
    }
    
    .file-actions {
        flex-direction: column;
    }
    
    .auth-footer {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<?php include 'footer.php'; ?>