<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$volunteers = $conn->query("
    SELECT 
        v.*,
        COUNT(ve.id) AS joined_events,
        SUM(CASE WHEN ve.attendance_status='approved' THEN 1 ELSE 0 END) AS approved_events,
        SUM(CASE WHEN ve.payment_status='paid' THEN 1 ELSE 0 END) AS paid_events
    FROM volunteers v
    LEFT JOIN volunteer_events ve ON v.id = ve.volunteer_id
    GROUP BY v.id
    ORDER BY v.id DESC
");

function safe($value) {
    return !empty($value) ? htmlspecialchars($value) : "Not added";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Volunteers | Eventix Admin</title>
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
            max-width: 1350px;
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

        .avatar {
            width: 48px;
            height: 48px;
            border-radius: 15px;
            background: linear-gradient(135deg, #2563eb, #06b6d4);
            color: white;
            display: grid;
            place-items: center;
            font-size: 20px;
            font-weight: 900;
        }

        .badge-soft {
            padding: 7px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            display: inline-block;
        }

        .badge-joined {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge-approved {
            background: #dcfce7;
            color: #166534;
        }

        .badge-paid {
            background: #ecfdf5;
            color: #047857;
        }

        .qr-img {
            width: 75px;
            height: 75px;
            border-radius: 14px;
            object-fit: cover;
            border: 1px solid #e5e7eb;
            background: white;
            padding: 4px;
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
        <a href="manage-events.php" class="btn btn-outline-info btn-sm me-2">Events</a>
        <a href="manage-organizers.php" class="btn btn-outline-warning btn-sm me-2">Organizers</a>
        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
</nav>

<div class="page">

    <section class="hero">
        <h1>Manage Volunteers</h1>
        <p class="mb-0">
            View registered volunteers, their joined events, attendance approval, payment records, and QR status.
        </p>
    </section>

    <div class="card-box">

        <?php if ($volunteers && $volunteers->num_rows > 0) { ?>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Volunteer</th>
                            <th>Contact</th>
                            <th>College</th>
                            <th>Skills</th>
                            <th>Joined</th>
                            <th>Approved</th>
                            <th>Paid</th>
                            <th>QR</th>
                            <th>Registered</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $i = 1; while ($row = $volunteers->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $i++; ?></td>

                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar">
                                            <?php echo strtoupper(substr($row['full_name'], 0, 1)); ?>
                                        </div>

                                        <div>
                                            <strong><?php echo safe($row['full_name']); ?></strong><br>
                                            <small class="text-muted">
                                                ID: <?php echo $row['id']; ?>
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <?php echo safe($row['email']); ?><br>
                                    <small class="text-muted">
                                        <?php echo safe($row['phone']); ?>
                                    </small>
                                </td>

                                <td><?php echo safe($row['college']); ?></td>

                                <td style="max-width:220px;">
                                    <?php echo nl2br(safe($row['skills'])); ?>
                                </td>

                                <td>
                                    <span class="badge-soft badge-joined">
                                        <?php echo $row['joined_events'] ?? 0; ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="badge-soft badge-approved">
                                        <?php echo $row['approved_events'] ?? 0; ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="badge-soft badge-paid">
                                        <?php echo $row['paid_events'] ?? 0; ?>
                                    </span>
                                </td>

                                <td>
                                    <?php if (!empty($row['payment_qr'])) { ?>
                                        <img src="../uploads/volunteer_qr/<?php echo safe($row['payment_qr']); ?>"
                                             class="qr-img"
                                             alt="QR">
                                    <?php } else { ?>
                                        <span class="text-muted">No QR</span>
                                    <?php } ?>
                                </td>

                                <td>
                                    <?php echo !empty($row['created_at']) ? date("d M Y", strtotime($row['created_at'])) : "Not added"; ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

        <?php } else { ?>

            <div class="empty-box">
                <i class="fa-solid fa-users-slash fa-4x mb-3"></i>
                <h4>No volunteers found</h4>
                <p>No volunteer accounts are registered yet.</p>
            </div>

        <?php } ?>

    </div>

</div>

<footer>
    © 2026 Eventix | Admin Manage Volunteers
</footer>

</body>
</html>