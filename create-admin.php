<?php
include('config/db.php');

$name = "Main Admin";
$email = "admin@eventix.com";
$phone = "9999999999";
$password = password_hash("admin123", PASSWORD_DEFAULT);
$role = "admin";
$status = "active";

// Delete old admin
$conn->query("DELETE FROM users WHERE email='admin@eventix.com'");

// Insert admin
$sql = "INSERT INTO users 
(name, email, phone, password, role, status)
VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "ssssss",
    $name,
    $email,
    $phone,
    $password,
    $role,
    $status
);

if ($stmt->execute()) {

    echo "<h2>Admin Created Successfully</h2>";

    echo "Email: admin@eventix.com <br>";
    echo "Password: admin123";

} else {

    echo "Error: " . $stmt->error;
}
?>