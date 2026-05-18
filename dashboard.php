<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

function getCount($conn, $sql) {
    $result = $conn->query($sql);
    if ($result) {
        return $result->fetch_assoc()['total'];
    }
    return 0;
}

$total_organizers = getCount($conn, "SELECT COUNT(*) AS total FROM users WHERE role='organizer'");
$total_volunteers = getCount($conn, "SELECT COUNT(*) AS total FROM volunteers");
$total_events = getCount($conn, "SELECT COUNT(*) AS total FROM events");
$total_joined = getCount($conn, "SELECT COUNT(*) AS total FROM volunteer_events");
$blocked_organizers = getCount($conn, "SELECT COUNT(*) AS total FROM users WHERE role='organizer' AND organizer_status='blocked'");
$pending_payments = getCount($conn, "SELECT COUNT(*) AS total FROM volunteer_events WHERE payment_status='pending'");

$recent_events = $conn->query("
    SELECT 
        e.id,
        e.event_name,
        e.event_type,
        e.event_date,
        e.event_time,
        e.venue,
        e.payment_timeline,
        u.name AS organizer_name,
        COUNT(ve.id) AS joined_count
    FROM events e
    JOIN users u ON e.organizer_id = u.id
    LEFT JOIN volunteer_events ve ON e.id = ve.event_id
    GROUP BY e.id
    ORDER BY e.id DESC
    LIMIT 5
");

function safe($value) {
    return !empty($value) ? htmlspecialchars($value) : "Not added";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | Eventix</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            background: #f4f7fb;
            font-family: "Segoe UI", sans-serif;
            color: #111827;
            margin: 0;
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
            max-width: 1350px;
            margin: auto;
            padding: 35px 15px;
        }

        .hero {
            background: linear-gradient(135deg, #111827, #2563eb);
            color: white;
            border-radius: 28px;
            padding: 35px;
            margin-bottom: 25px;
            box-shadow: 0 18px 50px rgba(17,24,39,.22);
        }

        .hero h1 {
            font-weight: 900;
            margin-bottom: 8px;
        }

        .hero p {
            color: #dbeafe;
            margin-bottom: 0;
        }

        .stat-card {
            background: white;
            border-radius: 24px;
            padding: 24px;
            height: 100%;
            box-shadow: 0 8px 30px rgba(0,0,0,.07);
            border: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .icon-box {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            display: grid;
            place-items: center;
            font-size: 24px;
            background: #eef2ff;
            color: #2563eb;
            flex-shrink: 0;
        }

        .stat-card h2 {
            font-weight: 900;
            margin: 0;
        }

        .stat-card p {
            color: #6b7280;
            margin-bottom: 0;
        }

        .card-box {
            background: white;
            border-radius: 24px;
            padding: 25px;
            box-shadow: 0 8px 30px rgba(0,0,0,.07);
            border: 1px solid #e5e7eb;
            height: 100%;
        }

        .section-title {
            font-weight: 900;
            color: #111827;
            margin-bottom: 20px;
        }

        .action-card {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 18px;
            border-radius: 18px;
            text-decoration: none;
            color: #111827;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            transition: .2s;
            height: 100%;
        }

        .action-card:hover {
            background: #eef2ff;
            border-color: #c7d2fe;
            transform: translateY(-4px);
            color: #111827;
        }

        .action-icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            background: #dbeafe;
            color: #2563eb;
            display: grid;
            place-items: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .table {
            vertical-align: middle;
        }

        .badge-count {
            background: #2563eb;
            color: white;
            padding: 7px 13px;
            border-radius: 999px;
            font-weight: 800;
            font-size: 12px;
        }

        .badge-warning-soft {
            background: #fef3c7;
            color: #92400e;
            padding: 7px 13px;
            border-radius: 999px;
            font-weight: 800;
            font-size: 12px;
        }

        .empty-box {
            text-align: center;
            padding: 45px 15px;
            color: #64748b;
        }

        footer {
            background: #111827;
            color: #9ca3af;
            text-align: center;
            padding: 15px;
            margin-top: 35px;
        }

        @media(max-width: 768px) {
            .topbar {
                padding: 15px 18px;
            }

            .hero {
                padding: 25px;
            }
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
        <a href="manage-events.php" class="btn btn-outline-light btn-sm me-2">Events</a>
        <a href="manage-organizers.php" class="btn btn-outline-info btn-sm me-2">Organizers</a>
        <a href="manage-volunteers.php" class="btn btn-outline-warning btn-sm me-2">Volunteers</a>
        <a href="settings.php" class="btn btn-outline-secondary btn-sm me-2">Settings</a>
        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
</nav>

<div class="page">

    <section class="hero">
        <h1>Admin Dashboard</h1>
        <p>
            Monitor organizers, volunteers, events, registrations, and payment responsibility across Eventix.
        </p>
    </section>

    <div class="row g-4 mb-4">

        <div class="col-md-4">
            <div class="stat-card">
                <div class="icon-box">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
                <div>
                    <h2><?php echo $total_organizers; ?></h2>
                    <p>Total Organizers</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <div class="icon-box">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <h2><?php echo $total_volunteers; ?></h2>
                    <p>Total Volunteers</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <div class="icon-box">
                    <i class="fa-solid fa-calendar-days"></i>
                </div>
                <div>
                    <h2><?php echo $total_events; ?></h2>
                    <p>Total Events</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <div class="icon-box">
                    <i class="fa-solid fa-handshake-angle"></i>
                </div>
                <div>
                    <h2><?php echo $total_joined; ?></h2>
                    <p>Total Volunteer Registrations</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <div class="icon-box">
                    <i class="fa-solid fa-ban"></i>
                </div>
                <div>
                    <h2><?php echo $blocked_organizers; ?></h2>
                    <p>Blocked Organizers</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <div class="icon-box">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div>
                    <h2><?php echo $pending_payments; ?></h2>
                    <p>Pending Payment Records</p>
                </div>
            </div>
        </div>

    </div>

    <div class="row g-4">

        <div class="col-lg-4">
            <div class="card-box">
                <h4 class="section-title">Quick Admin Actions</h4>

                <div class="d-grid gap-3">

                    <a href="manage-events.php" class="action-card">
                        <div class="action-icon">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>
                        <div>
                            <strong>Manage Events</strong><br>
                            <small class="text-muted">Review and delete fake events</small>
                        </div>
                    </a>

                    <a href="manage-organizers.php" class="action-card">
                        <div class="action-icon">
                            <i class="fa-solid fa-user-tie"></i>
                        </div>
                        <div>
                            <strong>Manage Organizers</strong><br>
                            <small class="text-muted">Block or activate organizers</small>
                        </div>
                    </a>

                    <a href="manage-volunteers.php" class="action-card">
                        <div class="action-icon">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <div>
                            <strong>Manage Volunteers</strong><br>
                            <small class="text-muted">View registered volunteers</small>
                        </div>
                    </a>

                    <a href="settings.php" class="action-card">
                        <div class="action-icon">
                            <i class="fa-solid fa-gear"></i>
                        </div>
                        <div>
                            <strong>Settings</strong><br>
                            <small class="text-muted">Admin system settings</small>
                        </div>
                    </a>

                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card-box">
                <h4 class="section-title">Recent Events</h4>

                <?php if ($recent_events && $recent_events->num_rows > 0) { ?>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Event</th>
                                    <th>Organizer</th>
                                    <th>Date</th>
                                    <th>Joined</th>
                                    <th>Payment Timeline</th>
                                    <th>View</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php while ($event = $recent_events->fetch_assoc()) { ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo safe($event['event_name']); ?></strong><br>
                                            <small class="text-muted">
                                                <?php echo safe($event['event_type']); ?>
                                            </small>
                                        </td>

                                        <td><?php echo safe($event['organizer_name']); ?></td>

                                        <td>
                                            <?php echo date("d M Y", strtotime($event['event_date'])); ?><br>
                                            <small class="text-muted">
                                                <?php echo safe($event['event_time']); ?>
                                            </small>
                                        </td>

                                        <td>
                                            <span class="badge-count">
                                                <?php echo $event['joined_count']; ?>
                                            </span>
                                        </td>

                                        <td>
                                            <span class="badge-warning-soft">
                                                <?php echo safe($event['payment_timeline']); ?>
                                            </span>
                                        </td>

                                        <td>
                                            <a href="event-details.php?id=<?php echo $event['id']; ?>"
                                               class="btn btn-primary btn-sm rounded-pill">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                <?php } else { ?>

                    <div class="empty-box">
                        <i class="fa-solid fa-calendar-xmark fa-3x mb-3"></i>
                        <h5>No events found</h5>
                        <p>No events have been created yet.</p>
                    </div>

                <?php } ?>

            </div>
        </div>

    </div>

</div>

<footer>
    © 2026 Eventix | Admin Dashboard
</footer>

</body>
</html>