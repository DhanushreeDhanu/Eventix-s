<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['volunteer_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['event_id'])) {
    header("Location: available-events.php");
    exit();
}

$volunteer_id = $_SESSION['volunteer_id'];
$event_id = intval($_GET['event_id']);

/* Check event exists */
$event_stmt = $conn->prepare("SELECT * FROM events WHERE id=? AND status='upcoming' AND event_date >= CURDATE()");
$event_stmt->bind_param("i", $event_id);
$event_stmt->execute();
$event = $event_stmt->get_result()->fetch_assoc();

if (!$event) {
    header("Location: available-events.php");
    exit();
}

/* Check already joined */
$check = $conn->prepare("SELECT id FROM volunteer_events WHERE volunteer_id=? AND event_id=?");
$check->bind_param("ii", $volunteer_id, $event_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    $status = "already";
} else {
    $status = "new";
}

/* Join event */
if (isset($_POST['join_event']) && $status == "new") {

    $stmt = $conn->prepare("INSERT INTO volunteer_events
        (volunteer_id, event_id, attendance_status, payment_status, joined_at)
        VALUES (?, ?, 'pending', 'pending', NOW())");

    $stmt->bind_param("ii", $volunteer_id, $event_id);

    if ($stmt->execute()) {
        $status = "joined";
    } else {
        $status = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Join Event | Eventix</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(124,58,237,.35), transparent 35%),
                radial-gradient(circle at bottom right, rgba(34,211,238,.22), transparent 30%),
                linear-gradient(135deg, #050816, #15162c, #4f46e5);
            font-family: "Segoe UI", sans-serif;
            color: white;
        }

        .navbar {
            background: rgba(5,8,22,.9);
            backdrop-filter: blur(15px);
        }

        .brand-box {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            background: linear-gradient(135deg, #7c3aed, #22d3ee);
            display: inline-grid;
            place-items: center;
            margin-right: 10px;
        }

        .wrapper {
            padding: 60px 0;
        }

        .join-card {
            background: rgba(255,255,255,.98);
            color: #111827;
            border-radius: 32px;
            padding: 35px;
            box-shadow: 0 30px 90px rgba(0,0,0,.35);
        }

        .event-type {
            display: inline-block;
            padding: 7px 15px;
            border-radius: 999px;
            background: rgba(124,58,237,.12);
            color: #6d28d9;
            font-weight: 800;
            font-size: 13px;
            margin-bottom: 14px;
        }

        .info-line {
            margin-top: 13px;
            color: #4b5563;
            overflow-wrap: anywhere;
        }

        .info-line i {
            width: 25px;
            color: #7c3aed;
        }

        .section-box {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            padding: 17px;
            margin-top: 16px;
            color: #374151;
            line-height: 1.7;
            overflow-wrap: anywhere;
        }

        .payment-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
            border-radius: 18px;
            padding: 17px;
            margin-top: 16px;
        }

        .btn-main {
            background: linear-gradient(135deg, #7c3aed, #ec4899);
            color: white;
            border: none;
            border-radius: 999px;
            padding: 13px;
            font-weight: 900;
        }

        .btn-main:hover {
            color: white;
        }

        footer {
            background: rgba(5,8,22,.9);
            color: #9ca3af;
            text-align: center;
            padding: 15px;
            margin-top: 40px;
        }
    </style>
</head>

<body>

<nav class="navbar navbar-dark px-4 py-3">
    <a class="navbar-brand fw-bold d-flex align-items-center" href="dashboard.php">
        <span class="brand-box">
            <i class="fa-solid fa-bolt"></i>
        </span>
        Eventix Volunteer
    </a>

    <div>
        <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">Dashboard</a>
        <a href="available-events.php" class="btn btn-light btn-sm me-2">Available Events</a>
        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
</nav>
<section class="wrapper">
    <div class="container">
        <div class="join-card mx-auto" style="max-width: 850px;">

            <span class="event-type">
                <?php echo htmlspecialchars($event['event_type']); ?>
            </span>

            <h2 class="fw-bold mb-3">
                <?php echo htmlspecialchars($event['event_name']); ?>
            </h2>

            <div class="info-line">
                <i class="fa-solid fa-calendar-days"></i>
                <strong>Date:</strong>
                <?php echo date("d M Y", strtotime($event['event_date'])); ?>
            </div>

            <div class="info-line">
                <i class="fa-solid fa-clock"></i>
                <strong>Time:</strong>
                <?php echo htmlspecialchars($event['event_time']); ?>
            </div>

            <div class="info-line">
                <i class="fa-solid fa-location-dot"></i>
                <strong>Venue:</strong>
                <?php echo htmlspecialchars($event['venue']); ?>
            </div>

            <div class="info-line">
                <i class="fa-solid fa-users"></i>
                <strong>Required Volunteers:</strong>
                <?php echo htmlspecialchars($event['required_volunteers']); ?>
            </div>

            <?php if (!empty($event['google_map_link'])) { ?>
                <div class="mt-3">
                    <a href="<?php echo htmlspecialchars($event['google_map_link']); ?>"
                       target="_blank"
                       class="btn btn-outline-primary btn-sm rounded-pill px-3">
                        <i class="fa-solid fa-map-location-dot me-1"></i>
                        View Location
                    </a>
                </div>
            <?php } ?>

            <div class="section-box">
                <strong>Volunteer Duties:</strong><br>
                <?php echo nl2br(htmlspecialchars($event['volunteer_duties'])); ?>
            </div>

            <div class="section-box">
                <strong>Special Instructions:</strong><br>
                <?php echo nl2br(htmlspecialchars($event['instructions'])); ?>
            </div>

            <div class="section-box">
                <strong>Event Description:</strong><br>
                <?php echo nl2br(htmlspecialchars($event['description'])); ?>
            </div>

            <div class="payment-box">
                <strong>Volunteer Payment:</strong>
                ₹<?php echo htmlspecialchars($event['volunteer_payment']); ?> per person<br>

                <strong>Payment Schedule:</strong>
                <?php echo htmlspecialchars($event['payment_timeline']); ?><br>

                <strong>Payment Method:</strong>
                <?php echo htmlspecialchars($event['payment_method']); ?>
            </div>

            <?php if ($status == "new") { ?>
                <form method="POST" class="mt-4">
                    <button type="submit"
                            name="join_event"
                            class="btn btn-main w-100">
                        <i class="fa-solid fa-handshake-angle me-2"></i>
                        Confirm & Join Event
                    </button>
                </form>
            <?php } else { ?>
                <div class="alert alert-info rounded-4 mt-4 mb-0">
                    <strong>Status:</strong>
                    You have already joined this event.
                </div>
            <?php } ?>

            <a href="available-events.php"
               class="btn btn-outline-secondary w-100 rounded-pill mt-3">
                Back to Available Events
            </a>

        </div>
    </div>
</section>

<footer>
    © 2026 Eventix | Join Event
</footer>

<?php if ($status == "joined") { ?>
<script>
Swal.fire({
    title: 'Joined Successfully!',
    text: 'You have joined this event. Attendance approval is pending.',
    icon: 'success',
    confirmButtonText: 'View My Joined Events',
    confirmButtonColor: '#7c3aed',
    allowOutsideClick: false
}).then(() => {
    window.location.href = 'joined-events.php';
});
</script>
<?php } ?>

<?php if ($status == "already") { ?>
<script>
Swal.fire({
    title: 'Already Joined!',
    text: 'You have already registered for this event.',
    icon: 'info',
    confirmButtonText: 'Back to Events',
    confirmButtonColor: '#7c3aed'
});
</script>
<?php } ?>

<?php if ($status == "error") { ?>
<script>
Swal.fire({
    title: 'Something went wrong!',
    text: 'Could not join the event. Please try again.',
    icon: 'error',
    confirmButtonColor: '#dc2626'
});
</script>
<?php } ?>

</body>
</html>