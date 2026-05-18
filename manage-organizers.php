<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$organizers = $conn->query("
    SELECT 
        u.id,
        u.name,
        u.email,
        u.phone,
        u.created_at,
        COALESCE(u.organizer_status, 'active') AS organizer_status,
        COUNT(e.id) AS total_events
    FROM users u
    LEFT JOIN events e ON u.id = e.organizer_id
    WHERE u.role = 'organizer'
    GROUP BY u.id
    ORDER BY u.id DESC
");

function safe($value) {
    return !empty($value) ? htmlspecialchars($value) : "Not added";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Organizers | Eventix Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            background: #f4f6fb;
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
            max-width: 1250px;
            margin: auto;
            padding: 35px 15px;
        }

        .hero {
            background: linear-gradient(135deg, #2563eb, #06b6d4);
            color: white;
            border-radius: 26px;
            padding: 32px;
            margin-bottom: 25px;
            box-shadow: 0 18px 45px rgba(37,99,235,.25);
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

        .badge-active {
            background: #dcfce7;
            color: #166534;
            padding: 8px 14px;
            border-radius: 999px;
            font-weight: 800;
            font-size: 12px;
        }

        .badge-blocked {
            background: #fee2e2;
            color: #991b1b;
            padding: 8px 14px;
            border-radius: 999px;
            font-weight: 800;
            font-size: 12px;
        }

        .btn-pill {
            border-radius: 999px;
            font-weight: 700;
            padding: 7px 14px;
        }

        .empty-box {
            text-align: center;
            padding: 60px 20px;
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
        <a href="manage-volunteers.php" class="btn btn-outline-warning btn-sm me-2">Volunteers</a>
        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
</nav>

<div class="page">

    <section class="hero">
        <h1>Manage Organizers</h1>
        <p class="mb-0">
            View organizer accounts, event count, and block suspicious organizers.
        </p>
    </section>

    <div class="card-box">

        <?php if ($organizers && $organizers->num_rows > 0) { ?>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Organizer</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Total Events</th>
                            <th>Status</th>
                            <th>Joined On</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $i = 1; while ($row = $organizers->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $i++; ?></td>

                                <td>
                                    <strong><?php echo safe($row['name']); ?></strong>
                                </td>

                                <td><?php echo safe($row['email']); ?></td>

                                <td><?php echo safe($row['phone']); ?></td>

                                <td>
                                    <span class="badge bg-primary rounded-pill">
                                        <?php echo $row['total_events']; ?>
                                    </span>
                                </td>

                                <td>
                                    <?php if ($row['organizer_status'] == 'blocked') { ?>
                                        <span class="badge-blocked">Blocked</span>
                                    <?php } else { ?>
                                        <span class="badge-active">Active</span>
                                    <?php } ?>
                                </td>

                                <td>
                                    <?php echo !empty($row['created_at']) ? date("d M Y", strtotime($row['created_at'])) : "Not added"; ?>
                                </td>

                                <td class="text-end">
                                    <?php if ($row['organizer_status'] == 'blocked') { ?>
                                        <a href="activate-organizer.php?id=<?php echo $row['id']; ?>"
                                           class="btn btn-success btn-sm btn-pill"
                                           onclick="return confirmActivate(event, this.href);">
                                            <i class="fa-solid fa-check me-1"></i>
                                            Activate
                                        </a>
                                    <?php } else { ?>
                                        <a href="block-organizer.php?id=<?php echo $row['id']; ?>"
                                           class="btn btn-danger btn-sm btn-pill"
                                           onclick="return confirmBlock(event, this.href);">
                                            <i class="fa-solid fa-ban me-1"></i>
                                            Block
                                        </a>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

        <?php } else { ?>

            <div class="empty-box">
                <i class="fa-solid fa-user-tie fa-4x mb-3"></i>
                <h4>No organizers found</h4>
                <p>No organizer accounts are registered yet.</p>
            </div>

        <?php } ?>

    </div>

</div>

<footer>
    © 2026 Eventix | Admin Manage Organizers
</footer>

<script>
function confirmBlock(event, url) {
    event.preventDefault();

    Swal.fire({
        title: 'Block Organizer?',
        text: 'This organizer will not be able to login until activated again.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Block',
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

function confirmActivate(event, url) {
    event.preventDefault();

    Swal.fire({
        title: 'Activate Organizer?',
        text: 'This organizer will be allowed to login again.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Activate',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#16a34a',
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