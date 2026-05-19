<?php
session_start();
include('../config/db.php');

// 1. Guard check for admin session
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// 2. Validate input organizer parameter
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage-organizers.php");
    exit();
}

$organizer_id = intval($_GET['id']);

// 3. Verify user exists and holds the organizer role
$check = $conn->prepare("SELECT id, name FROM users WHERE id=? AND role='organizer'");
$check->bind_param("i", $organizer_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows == 0) {
    echo "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <title>Organizer Not Found</title>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <style>body { background: #f4f6fb; font-family: sans-serif; }</style>
    </head>
    <body>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Organizer Not Found!',
                    text: 'Invalid organizer account or role mapping.',
                    confirmButtonColor: '#dc2626'
                }).then(() => {
                    window.location.href='manage-organizers.php';
                });
            });
        </script>
    </body>
    </html>";
    exit();
}

// 4. Fail-safe structural check for the status tracking column
$check_col = $conn->query("SHOW COLUMNS FROM users LIKE 'organizer_status'");
if (!$check_col || $check_col->num_rows == 0) {
    // If column doesn't exist, execute an emergency schema migration so the query won't crash
    $conn->query("ALTER TABLE users ADD COLUMN organizer_status ENUM('active','blocked') DEFAULT 'active'");
}

// 5. Execute the update query safely
$stmt = $conn->prepare("UPDATE users SET organizer_status='blocked' WHERE id=? AND role='organizer'");
$stmt->bind_param("i", $organizer_id);

if ($stmt->execute()) {
    echo "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <title>Organizer Blocked</title>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <style>body { background: #f4f6fb; font-family: sans-serif; }</style>
    </head>
    <body>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Organizer Blocked!',
                    text: 'Organizer account has been blocked successfully.',
                    confirmButtonColor: '#2563eb',
                    confirmButtonText: 'Back to Manage Organizers'
                }).then(() => {
                    window.location.href='manage-organizers.php';
                });
            });
        </script>
    </body>
    </html>";
} else {
    echo "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <title>Block Failed</title>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <style>body { background: #f4f6fb; font-family: sans-serif; }</style>
    </head>
    <body>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Block Failed!',
                    text: 'Unable to update database state. Please try again.',
                    confirmButtonColor: '#dc2626'
                }).then(() => {
                    window.location.href='manage-organizers.php';
                });
            });
        </script>
    </body>
    </html>";
}
?>