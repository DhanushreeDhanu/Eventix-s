<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['organizer_id'])) {
    header("Location: login.php");
    exit();
}

$organizer_id = $_SESSION['organizer_id'];
$organizer_name = $_SESSION['organizer_name'];

$event_count_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM events WHERE organizer_id='$organizer_id'");
$event_count = mysqli_fetch_assoc($event_count_query)['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Organizer Dashboard | Eventix</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            background: linear-gradient(135deg, #050816, #15162c, #4f46e5);
            color: white;
            min-height: 100vh;
            font-family: "Segoe UI", sans-serif;
        }

        .navbar {
            background: rgba(5,8,22,.88);
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

        .dashboard-section {
            padding: 60px 0;
        }

        .dashboard-card {
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.18);
            backdrop-filter: blur(18px);
            border-radius: 28px;
            padding: 35px;
            text-align: center;
            transition: .3s;
            height: 100%;
        }

        .dashboard-card:hover {
            transform: translateY(-8px);
        }

        .icon-box {
            width: 75px;
            height: 75px;
            margin: auto;
            border-radius: 22px;
            display: grid;
            place-items: center;
            font-size: 32px;
            margin-bottom: 20px;
        }

        .purple {
            background: rgba(124,58,237,.18);
            color: #c4b5fd;
        }

        .green {
            background: rgba(34,197,94,.18);
            color: #86efac;
        }

        .cyan {
            background: rgba(34,211,238,.18);
            color: #67e8f9;
        }

        .danger {
            background: rgba(239,68,68,.18);
            color: #fca5a5;
        }

        .btn-main {
            border-radius: 999px;
            padding: 10px 24px;
            font-weight: 700;
        }

        .stats-box {
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.18);
            border-radius: 28px;
            padding: 30px;
            text-align: center;
            margin-bottom: 40px;
        }

        footer {
            background: rgba(5,8,22,.88);
            color: #9ca3af;
            text-align: center;
            padding: 15px;
            margin-top: 40px;
        }
    </style>
</head>

<body>

<!-- Navbar -->
<nav class="navbar navbar-dark px-4 py-3">
    <a class="navbar-brand fw-bold d-flex align-items-center" href="../index.php">
        <span class="brand-box">
            <i class="fa-solid fa-bolt"></i>
        </span>
        Eventix Organizer
    </a>

    <div>
        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
</nav>

<!-- Dashboard -->
<section class="dashboard-section">
    <div class="container">

        <!-- Welcome -->
        <div class="stats-box">
            <h2>Welcome, <?php echo $organizer_name; ?> 👋</h2>
            <p class="text-light mt-2">
                Manage your events, volunteers, and event activities here.
            </p>

            <h3 class="mt-4">
                Total Events Created:
                <span class="text-info"><?php echo $event_count; ?></span>
            </h3>
        </div>

        <!-- Dashboard Cards -->
        <div class="row g-4">

            <!-- Create Event -->
            <div class="col-md-4">
                <div class="dashboard-card">
                    <div class="icon-box purple">
                        <i class="fa-solid fa-calendar-plus"></i>
                    </div>

                    <h4>Create Event</h4>
                    <p class="text-light">
                        Add new event details, date, venue, and volunteer needs.
                    </p>

                    <a href="create-event.php"
                       class="btn btn-primary btn-main">
                        Open
                    </a>
                </div>
            </div>

            <!-- My Events -->
            <div class="col-md-4">
                <div class="dashboard-card">
                    <div class="icon-box green">
                        <i class="fa-solid fa-list-check"></i>
                    </div>

                    <h4>My Events</h4>
                    <p class="text-light">
                        View and manage all created events.
                    </p>

                    <a href="my-events.php"
                       class="btn btn-success btn-main">
                        Open
                    </a>
                </div>
            </div>

            <!-- Volunteers -->
            <div class="col-md-4">
                <div class="dashboard-card">
                    <div class="icon-box cyan">
                        <i class="fa-solid fa-users"></i>
                    </div>

                    <h4>View Volunteers</h4>
                    <p class="text-light">
                        See volunteers joined for your events.
                    </p>

                    <a href="volunteers.php"
                       class="btn btn-info btn-main">
                        Open
                    </a>
                </div>
            </div>

            <!-- Profile -->
            <div class="col-md-6">
                <div class="dashboard-card">
                    <div class="icon-box purple">
                        <i class="fa-solid fa-user"></i>
                    </div>

                    <h4>Organizer Profile</h4>
                    <p class="text-light">
                        View your organizer account details.
                    </p>

                    <a href="#"
                       class="btn btn-secondary btn-main">
                        View
                    </a>
                </div>
            </div>

            <!-- Logout -->
            <div class="col-md-6">
                <div class="dashboard-card">
                    <div class="icon-box danger">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </div>

                    <h4>Logout</h4>
                    <p class="text-light">
                        Securely logout from your organizer account.
                    </p>

                    <a href="logout.php"
                       class="btn btn-danger btn-main">
                        Logout
                    </a>
                </div>
            </div>

        </div>

    </div>
</section>

<footer>
    © 2026 Eventix | Organizer Dashboard
</footer>

</body>
</html> 