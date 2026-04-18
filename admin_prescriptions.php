<?php
session_start();
include 'db.php';

// Only admins
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
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
    header('Location: admin_prescriptions.php');
    exit();
}

// Prescriptions search & pagination
$p_q = isset($_GET['p_q']) ? trim($_GET['p_q']) : '';
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

$page_title = "Admin - Prescriptions";
include 'header.php';
?>

<div class="admin-container">
    <div class="admin-header card">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h1><i class="fas fa-file-medical"></i> Prescriptions</h1>
                <p>Manage uploaded prescriptions</p>
            </div>
            <div>
                <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>
    </div>

    <div style="margin:1rem 0;">
        <form method="GET" style="display:flex; gap:.5rem; flex-wrap:wrap; align-items:center;">
            <input type="text" name="p_q" placeholder="Search by user email or filename" value="<?php echo htmlspecialchars($p_q); ?>" style="padding:.5rem .75rem; border-radius:6px; border:1px solid #ddd; width:300px;">
            <input type="date" name="p_from" value="<?php echo htmlspecialchars($p_from); ?>" style="padding:.5rem .75rem; border-radius:6px; border:1px solid #ddd;">
            <input type="date" name="p_to" value="<?php echo htmlspecialchars($p_to); ?>" style="padding:.5rem .75rem; border-radius:6px; border:1px solid #ddd;">
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="admin_prescriptions.php" class="btn btn-secondary">Reset</a>
        </form>
    </div>

    <?php if (empty($prescriptions)): ?>
        <p>No prescriptions found.</p>
    <?php else: ?>
        <div class="prescriptions-list" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap:1rem; margin-top:1rem;">
            <?php foreach ($prescriptions as $p): ?>
                <?php $fname = basename($p['file_path']); $ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION)); $isImage = in_array($ext, ['jpg','jpeg','png']); ?>
                <div class="prescription-card card" style="padding:1rem;">
                    <div style="display:flex; gap:.5rem; align-items:center;">
                        <?php if ($isImage): ?>
                                    <?php $thumb = dirname($p['file_path']) . '/thumb_' . basename($p['file_path']); ?>
                                    <?php if (file_exists($thumb)): ?>
                                        <img src="<?php echo $thumb; ?>" alt="<?php echo htmlspecialchars($fname); ?>" style="width:60px; height:60px; object-fit:cover; border-radius:6px; border:1px solid #eee;">
                                    <?php else: ?>
                                        <img src="<?php echo $p['file_path']; ?>" alt="<?php echo htmlspecialchars($fname); ?>" style="width:60px; height:60px; object-fit:cover; border-radius:6px; border:1px solid #eee;">
                                    <?php endif; ?>
                                <?php else: ?>
                            <i class="fas fa-file-medical" style="font-size:1.6rem; color:#667eea;"></i>
                        <?php endif; ?>
                        <div style="flex:1; margin-left:.5rem;">
                            <div style="font-weight:700;"><?php echo htmlspecialchars($fname); ?></div>
                            <div style="color:#666; font-size:0.9rem;">Uploaded: <?php echo date('M d, Y H:i', strtotime($p['uploaded_at'])); ?></div>
                            <div style="color:#666; font-size:0.9rem;">By: <?php echo htmlspecialchars($p['user_email']); ?></div>
                        </div>
                    </div>
                    <div style="margin-top:.75rem; display:flex; gap:.5rem;">
                        <?php if ($isImage): ?>
                            <?php $thumb = dirname($p['file_path']) . '/thumb_' . basename($p['file_path']); ?>
                            <a href="<?php echo $p['file_path']; ?>" target="_blank" class="btn btn-secondary">Preview</a>
                        <?php else: ?>
                            <a href="download_prescription.php?id=<?php echo $p['id']; ?>" class="btn btn-secondary">Download</a>
                        <?php endif; ?>
                        <a href="admin_prescriptions.php?delete_prescription=<?php echo $p['id']; ?>" class="btn btn-danger" onclick="return confirm('Delete this prescription?')">Delete</a>
                        <a href="checkout.php?prescription_id=<?php echo $p['id']; ?>&from_cart=true" class="btn btn-primary">Order with this</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="margin-top:1rem; display:flex; gap:.5rem; align-items:center; justify-content:center;">
            <?php if ($page > 1): ?>
                <a href="admin_prescriptions.php?p_q=<?php echo urlencode($p_q); ?>&p_from=<?php echo urlencode($p_from); ?>&p_to=<?php echo urlencode($p_to); ?>&p_page=<?php echo $page-1; ?>" class="btn btn-secondary">&laquo; Prev</a>
            <?php endif; ?>
            <span style="padding:.5rem .75rem;">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
            <?php if ($page < $total_pages): ?>
                <a href="admin_prescriptions.php?p_q=<?php echo urlencode($p_q); ?>&p_from=<?php echo urlencode($p_from); ?>&p_to=<?php echo urlencode($p_to); ?>&p_page=<?php echo $page+1; ?>" class="btn btn-secondary">Next &raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>

<?php include 'footer.php'; ?>
