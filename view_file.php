<?php
session_start();
include 'db_connect.php';

if (!isset($_GET['file'])) {
    die("File not specified.");
}

$file_path = $_GET['file'];

// Validate if the file belongs to the logged-in user
$stmt = $conn->prepare("SELECT file_name FROM uploads WHERE file_path = ? AND user_id = ?");
$stmt->bind_param("si", $file_path, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Unauthorized access.");
}

$row = $result->fetch_assoc();
$file_name = $row['file_name'];

$extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
$allowed_previews = ["jpg", "jpeg", "png", "gif", "pdf", "txt", "html"];

if (in_array($extension, $allowed_previews)) {
    header("Content-Disposition: inline; filename=" . $file_name);
    header("Content-Type: " . mime_content_type($file_path));
    readfile($file_path);
    exit;
} else {
    echo "<h3>Preview not supported for this file type.</h3>";
    echo "<p><a href='$file_path' target='_blank'>Click here to download</a></p>";
}
?>
