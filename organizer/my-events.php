<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['organizer_id'])) {
    header("Location: login.php");
    exit();
}

$organizer_id = $_SESSION['organizer_id'];

// Pull all events associated with this organizer sequence matching the indexing rules
$sql = "SELECT * FROM events WHERE organizer_id = ? ORDER BY event_date DESC, id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$result = $stmt->get_result();

function showValue($value) {
    return (!empty($value) || $value === 0 || $value === '0') ? htmlspecialchars($value) : "Not added";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Events | Eventix</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #050816, #15162c, #4f46e5);
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
        .wrapper { padding: 50px 0; }
        .page-title { font-weight: 900; }
        .event-card {
            background: rgba(255,255,255,.96);
            color: #111827;
            border-radius: 28px;
            padding: 28px;
            height: 100%;
            box-shadow: 0 25px 70px rgba(0,0,0,.25);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
        }
        .event-card:hover { transform: translateY(-7px); }
        .event-type {
            display: inline-block; background: rgba(124,58,237,.12); color: #6d28d9;
            padding: 6px 14px; border-radius: 999px; font-size: 13px; font-weight: 800;
        }
        .status-badge { padding: 7px 14px; border-radius: 999px; font-size: 13px; font-weight: 800; }
        .upcoming { background: #dcfce7; color: #166534; }
        .completed { background: #dbeafe; color: #1d4ed8; }
        .cancelled { background: #fee2e2; color: #991b1b; }
        .info-line { margin-top: 12px; color: #4b5563; font-size: 15px; }
        .info-line i { width: 24px; color: #7c3aed; }
        .section-box {
            background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 18px;
            padding: 16px 20px; margin-top: 14px; color: #374151;
            white-space: pre-wrap; overflow-wrap: anywhere; word-break: break-word;
            line-height: 1.6; font-size: 14px; text-align: left;
        }
        .payment-box {
            background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 18px;
            padding: 15px; margin-top: 16px; color: #166534; font-size: 14px;
        }
        .empty-box {
            background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.12);
            border-radius: 30px; padding: 55px; text-align: center;
        }
        .btn-main {
            background: linear-gradient(135deg, #7c3aed, #ec4899); border: none;
            border-radius: 999px; color: white; font-weight: 800;
            padding: 10px 22px; text-decoration: none; display: inline-block; transition: all 0.2s;
        }
        .btn-main:hover { color: white; transform: translateY(-2px); box-shadow: 0 4px 15px rgba(124,58,237,0.3); }
        .card-actions { margin-top: auto; padding-top: 24px; }
        footer { background: rgba(5,8,22,.9); color: #9ca3af; text-align: center; padding: 15px; margin-top: 40px; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark px-4 py-3">
    <a class="navbar-brand fw-bold d-flex align-items-center" href="dashboard.php">
        <span class="brand-box"><i class="fa-solid fa-bolt"></i></span>
        Eventix Organizer
    </a>
    <div>
        <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2 px-3 rounded-pill">Dashboard</a>
        <a href="create-event.php" class="btn btn-light btn-sm me-2 px-3 rounded-pill">Create Event</a>
        <a href="logout.php" class="btn btn-danger btn-sm px-3 rounded-pill">Logout</a>
    </div>
</nav>

<section class="wrapper">
    <div class="container">

        <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
            <div>
                <h1 class="page-title">My Managed Events</h1>
                <p class="text-light-50 mb-0">Monitor cluster nodes, verify capacity parameters, and inspect description listings.</p>
            </div>
            <a href="create-event.php" class="btn-main mt-3 mt-md-0">
                <i class="fa-solid fa-plus me-2"></i> Create New Event
            </a>
        </div>

        <?php if ($result->num_rows > 0) { ?>
            <div class="row g-4">
                <?php while ($event = $result->fetch_assoc()) { 
                    $statusClass = strtolower($event['status'] ?? 'upcoming');
                ?>
                    <div class="col-lg-6">
                        <div class="event-card">

                            <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                <div>
                                    <span class="event-type"><?php echo showValue($event['event_type']); ?></span>
                                    <h3 class="mt-3 mb-1 fw-bold"><?php echo showValue($event['event_name']); ?></h3>
                                </div>
                                <span class="status-badge <?php echo $statusClass; ?>">
                                    <?php echo ucfirst(htmlspecialchars($event['status'] ?? 'upcoming')); ?>
                                </span>
                            </div>

                            <div class="info-line">
                                <i class="fa-solid fa-calendar-days"></i>
                                <strong>Date:</strong> 
                                <?php echo (!empty($event['event_date'])) ? date("d M Y", strtotime($event['event_date'])) : 'Not Configured'; ?>
                            </div>

                            <div class="info-line">
                                <i class="fa-solid fa-clock"></i>
                                <strong>Time Matrix:</strong> <?php echo showValue($event['event_time']); ?>
                            </div>

                            <div class="info-line">
                                <i class="fa-solid fa-location-dot"></i>
                                <strong>Venue:</strong> <?php echo showValue($event['venue']); ?>
                            </div>

                            <div class="info-line">
                                <i class="fa-solid fa-users"></i>
                                <strong>Required Allocation Target:</strong> <?php echo showValue($event['required_volunteers']); ?> Volunteers
                            </div>

                            <div class="payment-box">
                                <strong class="text-success">
                                    <i class="fa-solid fa-money-bill-transfer me-2"></i> Operational Compensation Profile
                                </strong>
                                <div class="mt-2">
                                    <strong>Bounty Payout:</strong> ₹<?php echo showValue($event['volunteer_payment']); ?> Per Assignment
                                </div>
                                <div>
                                    <strong>Clearance Timeline:</strong> <?php echo showValue($event['payment_timeline']); ?>
                                </div>
                            </div>

                            <div class="section-box">
                                <div class="fw-bold text-dark mb-1"><i class="fa-solid fa-file-lines me-2 text-primary"></i>System Logs & Description Context:</div>
                                <div class="text-secondary" style="font-size: 13.5px;">
                                    <?php echo nl2br(htmlspecialchars($event['description'])); ?>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2 card-actions">
                                <a href="volunteers.php?event_id=<?php echo $event['id']; ?>" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold shadow-sm">
                                    <i class="fa-solid fa-users me-1"></i> Roster Log
                                </a>
                                <a href="edit-event.php?id=<?php echo $event['id']; ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold">
                                    <i class="fa-solid fa-pen-to-square me-1"></i> Modify Data
                                </a>
                                <a href="delete-event.php?id=<?php echo $event['id']; ?>" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold" onclick="return confirm('Are you sure you want to completely drop this entry mapping?');">
                                    <i class="fa-solid fa-trash me-1"></i> Erase
                                </a>
                            </div>

                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } else { ?>
            <div class="empty-box shadow">
                <i class="fa-solid fa-calendar-xmark fa-4x mb-4 text-info opacity-75"></i>
                <h3 class="fw-bold">No Operational Profiles Found</h3>
                <p class="text-light-50">Initialize an ecosystem data node. Once initialized, your event manifests will stream here.</p>
                <a href="create-event.php" class="btn-main mt-3">
                    <i class="fa-solid fa-plus me-2"></i> Register First Cluster Manifest
                </a>
            </div>
        <?php } ?>

    </div>
</section>

<footer>
    © 2026 Eventix | Distributed Administration Environment
</footer>

</body>
</html>