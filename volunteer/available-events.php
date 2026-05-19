<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['volunteer_id'])) {
    header("Location: login.php");
    exit();
}

$volunteer_id = $_SESSION['volunteer_id'];
$volunteer_name = $_SESSION['volunteer_name'];

// Track active, non-expired upcoming opportunities
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

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
            border-bottom: 1px solid rgba(255,255,255,.1);
        }
        .brand-box {
            width: 42px; height: 42px; border-radius: 14px;
            background: linear-gradient(135deg, #7c3aed, #22d3ee);
            display: inline-grid; place-items: center; margin-right: 10px;
        }
        .wrapper { padding: 55px 0; }
        .page-card {
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.15);
            backdrop-filter: blur(18px);
            border-radius: 30px; padding: 32px; margin-bottom: 35px;
            box-shadow: 0 25px 70px rgba(0,0,0,.3);
        }
        .event-card {
            background: rgba(255,255,255,.98); color: #111827;
            border-radius: 28px; padding: 26px; height: 100%;
            box-shadow: 0 25px 65px rgba(0,0,0,.25);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex; flex-direction: column;
        }
        .event-card:hover { transform: translateY(-5px); }
        .event-type {
            display: inline-block; padding: 6px 14px; border-radius: 999px;
            background: rgba(124,58,237,.12); color: #6d28d9; font-weight: 800; font-size: 13px;
        }
        .info-line { margin-top: 10px; color: #4b5563; font-size: 14.5px; }
        .info-line i { width: 24px; color: #7c3aed; }
        .section-box {
            background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 18px;
            padding: 14px; margin-top: 14px; color: #374151; font-size: 14px; line-height: 1.6;
        }
        .payment-box {
            background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 18px;
            padding: 15px; margin-top: 16px; color: #166534; font-size: 14px;
        }
        .card-actions { margin-top: auto; padding-top: 20px; }
        .btn-main {
            background: linear-gradient(135deg, #7c3aed, #ec4899); border: none;
            border-radius: 999px; color: white; font-weight: 800;
            padding: 12px 22px; text-decoration: none; display: inline-block; transition: all 0.2s;
        }
        .btn-main:hover { color: white; transform: translateY(-2px); box-shadow: 0 4px 15px rgba(124,58,237,0.3); }
        .empty-box {
            background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.12);
            border-radius: 30px; padding: 55px; text-align: center;
        }
        footer { background: rgba(5,8,22,.9); color: #9ca3af; text-align: center; padding: 15px; margin-top: 40px; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark px-4 py-3">
    <a class="navbar-brand fw-bold d-flex align-items-center" href="dashboard.php">
        <span class="brand-box"><i class="fa-solid fa-bolt"></i></span> Eventix Volunteer
    </a>
    <div>
        <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2 px-3 rounded-pill">Dashboard</a>
        <a href="joined-events.php" class="btn btn-light btn-sm me-2 px-3 rounded-pill">My Events</a>
        <a href="logout.php" class="btn btn-danger btn-sm px-3 rounded-pill">Logout</a>
    </div>
</nav>

<section class="wrapper">
    <div class="container">

        <div class="page-card">
            <h2 class="fw-bold">Available Assignments Roster 🚀</h2>
            <p class="text-light-50 mb-0">Explore verified organization nodes, inspect compensation metrics, and request placement allocation bindings.</p>
        </div>

        <?php if ($events && $events->num_rows > 0) { ?>
            <div class="row g-4">
                <?php while ($event = $events->fetch_assoc()) { 
                    // Calculate real-time allocation state metrics to prevent over-allocation crashes
                    $count_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM volunteer_events WHERE event_id = ?");
                    $count_stmt->bind_param("i", $event['id']);
                    $count_stmt->execute();
                    $joined_count = $count_stmt->get_result()->fetch_assoc()['total'] ?? 0;
                    
                    $required = intval($event['required_volunteers']);
                    $is_full = ($joined_count >= $required);
                ?>
                    <div class="col-lg-6">
                        <div class="event-card">

                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                                <h4 class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($event['event_name']); ?></h4>
                                <span class="event-type"><?php echo htmlspecialchars($event['event_type'] ?? 'General'); ?></span>
                            </div>

                            <div class="info-line">
                                <i class="fa-solid fa-calendar-days"></i>
                                <strong>Date:</strong> <?php echo (!empty($event['event_date'])) ? date("d M Y", strtotime($event['event_date'])) : 'Not Configured'; ?>
                            </div>
                            <div class="info-line"><i class="fa-solid fa-clock"></i><strong>Schedules Matrix:</strong> <?php echo htmlspecialchars($event['event_time'] ?? 'TBD'); ?></div>
                            <div class="info-line"><i class="fa-solid fa-location-dot"></i><strong>Venue:</strong> <?php echo htmlspecialchars($event['venue']); ?></div>
                            <div class="info-line">
                                <i class="fa-solid fa-users"></i>
                                <strong>Allocation Density:</strong> 
                                <span class="fw-bold <?php echo $is_full ? 'text-danger' : 'text-success'; ?>">
                                    <?php echo $joined_count; ?> / <?php echo $required; ?> Enlisted
                                </span>
                            </div>

                            <div class="section-box">
                                <div class="fw-bold text-dark mb-1"><i class="fa-solid fa-clipboard-list text-primary me-2"></i>System Description Context:</div>
                                <div class="text-secondary" style="font-size: 13.5px;">
                                    <?php echo nl2br(htmlspecialchars($event['description'] ?? 'No operational instructions specified by the coordinator.')); ?>
                                </div>
                            </div>

                            <div class="payment-box">
                                <strong class="text-success"><i class="fa-solid fa-money-bill-wave me-2"></i>Compensation Profile</strong>
                                <div class="mt-2"><strong>Bounty Balance:</strong> ₹<?php echo htmlspecialchars($event['volunteer_payment'] ?? '0'); ?> Per Completed Assignment</div>
                                <div><strong>Clearance Timeline:</strong> <?php echo htmlspecialchars($event['payment_timeline'] ?? 'Upon Event Conclusion'); ?></div>
                            </div>

                            <div class="d-flex flex-wrap gap-2 card-actions w-100">
                                <?php if (!$is_full) { ?>
                                    <a href="join-event.php?event_id=<?php echo $event['id']; ?>" class="btn btn-main w-100 text-center shadow-sm" onclick="return confirm('Confirm requesting placement allocation for this track loop?');">
                                        <i class="fa-solid fa-handshake-angle me-2"></i>Apply for Placement
                                    </a>
                                <?php } else { ?>
                                    <button class="btn btn-secondary w-100 py-2 fw-bold rounded-pill" disabled>
                                        <i class="fa-solid fa-users-slash me-2"></i>Target Capacity Ceiling Met
                                    </button>
                                <?php } ?>

                                <?php if (!empty($event['google_map_link'])) { ?>
                                    <a href="<?php echo htmlspecialchars($event['google_map_link']); ?>" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill mt-2 w-100 text-center fw-bold">
                                        <i class="fa-solid fa-map-location-dot me-1"></i>Inspect Geo-Coordinates
                                    </a>
                                <?php } ?>
                            </div>

                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } else { ?>
            <div class="empty-box shadow">
                <i class="fa-solid fa-calendar-xmark fa-4x mb-4 text-info opacity-75"></i>
                <h3 class="fw-bold">Ecosystem Feed Empty</h3>
                <p class="text-light-50">No operational placement loops match your identity profile right now. Check back later for updated targets.</p>
            </div>
        <?php } ?>

    </div>
</section>

<footer>
    © 2026 Eventix | Secure Distributed Talent Logistics Environment
</footer>

</body>
</html>