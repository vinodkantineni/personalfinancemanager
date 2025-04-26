<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ptf";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to avoid encoding issues
$conn->set_charset("utf8mb4");

// Handle "MySQL server has gone away" by reconnecting
function reconnect($conn) {
    if ($conn->ping() === false) {
        $conn->close();
        $conn = new mysqli("localhost", "root", "", "ptf");
        if ($conn->connect_error) {
            die("Reconnection failed: " . $conn->connect_error);
        }
        $conn->set_charset("utf8mb4");
    }
    return $conn;
}
?>