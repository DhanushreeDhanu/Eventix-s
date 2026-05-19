<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "eventix"; // FIXED: Adjusted to match your structural tables setup

$conn = mysqli_connect($host, $user, $password, $database);

// Check if the connection established successfully
if (!$conn) {
    die("Database Connection failed: " . mysqli_connect_error());
}

// Ensure proper character encoding for emojis, symbols, and special text inputs
mysqli_set_charset($conn, "utf8mb4");
?>