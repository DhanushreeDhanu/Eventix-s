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

$check = $conn->prepare("SELECT id, name, role FROM users WHERE id=? AND role='organizer'");
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

$organizer = $result->fetch_assoc();

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
    </head>
    <body>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Organizer Blocked!',
                text: 'Organizer account has been blocked successfully.',
                confirmButtonColor: '#2563eb',
                confirmButtonText: 'Back to Manage Organizers'
            }).then(() => {
                window.location.href='manage-organizers.php';
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
    </head>
    <body>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Block Failed!',
                text: 'Unable to block organizer. Please try again.',
                confirmButtonColor: '#dc2626'
            }).then(() => {
                window.location.href='manage-organizers.php';
            });
        </script>
    </body>
    </html>";
}
?>