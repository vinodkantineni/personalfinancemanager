<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "User not logged in"]);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $upload_dir = "uploads/";

    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true); // Create directory if it doesn’t exist
    }

    $file_name = basename($_FILES["file"]["name"]);
    $file_tmp = $_FILES["file"]["tmp_name"];
    $file_path = $upload_dir . $file_name;

    if ($_FILES["file"]["size"] > 10 * 1024 * 1024) { // 10MB Limit
        echo json_encode(["error" => "File size exceeds limit"]);
        exit;
    }

    if (move_uploaded_file($file_tmp, $file_path)) {
        $stmt = $conn->prepare("INSERT INTO uploads (user_id, file_name, file_path) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("iss", $user_id, $file_name, $file_path);
            $stmt->execute();
            $stmt->close();
            echo json_encode(["success" => "File uploaded successfully"]);
        } else {
            echo json_encode(["error" => "Database error: " . $conn->error]);
        }
    } else {
        echo json_encode(["error" => "Error moving file to uploads folder"]);
    }

    exit;
}

// Fetch uploaded files
$stmt = $conn->prepare("SELECT id, file_name, file_path FROM uploads WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$files = [];
while ($row = $result->fetch_assoc()) {
    $files[] = $row;
}

$stmt->close();
echo json_encode($files);
?>
