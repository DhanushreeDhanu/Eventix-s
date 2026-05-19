<?php
include('config/db.php');

$name = "Main Admin";
$email = "admin@eventix.com";
$phone = "9999999999";
$password = password_hash("admin123", PASSWORD_DEFAULT);
$role = "admin";
$status = "active";

$conn->query("DELETE FROM users WHERE email='admin@eventix.com'");

$stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, role, organizer_status) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $name, $email, $phone, $password, $role, $status);

if ($stmt->execute()) {
    echo "Admin created successfully.<br>";
    echo "Email: admin@eventix.com<br>";
    echo "Password: admin123<br>";
} else {
    echo "Error: " . $conn->error;
}
?>