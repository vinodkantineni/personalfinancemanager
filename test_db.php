<?php
include 'db_connect.php';
echo "Connected successfully to database: " . $conn->host_info;
$conn->close();
?>