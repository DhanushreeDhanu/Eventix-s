<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['volunteer_id'])) {
    header("Location: login.php");
    exit();
}

$volunteer_id = $_SESSION['volunteer_id'];

$events = $conn->query("
    SELECT e.*, 
           (SELECT COUNT(*) 
            FROM volunteer_events ve 
            WHERE ve.event_id = e.id 
            AND ve.volunteer_id = '$volunteer_id') AS is_joined
    FROM events e
    ORDER BY e.event_date ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Available Events | Eventix</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            min-height: 100vh;
            background: #0f0c29;
            font-family: "Segoe UI", sans-serif;
            color: white;
        }

        .wrapper {
            padding: 55px 0;
        }

        .page-card {
            background: rgba(255,255,255,.1);
            border-radius: 25px;
            padding: 32px;
            margin-bottom: 35px;
        }

        .event-card {
            background: white;
            color: #111827;
            border-radius: 25px;
            padding: 26px;
            height: 100%;
        }

        .event-type {
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

        .empty-box {
            background: rgba(255,255,255,.1);
            border-radius: 25px;
            padding: 55px;
            text-align: center;
        }
    </style>
</head>

<body>

<section class="wrapper">
    <div class="container">

        <div class="page-card">
            <h2>Available Events for Volunteers 🚀</h2>
            <p class="mb-0">Explore professional events posted by organizers and join opportunities.</p>
        </div>

        <?php if ($events && $events->num_rows > 0) { ?>

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
                                ₹<?php echo htmlspecialchars($event['volunteer_payment']); ?> per person<br>

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

                                <?php if ($event['is_joined'] > 0) { ?>

                                    <button class="btn btn-secondary w-100" disabled>
                                        <i class="fa-solid fa-check-circle me-2"></i>
                                        Already Joined
                                    </button>

                                <?php } else { ?>

                                    <a href="join-event.php?event_id=<?php echo $event['id']; ?>"
                                       class="btn btn-main w-100">
                                        <i class="fa-solid fa-handshake-angle me-2"></i>
                                        Join This Event
                                    </a>

                                <?php } ?>

                            </div>

                        </div>
                    </div>

                <?php } ?>

            </div>

        <?php } else { ?>

            <div class="empty-box">
                <h3>No events available right now.</h3>
            </div>

        <?php } ?>

    </div>
</section>

</body>
</html>