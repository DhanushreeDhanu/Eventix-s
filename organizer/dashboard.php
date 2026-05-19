<?php
session_start();
include('../config/db.php');

// 1. Guard check for active organizer session
if (!isset($_SESSION['organizer_id'])) {
    header("Location: login.php");
    exit();
}

$organizer_id = $_SESSION['organizer_id'];

// FIXED: Fail-safe fallback wrapper if organizer name index isn't explicitly defined during authentication
$organizer_name = isset($_SESSION['organizer_name']) ? trim($_SESSION['organizer_name']) : 'Organizer';

// FIXED: Parameterized prepared statement to eliminate execution errors or query manipulation
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM events WHERE organizer_id = ?");
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$result = $stmt->get_result();
$event_count = $result->fetch_assoc()['total'];
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
            border-bottom: 1px solid rgba(255,255,255,.12);
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
        .dashboard-section { padding: 60px 0; }
        .dashboard-card {
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.18);
            backdrop-filter: blur(18px);
            border-radius: 28px;
            padding: 35px;
            text-align: center;
            transition: .3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .dashboard-card:hover {
            transform: translateY(-8px);
            background: rgba(255,255,255,.12);
        }
        .icon-box {
            width: 75px;
            height: 75px;
            margin: 0 auto 20px auto;
            border-radius: 22px;
            display: grid;
            place-items: center;
            font-size: 32px;
        }
        .purple { background: rgba(124,58,237,.18); color: #c4b5fd; }
        .green { background: rgba(34,197,94,.18); color: #86efac; }
        .cyan { background: rgba(34,211,238,.18); color: #67e8f9; }
        .danger { background: rgba(239,68,68,.18); color: #fca5a5; }
        
        .btn-main {
            border-radius: 999px;
            padding: 10px 24px;
            font-weight: 700;
            width: fit-content;
            margin: 15px auto 0 auto;
        }
        .stats-box {
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.15);
            border-radius: 28px;
            padding: 30px;
            text-align: center;
            margin-bottom: 40px;
            backdrop-filter: blur(10px);
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
    <a class="navbar-brand fw-bold d-flex align-items-center" href="dashboard.php">
        <span class="brand-box">
            <i class="fa-solid fa-bolt"></i>
        </span>
        Eventix Organizer
    </a>
    <div>
        <a href="logout.php" class="btn btn-danger btn-sm px-3 rounded-pill">Logout</a>
    </div>
</nav>

<section class="dashboard-section">
    <div class="container">

        <div class="stats-box">
            <h2>Welcome, <?php echo htmlspecialchars($organizer_name); ?> 👋</h2>
            <p class="text-light-50 mt-2">
                Manage your running events, coordinate signed-up volunteers, and check performance metrics.
            </p>
            <h4 class="mt-4 fw-normal">
                Total Events Published: 
                <span class="text-info fw-bold"><?php echo $event_count; ?></span>
            </h4>
        </div>

        <div class="row g-4">

            <div class="col-md-4">
                <div class="dashboard-card">
                    <div>
                        <div class="icon-box purple">
                            <i class="fa-solid fa-calendar-plus"></i>
                        </div>
                        <h4>Create Event</h4>
                        <p class="text-light-50 small">Add technical fests, dynamic cultural schedules, dates, and set cash incentive milestones.</p>
                    </div>
                    <a href="create-event.php" class="btn btn-primary btn-main px-4">Open Module</a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="dashboard-card">
                    <div>
                        <div class="icon-box green">
                            <i class="fa-solid fa-list-check"></i>
                        </div>
                        <h4>My Events</h4>
                        <p class="text-light-50 small">Track operations, inspect compiled data forms, and check live system registration status.</p>
                    </div>
                    <a href="my-events.php" class="btn btn-success btn-main px-4">Manage List</a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="dashboard-card">
                    <div>
                        <div class="icon-box cyan">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <h4>View Volunteers</h4>
                        <p class="text-light-50 small">Cross-reference verified applications, change check-in attendance, and log payout sheets.</p>
                    </div>
                    <a href="volunteers.php" class="btn btn-info btn-main text-dark px-4">View Roster</a>
                </div>
            </div>

            <div class="col-md-6">
                <div class="dashboard-card">
                    <div>
                        <div class="icon-box purple">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <h4>Organizer Profile</h4>
                        <p class="text-light-50 small">Update institutional contact phone numbers, emails, and manage credential parameters.</p>
                    </div>
                    <a href="#" class="btn btn-secondary btn-main px-4">Update Details</a>
                </div>
            </div>

            <div class="col-md-6">
                <div class="dashboard-card">
                    <div>
                        <div class="icon-box danger">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </div>
                        <h4>Secure Logout</h4>
                        <p class="text-light-50 small">Clear local device session tokens securely from your browser cache storage.</p>
                    </div>
                    <a href="logout.php" class="btn btn-danger btn-main px-4">Terminate Session</a>
                </div>
            </div>

        </div>

    </div>
</section>

<footer>
    © 2026 Eventix | Premium College Event Infrastructure Platform
</footer>

</body>
</html>