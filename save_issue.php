<?php
include 'db_connect.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if request is a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = $_POST['fullName'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $issueType = $_POST['issueType'];
    $otherDescription = isset($_POST['otherDescription']) ? $_POST['otherDescription'] : null;

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO issue_reports (full_name, phone, email, issue_type, other_description) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $fullName, $phone, $email, $issueType, $otherDescription);

    if ($stmt->execute()) {
        echo "<script>
                alert('Issue reported successfully!');
                window.location.href = 'contactus.html';
              </script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
