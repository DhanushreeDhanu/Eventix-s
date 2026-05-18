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

$event_stmt = $conn->prepare("
    SELECT 
        e.*,
        u.name AS organizer_name,
        u.email AS organizer_email,
        u.phone AS organizer_phone,
        COALESCE(u.organizer_status, 'active') AS organizer_status
    FROM events e
    JOIN users u ON e.organizer_id = u.id
    WHERE e.id=?
");
$event_stmt->bind_param("i", $event_id);
$event_stmt->execute();
$event = $event_stmt->get_result()->fetch_assoc();

if (!$event) {
    header("Location: manage-events.php");
    exit();
}

$vol_stmt = $conn->prepare("
    SELECT 
        ve.id AS join_id,
        ve.joined_at,
        ve.attendance_status,
        ve.payment_status,
        v.full_name,
        v.email,
        v.phone,
        v.college,
        v.skills,
        v.payment_qr
    FROM volunteer_events ve
    JOIN volunteers v ON ve.volunteer_id = v.id
    WHERE ve.event_id=?
    ORDER BY ve.joined_at DESC
");
$vol_stmt->bind_param("i", $event_id);
$vol_stmt->execute();
$volunteers = $vol_stmt->get_result();

function safe($value) {
    return !empty($value) ? htmlspecialchars($value) : "Not added";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Details | Eventix Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            background: #f4f7fb;
            font-family: "Segoe UI", sans-serif;
            color: #111827;
        }

        .topbar {
            background: #111827;
            padding: 16px 35px;
        }

        .brand {
            color: white;
            font-size: 24px;
            font-weight: 900;
            text-decoration: none;
        }

        .brand i {
            background: linear-gradient(135deg, #2563eb, #06b6d4);
            padding: 10px;
            border-radius: 14px;
            margin-right: 8px;
        }

        .page {
            max-width: 1300px;
            margin: auto;
            padding: 35px 15px;
        }

        .hero {
            background: linear-gradient(135deg, #2563eb, #06b6d4);
            color: white;
            border-radius: 28px;
            padding: 32px;
            margin-bottom: 25px;
            box-shadow: 0 18px 50px rgba(37,99,235,.25);
        }

        .hero h1 {
            font-weight: 900;
        }

        .card-box {
            background: white;
            border-radius: 24px;
            padding: 25px;
            box-shadow: 0 8px 30px rgba(0,0,0,.07);
            height: 100%;
        }

        .section-title {
            font-weight: 900;
            color: #2563eb;
            margin-bottom: 18px;
        }

        .info-line {
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .info-line:last-child {
            border-bottom: none;
        }

        .label {
            font-size: 12px;
            font-weight: 900;
            color: #64748b;
            text-transform: uppercase;
        }

        .value {
            font-weight: 700;
            margin-top: 3px;
            overflow-wrap: anywhere;
        }

        .status-active {
            background: #dcfce7;
            color: #166534;
            padding: 7px 13px;
            border-radius: 999px;
            font-weight: 900;
            font-size: 12px;
        }

        .status-blocked {
            background: #fee2e2;
            color: #991b1b;
            padding: 7px 13px;
            border-radius: 999px;
            font-weight: 900;
            font-size: 12px;
        }

        .badge-status {
            padding: 7px 13px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 900;
            display: inline-block;
            margin: 2px;
        }

        .pending {
            background: #fef3c7;
            color: #92400e;
        }

        .approved,
        .paid {
            background: #dcfce7;
            color: #166534;
        }

        .rejected,
        .unpaid {
            background: #fee2e2;
            color: #991b1b;
        }

        .vol-card {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 20px;
            padding: 18px;
            margin-bottom: 15px;
        }

        .avatar {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            background: linear-gradient(135deg, #2563eb, #06b6d4);
            color: white;
            display: grid;
            place-items: center;
            font-size: 22px;
            font-weight: 900;
        }

        .qr-img {
            width: 120px;
            height: 120px;
            border-radius: 14px;
            object-fit: cover;
            border: 1px solid #e5e7eb;
            background: white;
            padding: 5px;
        }

        .empty-box {
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
        }

        footer {
            background: #111827;
            color: #9ca3af;
            text-align: center;
            padding: 15px;
            margin-top: 35px;
        }
    </style>
</head>

<body>

<nav class="topbar d-flex justify-content-between align-items-center flex-wrap gap-3">
    <a href="dashboard.php" class="brand">
        <i class="fa-solid fa-bolt"></i>
        Eventix Admin
    </a>

    <div>
        <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">Dashboard</a>
        <a href="manage-events.php" class="btn btn-outline-info btn-sm me-2">Events</a>
        <a href="manage-organizers.php" class="btn btn-outline-warning btn-sm me-2">Organizers</a>
        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
</nav>

<div class="page">

    <section class="hero">
        <h1><?php echo safe($event['event_name']); ?></h1>
        <p class="mb-0">
            Admin view for event details, organizer information, volunteer registration,
            attendance status, and payment monitoring.
        </p>
    </section>

    <div class="row g-4 mb-4">

        <div class="col-lg-8">
            <div class="card-box">
                <h4 class="section-title">
                    <i class="fa-solid fa-calendar-days me-2"></i>
                    Event Information
                </h4>

                <div class="row">
                    <div class="col-md-6 info-line">
                        <div class="label">Event Type</div>
                        <div class="value"><?php echo safe($event['event_type']); ?></div>
                    </div>

                    <div class="col-md-6 info-line">
                        <div class="label">Event Date</div>
                        <div class="value"><?php echo date("d M Y", strtotime($event['event_date'])); ?></div>
                    </div>

                    <div class="col-md-6 info-line">
                        <div class="label">Event Time</div>
                        <div class="value"><?php echo safe($event['event_time']); ?></div>
                    </div>

                    <div class="col-md-6 info-line">
                        <div class="label">Venue</div>
                        <div class="value"><?php echo safe($event['venue']); ?></div>
                    </div>

                    <div class="col-md-6 info-line">
                        <div class="label">Required Volunteers</div>
                        <div class="value"><?php echo safe($event['required_volunteers']); ?></div>
                    </div>

                    <div class="col-md-6 info-line">
                        <div class="label">Volunteer Payment</div>
                        <div class="value">₹<?php echo safe($event['volunteer_payment']); ?></div>
                    </div>

                    <div class="col-md-6 info-line">
                        <div class="label">Payment Timeline</div>
                        <div class="value"><?php echo safe($event['payment_timeline']); ?></div>
                    </div>

                    <div class="col-md-6 info-line">
                        <div class="label">Payment Method</div>
                        <div class="value"><?php echo safe($event['payment_method']); ?></div>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="label">Volunteer Duties</div>
                    <p class="value"><?php echo nl2br(safe($event['volunteer_duties'])); ?></p>
                </div>

                <div class="mt-4">
                    <div class="label">Special Instructions</div>
                    <p class="value"><?php echo nl2br(safe($event['instructions'])); ?></p>
                </div>

                <div class="mt-4">
                    <div class="label">Description</div>
                    <p class="value"><?php echo nl2br(safe($event['description'])); ?></p>
                </div>

                <?php if (!empty($event['google_map_link'])) { ?>
                    <a href="<?php echo safe($event['google_map_link']); ?>"
                       target="_blank"
                       class="btn btn-primary rounded-pill px-4 mt-2">
                        <i class="fa-solid fa-map-location-dot me-2"></i>
                        View Google Map
                    </a>
                <?php } ?>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-box">
                <h4 class="section-title">
                    <i class="fa-solid fa-user-tie me-2"></i>
                    Organizer Details
                </h4>

                <div class="info-line">
                    <div class="label">Organizer Name</div>
                    <div class="value"><?php echo safe($event['organizer_name']); ?></div>
                </div>

                <div class="info-line">
                    <div class="label">Email</div>
                    <div class="value"><?php echo safe($event['organizer_email']); ?></div>
                </div>

                <div class="info-line">
                    <div class="label">Phone</div>
                    <div class="value"><?php echo safe($event['organizer_phone']); ?></div>
                </div>

                <div class="info-line">
                    <div class="label">Organizer Status</div>
                    <div class="value">
                        <?php if ($event['organizer_status'] == 'blocked') { ?>
                            <span class="status-blocked">Blocked</span>
                        <?php } else { ?>
                            <span class="status-active">Active</span>
                        <?php } ?>
                    </div>
                </div>

                <div class="info-line">
                    <div class="label">Event Contact Person</div>
                    <div class="value"><?php echo safe($event['contact_person']); ?></div>
                </div>

                <div class="info-line">
                    <div class="label">Event Contact Phone</div>
                    <div class="value"><?php echo safe($event['contact_phone']); ?></div>
                </div>
            </div>
        </div>

    </div>

    <div class="card-box">
        <h4 class="section-title">
            <i class="fa-solid fa-users me-2"></i>
            Registered Volunteers
        </h4>

        <?php if ($volunteers->num_rows > 0) { ?>

            <?php while ($vol = $volunteers->fetch_assoc()) { ?>

                <div class="vol-card">
                    <div class="row align-items-center g-3">

                        <div class="col-md-1">
                            <div class="avatar">
                                <?php echo strtoupper(substr($vol['full_name'], 0, 1)); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <h5 class="fw-bold mb-1"><?php echo safe($vol['full_name']); ?></h5>
                            <small class="text-muted"><?php echo safe($vol['email']); ?></small><br>
                            <small class="text-muted"><?php echo safe($vol['phone']); ?></small>
                        </div>

                        <div class="col-md-3">
                            <strong>College:</strong><br>
                            <small><?php echo safe($vol['college']); ?></small><br>

                            <strong>Skills:</strong><br>
                            <small><?php echo nl2br(safe($vol['skills'])); ?></small>
                        </div>

                        <div class="col-md-2">
                            <span class="badge-status <?php echo strtolower($vol['attendance_status']); ?>">
                                Attendance: <?php echo ucfirst($vol['attendance_status']); ?>
                            </span><br>

                            <span class="badge-status <?php echo strtolower($vol['payment_status']); ?>">
                                Payment: <?php echo ucfirst($vol['payment_status']); ?>
                            </span><br>

                            <small class="text-muted">
                                Joined:
                                <?php echo date("d M Y", strtotime($vol['joined_at'])); ?>
                            </small>
                        </div>

                        <div class="col-md-2 text-center">
                            <?php if (!empty($vol['payment_qr'])) { ?>
                                <img src="../uploads/volunteer_qr/<?php echo safe($vol['payment_qr']); ?>"
                                     class="qr-img"
                                     alt="Payment QR">
                            <?php } else { ?>
                                <small class="text-muted">No QR uploaded</small>
                            <?php } ?>
                        </div>

                    </div>
                </div>

            <?php } ?>

        <?php } else { ?>

            <div class="empty-box">
                <i class="fa-solid fa-user-slash fa-4x mb-3"></i>
                <h4>No volunteers registered</h4>
                <p>No volunteer has joined this event yet.</p>
            </div>

        <?php } ?>

    </div>

</div>

<footer>
    © 2026 Eventix | Admin Event Details
</footer>

</body>
</html>