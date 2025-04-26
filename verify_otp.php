<?php
session_start();
include 'db_connect.php'; // ✅ Connect to your MySQL database

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $otp = trim($_POST["otp"]);

    if (!empty($email) && !empty($otp)) {
        // ✅ Check if email in POST matches email stored in session
        if (isset($_SESSION['email']) && $email === $_SESSION['email']) {
            // ✅ Prepare query to get user details including OTP and expiry
            $stmt = $conn->prepare("SELECT id, otp_code, otp_expiry FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $current_time = date("Y-m-d H:i:s");

                // ✅ Check if OTP is valid and not expired
                if ($user['otp_expiry'] >= $current_time && $user['otp_code'] == $otp) {
                    // ✅ OTP is correct - complete login
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['email'] = $email; // ✅ Set email in session for later pages

                    // ✅ Clear OTP fields from database after verification
                    $clear_otp = $conn->prepare("UPDATE users SET otp_code = NULL, otp_expiry = NULL WHERE email = ?");
                    $clear_otp->bind_param("s", $email);
                    $clear_otp->execute();
                    $clear_otp->close();

                    // ✅ Redirect to upload.php
                    header("Location: upload.php");
                    exit();
                } else {
                    $error = "Invalid or expired OTP.";
                }
            } else {
                $error = "Email not found in database.";
            }
            $stmt->close();
        } else {
            $error = "Invalid email or session expired. Please login again.";
        }
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
    <title>Verify OTP | Personal Finance Manager</title>
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
                    <li><a href="login.php" class="btn-login">Login</a></li>
                    <li><a href="signup.html" class="btn-login">Signup</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="form-container">
        <h2>Verify OTP</h2>

        <!-- ✅ OTP Verification Form -->
        <form method="POST" action="">
            <input type="email" name="email" placeholder="Email" value="<?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?>" required>
            <input type="text" name="otp" placeholder="Enter OTP" required>
            <button type="submit">Verify OTP</button>
        </form>

        <!-- ✅ Display Messages -->
        <?php
        if (!empty($error)) {
            echo "<p class='error'>$error</p>";
        }
        if (!empty($success)) {
            echo "<p class='success'>$success</p>";
        }
        ?>
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
</body>
</html>
