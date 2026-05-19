<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['volunteer_id'])) {
    header("Location: login.php");
    exit();
}

$volunteer_id = $_SESSION['volunteer_id'];
$volunteer_name = $_SESSION['volunteer_name'];

/* Fetch upcoming events only */
$events = $conn->query("
    SELECT *
    FROM events
    WHERE status='upcoming'
    AND event_date >= CURDATE()
    ORDER BY event_date ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Available Events | Eventix</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

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
            padding: 55px 0;
        }

        .page-card {
            background: rgba(255,255,255,.1);
            border: 1px solid rgba(255,255,255,.18);
            backdrop-filter: blur(18px);
            border-radius: 30px;
            padding: 32px;
            margin-bottom: 35px;
            box-shadow: 0 25px 70px rgba(0,0,0,.25);
        }

        .event-card {
            background: rgba(255,255,255,.97);
            color: #111827;
            border-radius: 28px;
            padding: 26px;
            height: 100%;
            box-shadow: 0 25px 65px rgba(0,0,0,.24);
            transition: .3s;
        }

        .event-card:hover {
            transform: translateY(-7px);
        }

        .event-type {
            display: inline-block;
            padding: 7px 14px;
            border-radius: 999px;
            background: rgba(124,58,237,.12);
            color: #6d28d9;
            font-weight: 800;
            font-size: 13px;
        }

        .info-line {
            margin-top: 12px;
            color: #4b5563;
            overflow-wrap: anywhere;
        }

        .info-line i {
            width: 24px;
            color: #7c3aed;
        }

        .section-box {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            padding: 16px;
            margin-top: 16px;
            color: #374151;
            line-height: 1.7;
            overflow-wrap: anywhere;
        }

        .payment-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 18px;
            padding: 16px;
            margin-top: 16px;
            color: #166534;
        }

        .btn-main {
            background: linear-gradient(135deg, #7c3aed, #ec4899);
            border: none;
            border-radius: 999px;
            color: white;
            font-weight: 800;
            padding: 10px 22px;
        }

        .btn-main:hover {
            color: white;
            transform: translateY(-2px);
        }

        .empty-box {
            background: rgba(255,255,255,.1);
            border: 1px solid rgba(255,255,255,.18);
            border-radius: 30px;
            padding: 55px;
            text-align: center;
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
        <a href="joined-events.php" class="btn btn-light btn-sm me-2">My Events</a>
        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
</nav>

<section class="wrapper">
    <div class="container">

        <div class="page-card">
            <h2>Available Events for Volunteers 🚀</h2>
            <p class="text-light mb-0">
                Explore professional events posted by organizers and join opportunities.
            </p>
        </div>

        <?php if ($events->num_rows > 0) { ?>

            <div class="row g-4">

                <?php while ($event = $events->fetch_assoc()) { ?>

                    <div class="col-lg-6">
                        <div class="event-card">

                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <h4 class="fw-bold mb-2">
                                    <?php echo htmlspecialchars($event['event_name']); ?>
                                </h4>

                                <span class="event-type">
                                    <?php echo htmlspecialchars($event['event_type']); ?>
                                </span>
                            </div>

                            <div class="info-line">
                                <i class="fa-solid fa-calendar-days"></i>
                                <?php echo date("d M Y", strtotime($event['event_date'])); ?>
                            </div>

                            <div class="info-line">
                                <i class="fa-solid fa-clock"></i>
                                <?php echo htmlspecialchars($event['event_time']); ?>
                            </div>

                            <div class="info-line">
                                <i class="fa-solid fa-location-dot"></i>
                                <?php echo htmlspecialchars($event['venue']); ?>
                            </div>

                            <div class="info-line">
                                <i class="fa-solid fa-users"></i>
                                Required Volunteers:
                                <?php echo htmlspecialchars($event['required_volunteers']); ?>
                            </div>

                            <div class="section-box">
                                <strong>Volunteer Duties:</strong><br>
                                <?php echo nl2br(htmlspecialchars($event['volunteer_duties'])); ?>
                            </div>

                            <div class="section-box">
                                <strong>Special Instructions:</strong><br>
                                <?php echo nl2br(htmlspecialchars($event['instructions'])); ?>
                            </div>

                            <div class="payment-box">
                                <strong>Volunteer Payment:</strong>
                                ₹<?php echo htmlspecialchars($event['volunteer_payment']); ?>
                                per person<br>

                                <strong>Payment Schedule:</strong>
                                <?php echo htmlspecialchars($event['payment_timeline']); ?><br>

                                <strong>Payment Method:</strong>
                                <?php echo htmlspecialchars($event['payment_method']); ?>
                            </div>
                                                        <?php if (!empty($event['google_map_link'])) { ?>
                                <div class="mt-3">
                                    <a href="<?php echo htmlspecialchars($event['google_map_link']); ?>"
                                       target="_blank"
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="fa-solid fa-map-location-dot me-1"></i>
                                        View Location
                                    </a>
                                </div>
                            <?php } ?>

                            <div class="mt-4">
                                <a href="join-event.php?event_id=<?php echo $event['id']; ?>"
                                   class="btn btn-main w-100">
                                    <i class="fa-solid fa-handshake-angle me-2"></i>
                                    Join This Event
                                </a>
                            </div>

                        </div>
                    </div>

                <?php } ?>

            </div>

        <?php } else { ?>

            <div class="empty-box">
                <i class="fa-solid fa-calendar-xmark fa-3x mb-3"></i>
                <h3>No upcoming events available right now.</h3>
                <p class="text-light">
                    Please check back later for new opportunities.
                </p>
            </div>

        <?php } ?>

    </div>
</section>

<footer>
    © 2026 Eventix | Available Volunteer Events
</footer>

</body>
</html>