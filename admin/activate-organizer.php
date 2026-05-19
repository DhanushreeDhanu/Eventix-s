<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage-organizers.php");
    exit();
}

$organizer_id = intval($_GET['id']);

// Verify organizer exists
$check = $conn->prepare("SELECT id FROM users WHERE id=? AND role='organizer'");
$check->bind_param("i", $organizer_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows == 0) {
    $check->close(); // Clean up connection cursor
    echo "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <title>Organizer Not Found</title>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Organizer Not Found!',
                text: 'Invalid organizer account.',
                confirmButtonColor: '#dc2626'
            }).then(() => {
                window.location.href='manage-organizers.php';
            });
        </script>
    </body>
    </html>";
    exit();
}
$check->close();

// Update status (Make sure 'organizer_status' column exists in your MySQL table!)
$stmt = $conn->prepare("UPDATE users SET organizer_status='active' WHERE id=? AND role='organizer'");
$stmt->bind_param("i", $organizer_id);

if ($stmt->execute()) {
    echo "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <title>Organizer Activated</title>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Organizer Activated!',
                text: 'Organizer account has been activated successfully.',
                confirmButtonColor: '#2563eb',
                confirmButtonText: 'Back to Manage Organizers'
            }).then(() => {
                window.location.href='manage-organizers.php';
            });
        </script>
    </body>
    </html>";
    exit(); // Added safety exit
} else {
    echo "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <title>Activation Failed</title>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Activation Failed!',
                text: 'Unable to activate organizer. Error: " . esacpe_string($conn->error) . "',
                confirmButtonColor: '#dc2626'
            }).then(() => {
                window.location.href='manage-organizers.php';
            });
        </script>
    </body>
    </html>";
    exit(); // Added safety exit
}
?>