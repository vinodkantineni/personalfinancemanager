<?php
session_start();
include 'db_connect.php';

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (!empty($email) && !empty($password)) {
        // Reconnect to ensure valid connection
        $conn = reconnect($conn);

        // Verify email and password
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Generate 6-digit OTP
                $otp = rand(100000, 999999); // Generates a number like 123456
                $expiry = date("Y-m-d H:i:s", strtotime('+5 minutes'));

                // Store OTP
                $conn = reconnect($conn); // Reconnect before update
                $update_otp = $conn->prepare("UPDATE users SET otp_code = ?, otp_expiry = ? WHERE email = ?");
                if ($update_otp === false) {
                    die("Prepare failed: " . $conn->error);
                }
                $update_otp->bind_param("sss", $otp, $expiry, $email);
                $update_otp->execute();
                $update_otp->close();

                // Send OTP via email
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'reddy200416@gmail.com';
                    $mail->Password = 'zely sslt bvpt xboq';
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;

                    $mail->setFrom('reddy200416@gmail.com', 'Personal Finance Manager');
                    $mail->addAddress($email);
                    $mail->Subject = 'Your OTP Code';
                    $mail->isHTML(true);
                    $mail->Body = "<h3>Your OTP is $otp</h3><p>It expires in 5 minutes.</p>";

                    $mail->send();
                    $_SESSION['email'] = $email;
                    $_SESSION['user_id'] = $user['id'];
                    $success = "OTP sent to your email. <a href='verify_otp.php'>Verify OTP</a>";
                } catch (Exception $e) {
                    $error = "Failed to send OTP. Mailer Error: {$mail->ErrorInfo}";
                }
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "Email not found.";
        }
        $stmt->close();
    } else {
        $error = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Personal Finance Manager</title>
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="signup.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Personal Finance Manager</h1>
            <nav>
                <ul class="nav-links">
                    <li><a href="upload.php" class="btn-login">Home</a></li>
                    <li><a href="signup.html" class="btn-login">Signup</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main class="form-container">
        <h2>Login</h2>
        <form method="POST" action="">
            <input type="email" name="email" placeholder="Email" required><br>
            <div class="password-container">
                <input type="password" name="password" id="password" placeholder="Password" required>
                <span class="toggle-password" style="cursor: pointer;">👁</span>
            </div>
            <button type="submit">Login</button>
        </form>
        <?php
        if (!empty($error)) {
            echo "<p class='error'>$error</p>";
        }
        if (!empty($success)) {
            echo "<p class='success'>$success</p>";
        }
        ?>
        <p>Don't have an account? <a href="signup.html">Signup</a></p>
    </main>
    <footer>
        <p>Follow Us:</p>
        <div class="social-links">
            <a href="https://instagram.com" target="_blank">📸 Instagram</a>
            <a href="https://facebook.com" target="_blank">📘 Facebook</a>
            <a href="https://linkedin.com" target="_blank">🔗 LinkedIn</a>
            <a href="https://youtube.com" target="_blank">▶ YouTube</a>
        </div>
        <p>© 2025 Personal Finance Manager. All rights reserved.</p>
    </footer>
    <script>
        document.querySelector(".toggle-password").addEventListener("click", function () {
            const passwordField = document.getElementById("password");
            passwordField.type = passwordField.type === "password" ? "text" : "password";
        });
    </script>
</body>
</html>