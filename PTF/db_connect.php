<?php
$servername = "localhost"; // Change this if your database is hosted elsewhere
$username = "root"; // Default XAMPP username is "root"
$password = ""; // Default XAMPP password is empty
$dbname = "pft"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
