<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['volunteer_id'])) {
    header("Location: login.php");
    exit();
}

$volunteer_id = $_SESSION['volunteer_id'];

$stmt = $conn->prepare("SELECT * FROM volunteers WHERE id=?");
$stmt->bind_param("i", $volunteer_id);
$stmt->execute();
$volunteer = $stmt->get_result()->fetch_assoc();

$volunteer_name = $volunteer['full_name'];

function countData($conn, $sql, $id) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['total'];
}

$joined_events = countData($conn, "SELECT COUNT(*) AS total FROM volunteer_events WHERE volunteer_id=?", $volunteer_id);
$approved_attendance = countData($conn, "SELECT COUNT(*) AS total FROM volunteer_events WHERE volunteer_id=? AND attendance_status='approved'", $volunteer_id);
$paid_events = countData($conn, "SELECT COUNT(*) AS total FROM volunteer_events WHERE volunteer_id=? AND payment_status='paid'", $volunteer_id);

$recent_stmt = $conn->prepare("
    SELECT e.event_name, e.event_date, e.event_time, e.venue, ve.attendance_status, ve.payment_status
    FROM volunteer_events ve
    JOIN events e ON ve.event_id = e.id
    WHERE ve.volunteer_id=?
    ORDER BY ve.id DESC
    LIMIT 3
");
$recent_stmt->bind_param("i", $volunteer_id);
$recent_stmt->execute();
$recent_events = $recent_stmt->get_result();

function safe($value) {
    return !empty($value) ? htmlspecialchars($value) : "Not added";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Volunteer Dashboard | Eventix</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
    --bg-main: #0b0f19;         /* Very dark navy */
    --bg-card: #131c2e;         /* Premium dark blue card */
    --bg-card-hover: #1c2842;   /* Distinct hover dark blue */
    --border-color: #243454;    /* Navy border accent */
    
    --primary: #38bdf8;         /* High-contrast Vivid Sky Blue */
    --primary-hover: #0ea5e9;
    --primary-light: rgba(56, 189, 248, 0.12);
    
    --text-main: #f8fafc;       /* Crisp white for main text visibility */
    --text-muted: #94a3b8;      /* Light silver for descriptions */
    
    /* Brightened Status Alerts for Dark Blue BG */
    --success-bg: rgba(52, 211, 153, 0.15);
    --success-text: #34d399;
    --success-border: rgba(52, 211, 153, 0.4);
    
    --warning-bg: rgba(251, 191, 36, 0.15);
    --warning-text: #fbbf24;
    --warning-border: rgba(251, 191, 36, 0.4);
}

/* Base Body Styles overriding dark-text defaults */
body {
    background-color: var(--bg-main);
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    color: var(--text-main);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

/* Force override of default Bootstrap dark colors to support the theme */
.main-wrapper, 
.main-wrapper h1, 
.main-wrapper h2, 
.main-wrapper h3, 
.main-wrapper h4, 
.main-wrapper h5, 
.main-wrapper h6, 
.main-wrapper strong,
.main-wrapper .text-dark {
    color: var(--text-main) !important;
}

.main-wrapper p, 
.main-wrapper small, 
.main-wrapper span, 
.main-wrapper .text-muted {
    color: var(--text-muted) !important;
}

/* Top Navigation */
.topbar {
    background-color: var(--bg-card);
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--border-color);
}

.brand-icon {
    width: 38px;
    height: 38px;
    border-radius: 8px;
    background: var(--primary);
    color: #0b0f19;
    display: inline-grid;
    place-items: center;
    font-size: 1.1rem;
    font-weight: bold;
}

.main-wrapper {
    flex: 1;
    width: 100%;
    max-width: 1240px;
    margin: 0 auto;
    padding: 2rem 1rem;
}

/* Hero Section with distinct dark blue gradient */
.hero-banner {
    background: linear-gradient(135deg, #131c2e 0%, #102244 100%);
    border: 1px solid var(--border-color);
    border-left: 5px solid var(--primary);
    border-radius: 1rem;
    padding: 2rem;
}

/* Main Cards layout */
.dashboard-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 1rem;
    padding: 1.5rem;
    height: 100%;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 0.75rem;
    background: var(--primary-light);
    color: var(--primary) !important;
    display: grid;
    place-items: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}

/* Quick Action grid layout items */
.action-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border-radius: 0.75rem;
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid var(--border-color);
    text-decoration: none;
    transition: all 0.2s ease-in-out;
}

.action-card:hover {
    background: var(--bg-card-hover);
    border-color: var(--primary);
    transform: translateY(-2px);
}

.action-card .stat-icon {
    transition: all 0.2s;
}

.action-card:hover .stat-icon {
    background: var(--primary);
    color: #0b0f19 !important;
}

/* Profile Section Layout modifiers */
.profile-avatar {
    width: 60px;
    height: 60px;
    border-radius: 1rem;
    background: linear-gradient(135deg, var(--primary) 0%, #0284c7 100%);
    color: #0b0f19 !important;
    font-size: 1.5rem;
    font-weight: 700;
    display: grid;
    place-items: center;
}

.profile-details-list border-bottom {
    border-color: var(--border-color) !important;
}

/* Alerts and Banners */
.status-banner {
    border-radius: 0.75rem;
    padding: 1rem;
    font-size: 0.9rem;
}

.status-banner strong, .status-badge strong {
    color: inherit !important;
}

.status-banner p, .status-banner small {
    color: inherit !important;
    opacity: 0.85;
}

.status-banner.missing { 
    background: var(--warning-bg); 
    color: var(--warning-text) !important; 
    border: 1px solid var(--warning-border); 
}

.status-banner.verified { 
    background: var(--success-bg); 
    color: var(--success-text) !important; 
    border: 1px solid var(--success-border); 
}

/* Labels & Status Badges */
.status-badge {
    padding: 0.4rem 0.8rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: capitalize;
    display: inline-block;
    border: 1px solid transparent;
}

.status-badge.pending { 
    background: var(--warning-bg); 
    color: var(--warning-text) !important; 
    border-color: var(--warning-border); 
}

.status-badge.approved, 
.status-badge.paid { 
    background: var(--success-bg); 
    color: var(--success-text) !important; 
    border-color: var(--success-border); 
}

/* Live Event Row Items */
.event-item {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid var(--border-color);
    border-radius: 0.75rem;
    padding: 1rem;
}

.event-item .fa-regular, 
.event-item .fa-solid {
    color: var(--primary) !important;
}

/* Bright contrast interactive structural Buttons */
.btn-primary-custom {
    background: linear-gradient(135deg, var(--primary) 0%, #0ea5e9 100%);
    color: #0b0f19 !important;
    border-radius: 0.5rem;
    padding: 0.6rem 1.2rem;
    font-weight: 700;
    text-decoration: none;
    border: none;
    transition: all 0.2s;
}

.btn-primary-custom:hover {
    background: linear-gradient(135deg, #0ea5e9 0%, var(--primary) 100%);
    opacity: 0.95;
    transform: translateY(-1px);
}

footer {
    background: var(--bg-card);
    border-top: 1px solid var(--border-color);
    color: var(--text-muted) !important;
    text-align: center;
    padding: 1.25rem;
    font-size: 0.9rem;
    margin-top: auto;
}
    </style>
</head>

<body>

<nav class="topbar navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid px-2">
        <a href="dashboard.php" class="navbar-brand d-flex align-items-center fw-bold gap-2 m-0 fs-5 text-white">
            <span class="brand-icon"><i class="fa-solid fa-bolt"></i></span>
            Eventix Volunteer
        </a>
        
        <div class="d-flex gap-2">
            <a href="available-events.php" class="btn btn-outline-light btn-sm rounded px-3 d-none d-sm-inline-block">Browse Events</a>
            <a href="joined-events.php" class="btn btn-light btn-sm rounded px-3 fw-semibold">My Events</a>
            <a href="logout.php" class="btn btn-danger btn-sm rounded px-3">Logout</a>
        </div>
    </div>
</nav>

<div class="main-wrapper">

    <div class="hero-banner d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="fw-bold mb-1 fs-3 text-white">Hello, <?php echo safe($volunteer_name); ?> 👋</h1>
            <p class="text-muted mb-0">Your volunteer dashboard is ready. Join events and track your work here.</p>
        </div>
        <div>
            <a href="available-events.php" class="btn btn-primary-custom d-inline-block w-100 text-center">
                <i class="fa-solid fa-magnifying-glass me-2"></i>Find Events
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-md-4">
            <div class="dashboard-card d-flex align-items-center gap-3">
                <div class="stat-icon"><i class="fa-solid fa-calendar-check"></i></div>
                <div>
                    <h3 class="fw-bold mb-0 lh-1 text-white"><?php echo $joined_events; ?></h3>
                    <small class="text-muted fw-medium">Joined Events</small>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-4">
            <div class="dashboard-card d-flex align-items-center gap-3">
                <div class="stat-icon"><i class="fa-solid fa-user-check"></i></div>
                <div>
                    <h3 class="fw-bold mb-0 lh-1 text-white"><?php echo $approved_attendance; ?></h3>
                    <small class="text-muted fw-medium">Attendance Approved</small>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-md-4">
            <div class="dashboard-card d-flex align-items-center gap-3">
                <div class="stat-icon"><i class="fa-solid fa-wallet"></i></div>
                <div>
                    <h3 class="fw-bold mb-0 lh-1 text-white"><?php echo $paid_events; ?></h3>
                    <small class="text-muted fw-medium">Paid Events</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        
        <div class="col-lg-4">
            <div class="dashboard-card d-flex flex-column gap-3">
                <div class="d-flex align-items-center gap-3 border-bottom border-secondary pb-3">
                    <div class="profile-avatar">
                        <?php echo strtoupper(substr($volunteer_name, 0, 1)); ?>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 fs-5 text-white">My Profile</h4>
                        <span class="text-muted small">Volunteer Account</span>
                    </div>
                </div>

                <div class="profile-details-list d-flex flex-column gap-2.5">
                    <div>
                        <small class="text-muted d-block uppercase fw-semibold" style="font-size:0.75rem;">Email Address</small>
                        <span class="text-break"><?php echo safe($volunteer['email']); ?></span>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted d-block uppercase fw-semibold" style="font-size:0.75rem;">Phone Number</small>
                        <span><?php echo safe($volunteer['phone']); ?></span>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted d-block uppercase fw-semibold" style="font-size:0.75rem;">College / Institution</small>
                        <span><?php echo safe($volunteer['college']); ?></span>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted d-block uppercase fw-semibold" style="font-size:0.75rem;">Skills & Core Talents</small>
                        <span class="text-muted small"><?php echo nl2br(safe($volunteer['skills'])); ?></span>
                    </div>
                </div>

                <div class="mt-2">
                    <?php if (!empty($volunteer['payment_qr'])) { ?>
                        <div class="status-banner verified">
                            <i class="fa-solid fa-circle-check me-2"></i><strong>Payment QR Uploaded</strong>
                            <p class="mb-0 mt-1 small opacity-75">Organizers can disburse your payments smoothly.</p>
                        </div>
                    <?php } else { ?>
                        <div class="status-banner missing">
                            <i class="fa-solid fa-circle-exclamation me-2"></i><strong>Payment QR Missing</strong>
                            <p class="mb-0 mt-1 small opacity-75">Upload your UPI scan code to receive event payouts.</p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="col-lg-8 d-flex flex-column gap-4">

            <div class="dashboard-card">
                <h4 class="fw-bold mb-3 fs-5 text-white">Quick Dashboard Actions</h4>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <a href="available-events.php" class="action-card">
                            <div class="stat-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
                            <div>
                                <strong class="d-block text-white">Browse Events</strong>
                                <small class="text-muted">Find public opportunities</small>
                            </div>
                        </a>
                    </div>

                    <div class="col-sm-6">
                        <a href="joined-events.php" class="action-card">
                            <div class="stat-icon"><i class="fa-solid fa-calendar-days"></i></div>
                            <div>
                                <strong class="d-block text-white">My Joined Events</strong>
                                <small class="text-muted">Check evaluation status</small>
                            </div>
                        </a>
                    </div>

                    <div class="col-sm-6">
                        <a href="upload-payment-qr.php" class="action-card">
                            <div class="stat-icon"><i class="fa-solid fa-qrcode"></i></div>
                            <div>
                                <strong class="d-block text-white">Upload Payment QR</strong>
                                <small class="text-muted">Link direct financial info</small>
                            </div>
                        </a>
                    </div>

                    <div class="col-sm-6">
                        <a href="edit-profile.php" class="action-card">
                            <div class="stat-icon"><i class="fa-solid fa-user-gear"></i></div>
                            <div>
                                <strong class="d-block text-white">Edit Profile</strong>
                                <small class="text-muted">Modify structural details</small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <div class="dashboard-card">
                <h4 class="fw-bold mb-3 fs-5 text-white">Recent Workspace History</h4>

                <?php if ($recent_events->num_rows > 0) { ?>
                    <div class="d-flex flex-column gap-2">
                        <?php while ($event = $recent_events->fetch_assoc()) { ?>
                            <div class="event-item d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                                <div>
                                    <h6 class="fw-bold mb-1 text-white"><?php echo safe($event['event_name']); ?></h6>
                                    <div class="d-flex flex-wrap align-items-center gap-2 text-muted small">
                                        <span><i class="fa-regular fa-calendar me-1"></i><?php echo date("d M Y", strtotime($event['event_date'])); ?></span>
                                        <span class="d-none d-sm-inline opacity-20">|</span>
                                        <span><i class="fa-regular fa-clock me-1"></i><?php echo safe($event['event_time']); ?></span>
                                        <span class="d-none d-sm-inline opacity-20">|</span>
                                        <span><i class="fa-solid fa-location-dot me-1"></i><?php echo safe($event['venue']); ?></span>
                                    </div>
                                </div>

                                <div class="d-flex gap-2 align-items-center flex-wrap">
                                    <span class="status-badge <?php echo strtolower($event['attendance_status']); ?>">
                                        Attendance: <?php echo $event['attendance_status']; ?>
                                    </span>
                                    <span class="status-badge <?php echo strtolower($event['payment_status']); ?>">
                                        Payment: <?php echo $event['payment_status']; ?>
                                    </span>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <div class="text-center py-5">
                        <div class="text-muted mb-3 opacity-30">
                            <i class="fa-solid fa-calendar-xmark fa-3x"></i>
                        </div>
                        <h5 class="fw-semibold mb-1 text-white">No operational histories yet</h5>
                        <p class="text-muted small mb-3">You haven't committed to or registered for upcoming activities.</p>
                        <a href="available-events.php" class="btn-primary-custom d-inline-block small">Browse Active Roles</a>
                    </div>
                <?php } ?>

            </div>
        </div>
    </div>
</div>

<footer>
    &copy; 2026 Eventix Ecosystem | Secure Volunteer Workspace.
</footer>

</body>
</html>