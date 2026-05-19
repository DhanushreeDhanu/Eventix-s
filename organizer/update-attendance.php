<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['organizer_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: volunteers.php");
    exit();
}

$id = (int)$_GET['id'];

$sql = "
    UPDATE volunteer_events ve
    JOIN events e ON ve.event_id = e.id
    SET ve.attendance_status = 'marked'
    WHERE ve.id = ?
    AND e.organizer_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $_SESSION['organizer_id']);
$stmt->execute();

header("Location: volunteers.php");
exit();
?>