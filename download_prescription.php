<?php
// Secure download handler for prescriptions
session_start();
include 'db.php';

// Only admins should download via this endpoint; if you want admins only, check admin session
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Allow admin or authenticated users? For now restrict to admin
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo 'Bad Request';
    exit;
}

$id = (int)$_GET['id'];
$res = $conn->query("SELECT file_path FROM prescriptions WHERE id = $id LIMIT 1");
if (!$res || $res->num_rows === 0) {
    http_response_code(404);
    echo 'Not found';
    exit;
}
$row = $res->fetch_assoc();
$path = $row['file_path'];

if (!$path || !file_exists($path)) {
    http_response_code(404);
    echo 'File not found';
    exit;
}

$basename = basename($path);
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $path) ?: 'application/octet-stream';
finfo_close($finfo);

// Send headers and file
header('Content-Description: File Transfer');
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . $basename . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;
?>