<?php
session_start();
// 1. Fixed the stray '+' syntax error and corrected pathing if this is inside the admin/ folder
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

// 2. Added verification check to make sure the organizer actually exists
$check = $conn->prepare("SELECT id FROM users WHERE id=? AND role='organizer'");
$check->bind_param("i", $organizer_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows == 0) {
    $check->close();
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

// 3. Update the status to 'blocked' (Make sure 'organizer_status' exists in your DB!)
$stmt = $conn->prepare("UPDATE users SET organizer_status='blocked' WHERE id=? AND role='organizer'");
$stmt->bind_param("i", $organizer_id);

if ($stmt->execute()) {
    // 4. Wrapped SweetAlert in proper clean HTML shell for correct rendering
    echo "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <title>Organizer Blocked</title>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Organizer Blocked!',
                text: 'Organizer account has been blocked successfully.',
                confirmButtonColor: '#2563eb'
            }).then(() => {
                window.location.href='manage-organizers.php';
            });
        </script>
    </body>
    </html>";
    exit();
} else {
    echo "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <title>Action Failed</title>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Failed!',
                text: 'Something went wrong while blocking the account.',
                confirmButtonColor: '#dc2626'
            }).then(() => {
                window.location.href='manage-organizers.php';
            });
        </script>
    </body>
    </html>";
    exit();
}
?>