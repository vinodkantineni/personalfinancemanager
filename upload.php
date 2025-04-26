<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    header('Content-Type: application/json');
    $upload_dir = "Uploads/";

    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_name = basename($_FILES["file"]["name"]);
    $file_tmp = $_FILES["file"]["tmp_name"];
    $file_path = $upload_dir . $file_name;

    if ($_FILES["file"]["size"] > 10 * 1024 * 1024) {
        echo json_encode(["error" => "File size exceeds 10MB limit"]);
        exit;
    }

    if (move_uploaded_file($file_tmp, $file_path)) {
        $conn = reconnect($conn);
        $stmt = $conn->prepare("INSERT INTO uploads (user_id, file_name, file_path) VALUES (?, ?, ?)");
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("iss", $user_id, $file_name, $file_path);
        $stmt->execute();
        $stmt->close();
        echo json_encode(["success" => "File uploaded successfully"]);
    } else {
        echo json_encode(["error" => "Error moving file to uploads folder"]);
    }
    exit;
}

// Fetch uploaded files
$conn = reconnect($conn);
$stmt = $conn->prepare("SELECT id, file_name, file_path FROM uploads WHERE user_id = ?");
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$files = [];
while ($row = $result->fetch_assoc()) {
    $files[] = $row;
}
$stmt->close();

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode($files);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Manager</title>
    <link rel="stylesheet" href="index.css">
</head>  
<body>
    <header>
        <div class="container">
            <h1 class="logo">Personal Finance Manager</h1>
            <nav>
                <ul class="nav-links">
                    <li><a href="contactus.html" class="btn-login">Contact</a></li>
                    <li><a href="profile.html" class="btn-login">Profile</a></li>
                    <li><a href="logout.php" class="btn-login">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main>
        <div class="contact-container">
            <h5>Upload Your File</h5>
            <form id="upload-form" enctype="multipart/form-data">
               <input type="file" id="file-input" name="file" required>
               <button type="submit" class="btn-signup">Upload</button>
             </form>
        </div>
        <p id="message"></p>
        <h2>Your Uploaded Files</h2>
        <ul id="file-list"></ul>
        <div id="chart-container"></div>
        <button class="btn-login">Cash</button>
        <button class="btn-login">Any Card</button>
    </main>
    <footer>
        <div>
            <p>Follow Us</p>
            <div class="social-links">
                <a href="https://instagram.com" target="_blank">📸 Instagram</a>
                <a href="https://facebook.com" target="_blank">📘 Facebook</a>
                <a href="https://linkedin.com" target="_blank">🔗 LinkedIn</a>
                <a href="https://youtube.com" target="_blank">▶ YouTube</a>
            </div>
            <p>© 2025 Personal Finance Manager. All rights reserved.</p>
        </div>
    </footer>
    <script src="upload.js"></script>
</body>
</html>