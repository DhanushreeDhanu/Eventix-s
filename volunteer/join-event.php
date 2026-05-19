<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'volunteer') {
    header("Location: ../login.php");
    exit();
}

$volunteer_id = $_SESSION['user_id'];

if (!isset($_GET['event_id'])) {
    header("Location: available-events.php");
    exit();
}

$event_id = intval($_GET['event_id']);

// Get event details
$event_stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$event_stmt->bind_param("i", $event_id);
$event_stmt->execute();
$event = $event_stmt->get_result()->fetch_assoc();

if (!$event) {
    header("Location: available-events.php");
    exit();
}

// Check if already joined
$check = $conn->prepare("SELECT id FROM volunteer_events WHERE volunteer_id = ? AND event_id = ?");
$check->bind_param("ii", $volunteer_id, $event_id);
$check->execute();
$check_result = $check->get_result();

$already_joined = $check_result->num_rows > 0;

$message = "";

// Join event
if (isset($_POST['join_event'])) {

    if ($already_joined) {

        $message = "already";

    } else {

        $stmt = $conn->prepare("
            INSERT INTO volunteer_events
            (volunteer_id, event_id, attendance_status, payment_status, joined_at)
            VALUES (?, ?, 'pending', 'pending', NOW())
        ");

        $stmt->bind_param("ii", $volunteer_id, $event_id);

        if ($stmt->execute()) {

            $message = "joined";
            $already_joined = true;

        } else {

            $message = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Join Event | Eventix</title>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>

body{
    margin:0;
    padding:0;
    font-family: Arial, sans-serif;
    background:#f4f7fc;
}

.container{
    width:90%;
    max-width:700px;
    margin:50px auto;
}

.card{
    background:#fff;
    border-radius:15px;
    padding:30px;
    box-shadow:0 4px 15px rgba(0,0,0,0.1);
}

h1{
    color:#333;
    margin-bottom:20px;
}

.info{
    margin-bottom:15px;
    font-size:16px;
    color:#555;
}

.info strong{
    color:#111;
}

.description{
    background:#f8f9fa;
    padding:15px;
    border-radius:10px;
    margin-top:10px;
    color:#444;
}

.btn{
    margin-top:25px;
    padding:14px 25px;
    border:none;
    border-radius:10px;
    font-size:16px;
    cursor:pointer;
    transition:0.3s;
}

.join-btn{
    background:#007bff;
    color:white;
}

.join-btn:hover{
    background:#0056b3;
}

.disabled-btn{
    background:gray;
    color:white;
    cursor:not-allowed;
}

.back-btn{
    display:inline-block;
    margin-top:20px;
    text-decoration:none;
    color:#007bff;
    font-weight:bold;
}

.back-btn:hover{
    text-decoration:underline;
}

.status{
    margin-top:15px;
    color:green;
    font-weight:bold;
}

</style>
</head>

<body>

<div class="container">

    <div class="card">

        <h1><?php echo htmlspecialchars($event['title']); ?></h1>

        <div class="info">
            <strong>Date:</strong>
            <?php echo htmlspecialchars($event['event_date']); ?>
        </div>

        <div class="info">
            <strong>Time:</strong>
            <?php echo htmlspecialchars($event['event_time']); ?>
        </div>

        <div class="info">
            <strong>Venue:</strong>
            <?php echo htmlspecialchars($event['venue']); ?>
        </div>

        <div class="description">
            <strong>Description:</strong><br><br>
            <?php echo htmlspecialchars($event['description']); ?>
        </div>

        <?php if ($already_joined): ?>

            <button class="btn disabled-btn" disabled>
                Already Registered
            </button>

            <div class="status">
                You have already registered for this event.
            </div>

        <?php else: ?>

            <form method="POST">

                <button type="submit" name="join_event" class="btn join-btn">
                    Confirm & Join Event
                </button>

            </form>

        <?php endif; ?>

        <br>

        <a href="available-events.php" class="back-btn">
            ← Back to Events
        </a>

    </div>

</div>

<?php if ($message == "joined"): ?>

<script>
Swal.fire({
    icon: 'success',
    title: 'Registration Successful',
    text: 'You have joined this event successfully!',
    confirmButtonColor: '#007bff'
});
</script>

<?php elseif ($message == "already"): ?>

<script>
Swal.fire({
    icon: 'info',
    title: 'Already Registered',
    text: 'You have already joined this event.',
    confirmButtonColor: '#007bff'
});
</script>

<?php elseif ($message == "error"): ?>

<script>
Swal.fire({
    icon: 'error',
    title: 'Registration Failed',
    text: 'Something went wrong!',
    confirmButtonColor: '#007bff'
});
</script>

<?php endif; ?>

</body>
</html>