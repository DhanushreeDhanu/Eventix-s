<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include '../config/db.php';

if (!isset($_SESSION['volunteer_id'])) {
    header("Location: login.php");
    exit();
}

$volunteer_id = (int)$_SESSION['volunteer_id'];

if (!isset($_GET['event_id'])) {
    showAlert('error', 'Invalid Event', 'Event ID missing.', 'available-events.php');
}

$event_id = (int)$_GET['event_id'];

try {
    $conn->begin_transaction();

    $stmt = $conn->prepare("SELECT * FROM events WHERE id = ? FOR UPDATE");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $event = $stmt->get_result()->fetch_assoc();

    if (!$event) {
        throw new Exception("Event not found.");
    }

    $event_date = $event['event_date'];

    $same = $conn->prepare("
        SELECT ve.id
        FROM volunteer_events ve
        JOIN events e ON ve.event_id = e.id
        WHERE ve.volunteer_id = ?
        AND e.event_date = ?
        AND ve.status = 'joined'
    ");
    $same->bind_param("is", $volunteer_id, $event_date);
    $same->execute();

    if ($same->get_result()->num_rows > 0) {
        throw new Exception("You already joined another event on the same date.");
    }

    $count = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM volunteer_events
        WHERE event_id = ?
        AND status = 'joined'
    ");
    $count->bind_param("i", $event_id);
    $count->execute();
    $joined = (int)$count->get_result()->fetch_assoc()['total'];

    if ($joined >= (int)$event['required_volunteers']) {
        throw new Exception("Volunteer limit reached.");
    }

    $already = $conn->prepare("
        SELECT id
        FROM volunteer_events
        WHERE volunteer_id = ?
        AND event_id = ?
    ");
    $already->bind_param("ii", $volunteer_id, $event_id);
    $already->execute();

    if ($already->get_result()->num_rows > 0) {
        throw new Exception("You already joined this event.");
    }

    $insert = $conn->prepare("
        INSERT INTO volunteer_events
        (volunteer_id, event_id, attendance_status, payment_status, status)
        VALUES (?, ?, 'pending', 'pending', 'joined')
    ");
    $insert->bind_param("ii", $volunteer_id, $event_id);
    $insert->execute();

    $conn->commit();

    showAlert(
        'success',
        'Joined Successfully!',
        'You have joined the event successfully.',
        'joined-events.php'
    );

} catch (Exception $e) {
    if ($conn->errno === 0) {
        $conn->rollback();
    }

    showAlert(
        'error',
        'Cannot Join Event',
        $e->getMessage(),
        'available-events.php'
    );
}

function showAlert($icon, $title, $message, $redirect) {
    $title = addslashes($title);
    $message = addslashes($message);
    $redirect = addslashes($redirect);

    echo "
    <!DOCTYPE html>
    <html>
    <head>
        <title>Eventix</title>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
        <script>
            Swal.fire({
                icon: '$icon',
                title: '$title',
                text: '$message',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = '$redirect';
            });
        </script>
    </body>
    </html>
    ";
    exit();
}
?>