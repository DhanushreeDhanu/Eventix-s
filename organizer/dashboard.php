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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

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
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
        }

        .dashboard-card:hover {
            transform: translateY(-8px);
        }

        .card-content {
            width: 100%;
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

        .btn-main {
            border-radius: 999px;
            padding: 10px 24px;
            font-weight: 700;
            width: fit-content;
            margin-top: 15px;
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

<section class="dashboard-section">
    <div class="container">

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

        <div class="row g-4 justify-content-center">

            <div class="col-md-4">
                <div class="dashboard-card">
                    <div class="card-content">
                        <div class="icon-box purple">
                            <i class="fa-solid fa-calendar-plus"></i>
                        </div>
                        <h4>Create Event</h4>
                        <p class="text-light-50">
                            Add new event details, date, venue, and volunteer needs.
                        </p>
                    </div>
                    <a href="create-event.php" class="btn btn-primary btn-main">
                        Open
                    </a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="dashboard-card">
                    <div class="card-content">
                        <div class="icon-box green">
                            <i class="fa-solid fa-list-check"></i>
                        </div>
                        <h4>My Events</h4>
                        <p class="text-light-50">
                            View and manage all created events.
                        </p>
                    </div>
                    <a href="my-events.php" class="btn btn-success btn-main">
                        Open
                    </a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="dashboard-card">
                    <div class="card-content">
                        <div class="icon-box cyan">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <h4>View Volunteers</h4>
                        <p class="text-light-50">
                            See volunteers joined for your events.
                        </p>
                    </div>
                    <a href="volunteers.php" class="btn btn-info btn-main">
                        Open
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