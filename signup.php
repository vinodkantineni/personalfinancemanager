<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize form inputs (matching the form's field names)
    $fullname = trim($_POST["fullname"]);
    $phone = trim($_POST["phone"]);
    $email = trim($_POST["email"]);
    $user_id = trim($_POST["userid"]); // Changed from user_id to userid to match the form
    $password = trim($_POST["password"]);

    // Validate all fields are filled
    if (empty($fullname) || empty($phone) || empty($email) || empty($user_id) || empty($password)) {
        die("Error: All fields are required!");
    }

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Check if email or user_id already exists
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR user_id = ?");
    $check_stmt->bind_param("ss", $email, $user_id);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        die("Error: Email or User ID already exists!");
    }
    $check_stmt->close();

    // Insert new user into the users table
    $stmt = $conn->prepare("INSERT INTO users (fullname, phone, email, user_id, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $fullname, $phone, $email, $user_id, $hashed_password);

    if ($stmt->execute()) {
        // Set session message and redirect to login page
        $_SESSION['signup_success'] = "Signup successful! Please login.";
        header("Location: login.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Close database connection
$conn->close();
?>