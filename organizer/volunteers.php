<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['organizer_id'])) {
    header("Location: login.php");
    exit();
}

$organizer_id = $_SESSION['organizer_id'];
$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($event_id <= 0) {
    header("Location: my-events.php");
    exit();
}

$event_stmt = $conn->prepare("SELECT * FROM events WHERE id = ? AND organizer_id = ?");
$event_stmt->bind_param("ii", $event_id, $organizer_id);
$event_stmt->execute();
$event = $event_stmt->get_result()->fetch_assoc();

if (!$event) {
    header("Location: my-events.php");
    exit();
}

$sql = "
SELECT 
    ve.id AS join_id,
    ve.joined_at,
    ve.payment_status,
    v.payment_qr AS volunteer_qr,
    v.full_name AS name,
    v.email,
    v.phone
FROM volunteer_events ve
JOIN volunteers v ON ve.volunteer_id = v.id
WHERE ve.event_id = ?
ORDER BY ve.joined_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

function safe($value) {
    return !empty($value) ? htmlspecialchars($value) : "Not added";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Event Volunteers | Eventix</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<h2>Joined Volunteers</h2>
<p>View volunteers registered for this event and manage payment details.</p>

<a href="my-events.php">Back to Events</a>

<?php if ($result->num_rows > 0) { ?>

    <?php while ($vol = $result->fetch_assoc()) { 
        $status = !empty($vol['payment_status']) ? strtolower($vol['payment_status']) : "pending";
    ?>

        <div class="card">
            <h3><?php echo safe($vol['name']); ?></h3>

            <p><b>Volunteer ID:</b> <?php echo $vol['join_id']; ?></p>
            <p><b>Email:</b> <?php echo safe($vol['email']); ?></p>
            <p><b>Phone:</b> <?php echo safe($vol['phone']); ?></p>
            <p><b>Joined:</b> <?php echo safe($vol['joined_at']); ?></p>
            <p><b>Payment Status:</b> <?php echo ucfirst($status); ?></p>

            <h4>Volunteer Payment Scanner</h4>

            <?php if (!empty($vol['volunteer_qr'])) { ?>
                <img src="../uploads/<?php echo htmlspecialchars($vol['volunteer_qr']); ?>" width="180">
            <?php } else { ?>
                <p>No QR uploaded by volunteer.</p>
            <?php } ?>

            <?php if ($status !== "paid") { ?>
                <br><br>
                <a href="mark-paid.php?id=<?php echo $vol['join_id']; ?>&event_id=<?php echo $event_id; ?>">
                    Mark Paid
                </a>
            <?php } ?>

            <br><br>
            <a href="mailto:<?php echo htmlspecialchars($vol['email']); ?>">Email</a>
        </div>

        <hr>

    <?php } ?>

<?php } else { ?>

    <h3>No Volunteers Joined Yet</h3>
    <p>Once volunteers join this event, their details will appear here.</p>

<?php } ?>

<a href="my-events.php">Back to My Events</a>

</body>
</html>