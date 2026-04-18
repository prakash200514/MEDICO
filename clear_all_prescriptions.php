<?php
include 'db.php';

echo "<h2>Clearing All Prescription Files and Records</h2>";

// Get all prescription files from database
$result = $conn->query("SELECT file_path FROM prescriptions");
$deleted_files = 0;
$deleted_records = 0;

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $file_path = $row['file_path'];
        if (file_exists($file_path)) {
            if (unlink($file_path)) {
                $deleted_files++;
                echo "<p>✅ Deleted file: " . basename($file_path) . "</p>";
            } else {
                echo "<p>❌ Failed to delete file: " . basename($file_path) . "</p>";
            }
        } else {
            echo "<p>⚠️ File not found: " . basename($file_path) . "</p>";
        }
    }
}

// Delete all prescription records from database
$delete_result = $conn->query("DELETE FROM prescriptions");
if ($delete_result) {
    $deleted_records = $conn->affected_rows;
    echo "<p>✅ Deleted $deleted_records prescription records from database</p>";
} else {
    echo "<p>❌ Error deleting prescription records: " . $conn->error . "</p>";
}

echo "<h3>Summary:</h3>";
echo "<p>📁 Deleted $deleted_files prescription files</p>";
echo "<p>🗄️ Deleted $deleted_records prescription records</p>";

echo "<h3>All prescriptions have been cleared!</h3>";
echo "<p>Now customers can only upload fresh prescriptions during checkout.</p>";
echo "<p><a href='checkout.php'>Go to Checkout</a></p>";
?>
