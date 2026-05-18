<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage-events.php");
    exit();
}

$event_id = intval($_GET['id']);

// Check event exists
$check = $conn->prepare("
    SELECT e.id, e.event_name, u.name AS organizer_name
    FROM events e
    JOIN users u ON e.organizer_id = u.id
    WHERE e.id=?
");
$check->bind_param("i", $event_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows == 0) {

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
                text: 'This event does not exist or may already be deleted.',
                confirmButtonColor: '#dc2626'
            }).then(() => {
                window.location.href='manage-events.php';
            });
        </script>
    </body>
    </html>";
    exit();
}

// Delete volunteer registrations first
$delete_volunteers = $conn->prepare("DELETE FROM volunteer_events WHERE event_id=?");
$delete_volunteers->bind_param("i", $event_id);
$delete_volunteers->execute();

// Delete event
$stmt = $conn->prepare("DELETE FROM events WHERE id=?");
$stmt->bind_param("i", $event_id);

if ($stmt->execute()) {

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
                title: 'Event Deleted!',
                text: 'The event has been permanently removed successfully.',
                confirmButtonColor: '#2563eb',
                confirmButtonText: 'Back to Manage Events'
            }).then(() => {
                window.location.href='manage-events.php';
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
        <title>Delete Failed</title>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Delete Failed!',
                text: 'Unable to delete event. Please try again.',
                confirmButtonColor: '#dc2626'
            }).then(() => {
                window.location.href='manage-events.php';
            });
        </script>
    </body>
    </html>";
}
?>