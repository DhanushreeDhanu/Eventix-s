<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['volunteer_id'])) {
    header("Location: login.php");
    exit();
}

$volunteer_id = intval($_SESSION['volunteer_id']);

// Authenticate profile identity mapping metrics securely
$stmt = $conn->prepare("SELECT * FROM volunteers WHERE id = ?");
$stmt->bind_param("i", $volunteer_id);
$stmt->execute();
$volunteer = $stmt->get_result()->fetch_assoc();

// FIXED: Gracefully handle account deletions or drops to prevent fatal crashes on null arrays
if (!$volunteer) {
    session_destroy();
    header("Location: login.php?error=invalid_session");
    exit();
}

// Safely map string components from the record array
$volunteer_name = $volunteer['full_name'] ?? 'Active Volunteer';

function countData($conn, $sql, $id) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return intval($result['total'] ?? 0);
}

// Gather statistical count metrics across relevant rosters
$joined_events = countData($conn, "SELECT COUNT(*) AS total FROM volunteer_events WHERE volunteer_id = ?", $volunteer_id);
$approved_attendance = countData($conn, "SELECT COUNT(*) AS total FROM volunteer_events WHERE volunteer_id = ? AND attendance_status = 'approved'", $volunteer_id);
$paid_events = countData($conn, "SELECT COUNT(*) AS total FROM volunteer_events WHERE volunteer_id = ? AND payment_status = 'paid'", $volunteer_id);

// Query recent assignment records cleanly
$recent_stmt = $conn->prepare("
    SELECT e.event_name, e.event_date, e.event_time, e.venue, ve.attendance_status, ve.payment_status
    FROM volunteer_events ve
    JOIN events e ON ve.event_id = e.id
    WHERE ve.volunteer_id = ?
    ORDER BY ve.id DESC
    LIMIT 3
");
$recent_stmt->bind_param("i", $volunteer_id);
$recent_stmt->execute();
$recent_events = $recent_stmt->get_result();

function safe($value) {
    return (!empty($value) || $value === 0 || $value === '0') ? htmlspecialchars(trim($value)) : "Not added";
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
            --bg-main: #0b0f19;         
            --bg-card: #131c2e;         
            --bg-card-hover: #1c2842;   
            --border-color: #243454;    
            
            --primary: #38bdf8;         
            --primary-hover: #0ea5e9;
            --primary-light: rgba(56, 189, 248, 0.12);
            
            --text-main: #f8fafc;       
            --text-muted: #94a3b8;      
            
            --success-bg: rgba(52, 211, 153, 0.15);
            --success-text: #34d399;
            --success-border: rgba(52, 211, 153, 0.4);
            
            --warning-bg: rgba(251, 191, 36, 0.15);
            --warning-text: #fbbf24;
            --warning-border: rgba(251, 191, 36, 0.4);
        }

        body {
            background-color: var(--bg-main);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .main-wrapper, 
        .main-wrapper h1, .main-wrapper h2, .main-wrapper h3, 
        .main-wrapper h4, .main-wrapper h5, .main-wrapper h6, 
        .main-wrapper strong {
            color: var(--text-main) !important;
        }

        .main-wrapper p, .main-wrapper small, .main-wrapper .text-muted {
            color: var(--text-muted) !important;
        }

        .topbar {
            background-color: var(--bg-card);
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .brand-icon {
            width: 38px; height: 38px; border-radius: 8px;
            background: var(--primary); color: #0b0f19;
            display: inline-grid; place-items: center;
            font-size: 1.1rem; font-weight: bold;
        }

        .main-wrapper {
            flex: 1; width: 100%; max-width: 1240px;
            margin: 0 auto; padding: 2rem 1rem;
        }

        .hero-banner {
            background: linear-gradient(135deg, #131c2e 0%, #102244 100%);
            border: 1px solid var(--border-color);
            border-left: 5px solid var(--primary);
            border-radius: 1rem; padding: 2rem;
        }

        .dashboard-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 1rem; padding: 1.5rem; height: 100%;
        }

        .stat-icon {
            width: 48px; height: 48px; border-radius: 0.75rem;
            background: var(--primary-light); color: var(--primary) !important;
            display: grid; place-items: center; font-size: 1.25rem; flex-shrink: 0;
        }

        .action-card {
            display: flex; align-items: center; gap: 1rem; padding: 1rem;
            border-radius: 0.75rem; background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--border-color); text-decoration: none;
            transition: all 0.2s ease-in-out;
        }

        .action-card:hover {
            background: var(--bg-card-hover); border-color: var(--primary);
            transform: translateY(-2px);
        }

        .action-card .stat-icon { transition: all 0.2s; }
        .action-card:hover .stat-icon { background: var(--primary); color: #0b0f19 !important; }

        .profile-avatar {
            width: 60px; height: 60px; border-radius: 1rem;
            background: linear-gradient(135deg, var(--primary) 0%, #0284c7 100%);
            color: #0b0f19 !important; font-size: 1.5rem; font-weight: 700;
            display: grid; place-items: center;
        }

        /* FIXED: Corrected invalid space selector cascade bug to bind border styling rules accurately */
        .profile-details-list .profile-item {
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 0.75rem; margin-bottom: 0.75rem;
        }
        .profile-details-list .profile-item:last-child { border: none; padding-bottom: 0; margin-bottom: 0; }

        .status-banner { border-radius: 0.75rem; padding: 1rem; font-size: 0.9rem; }
        .status-banner strong { color: inherit !important; }
        .status-banner p { color: inherit !important; opacity: 0.85; }

        .status-banner.missing { background: var(--warning-bg); color: var(--warning-text) !important; border: 1px solid var(--warning-border); }
        .status-banner.verified { background: var(--success-bg); color: var(--success-text) !important; border: 1px solid var(--success-border); }

        .status-badge {
            padding: 0.4rem 0.8rem; border-radius: 6px; font-size: 0.75rem;
            font-weight: 700; text-transform: uppercase; display: inline-block; border: 1px solid transparent;
        }
        .status-badge.pending { background: var(--warning-bg); color: var(--warning-text) !important; border-color: var(--warning-border); }
        .status-badge.approved, .status-badge.paid { background: var(--success-bg); color: var(--success-text) !important; border-color: var(--success-border); }

        .event-item { background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 1rem; }
        .event-item .fa-regular, .event-item .fa-solid { color: var(--primary) !important; }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary) 0%, #0ea5e9 100%);
            color: #0b0f19 !important; border-radius: 0.5rem; padding: 0.6rem 1.2rem;
            font-weight: 700; text-decoration: none; border: none; transition: all 0.2s;
        }
        .btn-primary-custom:hover { background: linear-gradient(135deg, #0ea5e9 0%, var(--primary) 100%); transform: translateY(-1px); }
        footer { background: var(--bg-card); border-top: 1px solid var(--border-color); color: var(--text-muted) !important; text-align: center; padding: 1.25rem; font-size: 0.9rem; margin-top: auto; }
    </style>
</head>
<body>

<nav class="topbar navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid px-2">
        <a href="dashboard.php" class="navbar-brand d-flex align-items-center fw-bold gap-2 m-0 fs-5 text-white">
            <span class="brand-icon"><i class="fa-solid fa-bolt"></i></span> Eventix Workspace
        </a>
        <div class="d-flex gap-2">
            <a href="available-events.php" class="btn btn-outline-light btn-sm rounded px-3 d-none d-sm-inline-block">Browse Shifts</a>
            <a href="joined-events.php" class="btn btn-light btn-sm rounded px-3 fw-semibold">My Schedule</a>
            <a href="logout.php" class="btn btn-danger btn-sm rounded px-3">Logout</a>
        </div>
    </div>
</nav>

<div class="main-wrapper">

    <div class="hero-banner d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="fw-bold mb-1 fs-3 text-white">Welcome Back, <?php echo safe($volunteer_name); ?> 👋</h1>
            <p class="text-muted mb-0">Ecosystem workspace is fully compiled. Request shifts, tracks, and map payout parameters dynamically.</p>
        </div>
        <div>
            <a href="available-events.php" class="btn btn-primary-custom d-inline-block w-100 text-center">
                <i class="fa-solid fa-magnifying-glass me-2"></i>Discover Allocations
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-md-4">
            <div class="dashboard-card d-flex align-items-center gap-3">
                <div class="stat-icon"><i class="fa-solid fa-calendar-check"></i></div>
                <div>
                    <h3 class="fw-bold mb-0 lh-1 text-white"><?php echo $joined_events; ?></h3>
                    <small class="text-muted fw-medium">Enlisted Nodes</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4">
            <div class="dashboard-card d-flex align-items-center gap-3">
                <div class="stat-icon"><i class="fa-solid fa-user-check"></i></div>
                <div>
                    <h3 class="fw-bold mb-0 lh-1 text-white"><?php echo $approved_attendance; ?></h3>
                    <small class="text-muted fw-medium">Attendance Logs</small>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-4">
            <div class="dashboard-card d-flex align-items-center gap-3">
                <div class="stat-icon"><i class="fa-solid fa-wallet"></i></div>
                <div>
                    <h3 class="fw-bold mb-0 lh-1 text-white"><?php echo $paid_events; ?></h3>
                    <small class="text-muted fw-medium">Cleared Balances</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="dashboard-card d-flex flex-column gap-3">
                <div class="d-flex align-items-center gap-3 border-bottom border-secondary pb-3">
                    <div class="profile-avatar"><?php echo strtoupper(substr($volunteer_name, 0, 1)); ?></div>
                    <div>
                        <h4 class="fw-bold mb-0 fs-5 text-white">Identity Profile</h4>
                        <span class="text-muted small">Verified Talent Asset</span>
                    </div>
                </div>

                <div class="profile-details-list">
                    <div class="profile-item">
                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size:0.7rem; letter-spacing: 0.5px;">Communication Endpoint</small>
                        <span class="text-break"><?php echo safe($volunteer['email']); ?></span>
                    </div>
                    <div class="profile-item">
                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size:0.7rem; letter-spacing: 0.5px;">Mobile Token</small>
                        <span><?php echo safe($volunteer['phone']); ?></span>
                    </div>
                    <div class="profile-item">
                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size:0.7rem; letter-spacing: 0.5px;">Institution Origin</small>
                        <span><?php echo safe($volunteer['college'] ?? 'Unspecified Matrix'); ?></span>
                    </div>
                    <div class="profile-item">
                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size:0.7rem; letter-spacing: 0.5px;">Skill Repositories</small>
                        <span class="small text-secondary"><?php echo nl2br(safe($volunteer['skills'] ?? 'General Utility Node')); ?></span>
                    </div>
                </div>

                <div class="mt-2">
                    <?php if (!empty($volunteer['payment_qr'])) { ?>
                        <div class="status-banner verified">
                            <i class="fa-solid fa-circle-check me-2"></i><strong>Payment Gateway Bound</strong>
                            <p class="mb-0 mt-1 small">Administrative modules can process balance settlements seamlessly.</p>
                        </div>
                    <?php } else { ?>
                        <div class="status-banner missing">
                            <i class="fa-solid fa-circle-exclamation me-2"></i><strong>Financial Token Missing</strong>
                            <p class="mb-0 mt-1 small">Please upload a valid UPI layout image to prevent payment processing holds.</p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="col-lg-8 d-flex flex-column gap-4">
            <div class="dashboard-card">
                <h4 class="fw-bold mb-3 fs-5 text-white">Quick Infrastructure Actions</h4>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <a href="available-events.php" class="action-card">
                            <div class="stat-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
                            <div>
                                <strong class="d-block text-white">Find Placements</strong>
                                <small class="text-muted">Inspect public tasks</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6">
                        <a href="joined-events.php" class="action-card">
                            <div class="stat-icon"><i class="fa-solid fa-calendar-days"></i></div>
                            <div>
                                <strong class="d-block text-white">Operational Schedule</strong>
                                <small class="text-muted">Check task allocations</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6">
                        <a href="upload-payment-qr.php" class="action-card">
                            <div class="stat-icon"><i class="fa-solid fa-qrcode"></i></div>
                            <div>
                                <strong class="d-block text-white">Configure Gateway</strong>
                                <small class="text-muted">Link financial assets</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6">
                        <a href="edit-profile.php" class="action-card">
                            <div class="stat-icon"><i class="fa-solid fa-user-gear"></i></div>
                            <div>
                                <strong class="d-block text-white">Manage Settings</strong>
                                <small class="text-muted">Modify baseline files</small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <div class="dashboard-card">
                <h4 class="fw-bold mb-3 fs-5 text-white">Recent Task Execution Metrics</h4>

                <?php if ($recent_events && $recent_events->num_rows > 0) { ?>
                    <div class="d-flex flex-column gap-2">
                        <?php while ($event = $recent_events->fetch_assoc()) { 
                            $attendance = !empty($event['attendance_status']) ? strtolower($event['attendance_status']) : 'pending';
                            $payment = !empty($event['payment_status']) ? strtolower($event['payment_status']) : 'pending';
                        ?>
                            <div class="event-item d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                                <div>
                                    <h6 class="fw-bold mb-1 text-white"><?php echo safe($event['event_name']); ?></h6>
                                    <div class="d-flex flex-wrap align-items-center gap-2 text-muted small">
                                        <span><i class="fa-regular fa-calendar me-1"></i><?php echo date("d M Y", strtotime($event['event_date'])); ?></span>
                                        <span class="opacity-25">|</span>
                                        <span><i class="fa-regular fa-clock me-1"></i><?php echo safe($event['event_time']); ?></span>
                                        <span class="opacity-25">|</span>
                                        <span><i class="fa-solid fa-location-dot me-1"></i><?php echo safe($event['venue']); ?></span>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 align-items-center flex-wrap">
                                    <span class="status-badge <?php echo $attendance; ?>">Log: <?php echo $attendance; ?></span>
                                    <span class="status-badge <?php echo $payment; ?>">Settlement: <?php echo $payment; ?></span>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <div class="text-center py-5 opacity-75">
                        <div class="text-muted mb-2"><i class="fa-solid fa-database fa-2x"></i></div>
                        <h5 class="fw-semibold mb-1 text-white">No Stream Pipelines Found</h5>
                        <p class="text-muted small mb-3">Your allocation record array contains no logged entries.</p>
                        <a href="available-events.php" class="btn-primary-custom d-inline-block small">Browse Active Roles</a>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<footer>
    &copy; 2026 Eventix Ecosystem | Secure Distributed Talent Logistics Environment.
</footer>

</body>
</html>