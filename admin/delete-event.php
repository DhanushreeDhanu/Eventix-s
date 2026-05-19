<?php
session_start();
include('../config/db.php');

// 1. Double check security access
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage-events.php");
    exit();
}

$event_id = intval($_GET['id']);

// 2. Verify the event actually exists before deleting anything
$check = $conn->prepare("SELECT id FROM events WHERE id = ?");
$check->bind_param("i", $event_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows == 0) {
    $check->close();
    echo "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <title>Event Not Found</title>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Event Not Found!',
                text: 'The event you are trying to delete does not exist.',
                confirmButtonColor: '#dc2626'
            }).then(() => {
                window.location.href='manage-events.php';
            });
        </script>
    </body>
    </html>";
    exit();
}
$check->close();

// 3. Clear out linked volunteer relationships first (Prevents DB constraint crashes)
$delete_volunteers = $conn->prepare("DELETE FROM volunteer_events WHERE event_id = ?");
$delete_volunteers->bind_param("i", $event_id);
$delete_volunteers->execute();
$delete_volunteers->close();

// 4. Delete the main event record
$stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);

if ($stmt->execute()) {
    $stmt->close();
    // Integrated SweetAlert2 matching your dashboard styling
    echo "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <title>Event Deleted</title>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Deleted Successfully!',
                text: 'The event and all linked volunteer registrations have been removed.',
                confirmButtonColor: '#2563eb'
            }).then(() => {
                window.location.href='manage-events.php';
            });
        </script>
    </body>
    </html>";
    exit();
} else {
    $stmt->close();
    echo "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <title>Deletion Failed</title>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Action Failed!',
                text: 'Something went wrong while deleting the event.',
                confirmButtonColor: '#dc2626'
            }).then(() => {
                window.location.href='manage-events.php';
            });
        </script>
    </body>
    </html>";
    exit();
}
?>