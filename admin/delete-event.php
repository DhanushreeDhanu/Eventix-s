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

// First delete related volunteer records
$delete_volunteers = $conn->prepare("DELETE FROM volunteer_events WHERE event_id = ?");
$delete_volunteers->bind_param("i", $event_id);
$delete_volunteers->execute();

// Now delete event
$stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);

if ($stmt->execute()) {
    header("Location: manage-events.php?deleted=1");
    exit();
} else {
    header("Location: manage-events.php?error=1");
    exit();
}
?>