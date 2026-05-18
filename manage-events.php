<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$events = $conn->query("
    SELECT 
        e.*,
        u.name AS organizer_name,
        u.email AS organizer_email,
        COUNT(ve.id) AS total_volunteers
    FROM events e
    JOIN users u ON e.organizer_id = u.id
    LEFT JOIN volunteer_events ve ON e.id = ve.event_id
    GROUP BY e.id
    ORDER BY e.id DESC
");

function safe($value) {
    return !empty($value) ? htmlspecialchars($value) : "Not added";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Events | Eventix Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
            max-width: 1400px;
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
        }

        .table {
            vertical-align: middle;
        }

        .badge-paid {
            background: #dcfce7;
            color: #166534;
            padding: 7px 13px;
            border-radius: 999px;
            font-weight: 800;
        }

        .badge-pending {
            background: #fef3c7;
            color: #92400e;
            padding: 7px 13px;
            border-radius: 999px;
            font-weight: 800;
        }

        .btn-pill {
            border-radius: 999px;
            font-weight: 700;
            padding: 7px 14px;
        }

        .empty-box {
            text-align: center;
            padding: 70px 20px;
            color: #6b7280;
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
        <a href="manage-organizers.php" class="btn btn-outline-info btn-sm me-2">Organizers</a>
        <a href="manage-volunteers.php" class="btn btn-outline-warning btn-sm me-2">Volunteers</a>
        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
</nav>

<div class="page">

    <section class="hero">
        <h1>Manage Events</h1>
        <p class="mb-0">
            Monitor all organizer-created events, volunteer registrations,
            payment details, and remove suspicious events.
        </p>
    </section>

    <div class="card-box">

        <?php if ($events && $events->num_rows > 0) { ?>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Event</th>
                            <th>Organizer</th>
                            <th>Date</th>
                            <th>Venue</th>
                            <th>Volunteers</th>
                            <th>Volunteer Payment</th>
                            <th>Payment Timeline</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $i = 1; while ($row = $events->fetch_assoc()) { ?>
                            <tr>

                                <td><?php echo $i++; ?></td>

                                <td>
                                    <strong><?php echo safe($row['event_name']); ?></strong><br>
                                    <small class="text-muted">
                                        <?php echo safe($row['event_type']); ?>
                                    </small>
                                </td>

                                <td>
                                    <strong><?php echo safe($row['organizer_name']); ?></strong><br>
                                    <small class="text-muted">
                                        <?php echo safe($row['organizer_email']); ?>
                                    </small>
                                </td>

                                <td>
                                    <?php echo date("d M Y", strtotime($row['event_date'])); ?><br>
                                    <small class="text-muted">
                                        <?php echo safe($row['event_time']); ?>
                                    </small>
                                </td>

                                <td><?php echo safe($row['venue']); ?></td>

                                <td>
                                    <span class="badge bg-primary rounded-pill">
                                        <?php echo $row['total_volunteers']; ?>
                                    </span>
                                </td>

                                <td>
                                    ₹<?php echo safe($row['volunteer_payment']); ?>
                                </td>

                                <td>
                                    <?php echo safe($row['payment_timeline']); ?>
                                </td>

                                <td>
                                    <a href="event-details.php?id=<?php echo $row['id']; ?>"
                                       class="btn btn-info btn-sm btn-pill text-white mb-1">
                                        <i class="fa-solid fa-eye"></i>
                                        View
                                    </a>

                                    <a href="delete-event.php?id=<?php echo $row['id']; ?>"
                                       class="btn btn-danger btn-sm btn-pill"
                                       onclick="return confirmDelete(event, this.href);">
                                        <i class="fa-solid fa-trash"></i>
                                        Delete
                                    </a>
                                </td>

                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

        <?php } else { ?>

            <div class="empty-box">
                <i class="fa-solid fa-calendar-xmark fa-4x mb-3"></i>
                <h4>No events found</h4>
                <p>No organizer events are currently available.</p>
            </div>

        <?php } ?>

    </div>

</div>

<footer>
    © 2026 Eventix | Admin Manage Events
</footer>

<script>
function confirmDelete(event, url) {
    event.preventDefault();

    Swal.fire({
        title: 'Delete Event?',
        text: 'This event will be permanently removed from the system.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });

    return false;
}
</script>

</body>
</html>