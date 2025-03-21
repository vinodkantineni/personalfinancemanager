<?php
include 'db_connect.php'; // ✅ Ensure this file is included properly

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST["fullname"]);
    $phone = trim($_POST["phone"]);
    $email = trim($_POST["email"]);
    $userid = trim($_POST["userid"]);
    $password = trim($_POST["password"]);

    if (empty($fullname) || empty($phone) || empty($email) || empty($userid) || empty($password)) {
        die("Error: All fields are required!");
    }

    // Hash password before saving
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Check if the email or user ID already exists
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR userid = ?");
    $check_stmt->bind_param("ss", $email, $userid);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        die("Error: Email or User ID already exists!");
    }

    $check_stmt->close();

    // Insert user data
    $stmt = $conn->prepare("INSERT INTO users (fullname, phone, email, userid, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $fullname, $phone, $email, $userid, $hashed_password);

    if ($stmt->execute()) {
        echo "Signup successful!";
        header("Location: login.html"); // ✅ Redirect after signup
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
