<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['volunteer_id'])) {
    header("Location: login.php");
    exit();
}

// FIXED: Enforce integer type validation on incoming session data streams
$volunteer_id = intval($_SESSION['volunteer_id']);

// ----------------------------------------------------
// IDENTITY VECTOR RETRIEVAL
// ----------------------------------------------------
$vstmt = $conn->prepare("SELECT full_name FROM volunteers WHERE id = ?");
$vstmt->bind_param("i", $volunteer_id);
$vstmt->execute();
$volunteer = $vstmt->get_result()->fetch_assoc();
$volunteer_name = $volunteer['full_name'] ?? 'Volunteer Resource';
$vstmt->close();

// ----------------------------------------------------
// HISTORICAL ASSIGNMENT MATRIX RETRIEVAL
// ----------------------------------------------------
$stmt = $conn->prepare("
    SELECT 
        ve.id,
        ve.joined_at,
        ve.attendance_status,
        ve.payment_status,
        e.event_name,
        e.event_type,
        e.event_date,
        e.event_time,
        e.venue,
        e.description,
        e.contact_person,
        e.contact_phone,
        e.volunteer_payment,
        e.payment_timeline,
        e.payment_method
    FROM volunteer_events ve
    JOIN events e ON ve.event_id = e.id
    WHERE ve.volunteer_id = ?
    ORDER BY ve.joined_at DESC
");
$stmt->bind_param("i", $volunteer_id);
$stmt->execute();
$events = $stmt->get_result();

// FIXED: Corrected the evaluation logic so that numeric '0' metrics render properly instead of being marked missing
function safe($value) {
    return (!empty($value) || $value === 0 || $value === '0') ? htmlspecialchars(trim($value)) : "Not declared";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Joined Events | Eventix</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --bg-main: #0b1329;         
            --bg-card: #1c2541;         
            --border-color: #2e3b5e;    
            --primary: #5bc0be;         
            --text-main: #f1f5f9;       
            --text-muted: #94a3b8;      
            
            --success-bg: rgba(46, 213, 115, 0.12);
            --success-text: #2ed573;
            --warning-bg: rgba(255, 165, 2, 0.12);
            --warning-text: #ffa502;
            --danger-bg: rgba(255, 71, 87, 0.12);
            --danger-text: #ff4757;
        }

        body {
            background-color: var(--bg-main);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .main-wrapper, h1, h4, strong, .text-light { color: var(--text-main) !important; }
        p, small, span, .text-muted { color: var(--text-muted) !important; }

        .topbar {
            background-color: var(--bg-card);
            padding: 0.75rem 2rem;
            border-bottom: 1px solid var(--border-color);
        }

        .brand-icon {
            width: 32px; height: 32px; border-radius: 6px;
            background: var(--primary); color: var(--bg-main);
            display: inline-grid; place-items: center;
            font-size: 0.9rem; font-weight: bold;
        }

        .main-wrapper {
            flex: 1; width: 100%; max-width: 1100px;
            margin: 0 auto; padding: 2rem 1rem;
        }

        .page-header-block {
            padding-bottom: 1.25rem; margin-bottom: 2rem;
            border-bottom: 1px solid var(--border-color);
        }

        .event-card {
            background: var(--bg-card); border: 1px solid var(--border-color);
            border-radius: 10px; padding: 1.5rem; margin-bottom: 1.25rem;
            transition: transform 0.15s ease-in-out;
        }
        .event-card:hover { transform: translateY(-2px); }

        .card-prime-header {
            display: flex; justify-content: space-between;
            align-items: flex-start; flex-wrap: wrap; gap: 1rem;
            padding-bottom: 1rem; margin-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .event-title { font-size: 1.3rem; font-weight: 700; letter-spacing: -0.01em; }

        .meta-strip { display: flex; flex-wrap: wrap; align-items: center; gap: 1.25rem; margin-top: 0.5rem; font-size: 0.85rem; }
        .meta-strip span { display: inline-flex; align-items: center; gap: 0.4rem; }
        .meta-strip i { color: var(--primary) !important; }

        .status-badge-container { display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap; }

        .status-badge {
            padding: 0.4rem 0.75rem; border-radius: 6px;
            font-size: 0.75rem; font-weight: 600; text-transform: uppercase;
            letter-spacing: 0.3px; display: inline-flex; align-items: center;
        }
        
        /* Contextual styles for tracking state nodes */
        .status-badge.pending { background: var(--warning-bg); color: var(--warning-text) !important; }
        .status-badge.approved, .status-badge.paid { background: var(--success-bg); color: var(--success-text) !important; }
        .status-badge.rejected, .status-badge.cancelled { background: var(--danger-bg); color: var(--danger-text) !important; }

        .description-block { font-size: 0.9rem; line-height: 1.6; color: #cbd5e1 !important; margin-bottom: 1.25rem; }

        .spec-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;
            border-top: 1px dashed var(--border-color); padding-top: 1rem;
        }

        @media (max-width: 768px) { .spec-grid { grid-template-columns: 1fr; } }

        .spec-cell { font-size: 0.85rem; }
        .spec-label {
            color: var(--primary) !important; font-size: 0.75rem;
            font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.05em; margin-bottom: 0.25rem;
        }

        .card-stamp { font-size: 0.75rem; margin-top: 1.25rem; display: flex; align-items: center; gap: 0.4rem; }

        .btn-corporate {
            background: transparent; color: var(--text-main) !important;
            border: 1px solid var(--border-color); border-radius: 6px;
            padding: 0.4rem 1rem; font-size: 0.85rem; font-weight: 600;
            text-decoration: none; transition: all 0.15s ease-in-out;
        }
        .btn-corporate:hover { background: rgba(255, 255, 255, 0.05); border-color: var(--primary); }

        .empty-box {
            text-align: center; padding: 5rem 1.5rem;
            background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 10px;
        }
        .empty-box i { font-size: 3rem; opacity: 0.15; margin-bottom: 1.25rem; color: var(--primary); }

        footer {
            background: var(--bg-card); border-top: 1px solid var(--border-color);
            color: var(--text-muted) !important; text-align: center;
            padding: 1.25rem; font-size: 0.85rem; margin-top: auto;
        }
    </style>
</head>
<body>

<nav class="topbar d-flex justify-content-between align-items-center">
    <a href="dashboard.php" class="d-flex align-items-center gap-2 text-decoration-none fw-bold text-white fs-5">
        <span class="brand-icon"><i class="fa-solid fa-bolt" style="color: #0b1329 !important;"></i></span> Eventix
    </a>
    <div class="d-flex gap-2">
        <a href="dashboard.php" class="btn-corporate">Dashboard</a>
        <a href="available-events.php" class="btn-corporate d-none d-sm-inline-block">Browse Shifts</a>
        <a href="logout.php" class="btn btn-sm btn-danger px-3 fw-medium" style="border-radius:6px; display:inline-flex; align-items:center;">Logout</a>
    </div>
</nav>

<div class="main-wrapper">

    <header class="page-header-block">
        <h1 class="fw-bold fs-3 mb-1">My Joined Events</h1>
        <p class="mb-0 small">Account Index: <strong class="text-white"><?php echo safe($volunteer_name); ?></strong> &bull; Operational history log records.</p>
    </header>

    <?php if ($events && $events->num_rows > 0): ?>
        <?php while ($event = $events->fetch_assoc()): ?>
            <div class="event-card shadow-sm">

                <div class="card-prime-header">
                    <div>
                        <div class="event-title text-white"><?php echo safe($event['event_name']); ?></div>
                        <div class="meta-strip">
                            <span><i class="fa-regular fa-calendar"></i><?php echo date("d M Y", strtotime($event['event_date'])); ?></span>
                            <span><i class="fa-regular fa-clock"></i><?php echo safe($event['event_time']); ?></span>
                            <span><i class="fa-solid fa-location-dot"></i><?php echo safe($event['venue']); ?></span>
                        </div>
                    </div>

                    <div class="status-badge-container">
                        <span class="status-badge <?php echo strtolower($event['attendance_status']); ?>">
                            Duty: &nbsp;<strong><?php echo safe($event['attendance_status']); ?></strong>
                        </span>
                        <span class="status-badge <?php echo strtolower($event['payment_status']); ?>">
                            Balance: &nbsp;<strong><?php echo safe($event['payment_status']); ?></strong>
                        </span>
                    </div>
                </div>

                <div class="description-block">
                    <?php echo nl2br(safe($event['description'])); ?>
                </div>

                <div class="spec-grid">
                    <div class="spec-cell">
                        <div class="spec-label">Supervisor Core</div>
                        <strong class="text-white d-block"><?php echo safe($event['contact_person']); ?></strong>
                        <span class="text-muted small"><?php echo safe($event['contact_phone']); ?></span>
                    </div>

                    <div class="spec-cell">
                        <div class="spec-label">Compensation Unit</div>
                        <strong class="text-white d-block">₹<?php echo safe($event['volunteer_payment']); ?></strong>
                        <span class="text-muted small"><?php echo safe($event['payment_timeline']); ?></span>
                    </div>

                    <div class="spec-cell">
                        <div class="spec-label">Settlement Pipeline</div>
                        <strong class="text-white d-block"><?php echo safe($event['payment_method']); ?></strong>
                        <span class="text-muted small">System Cleared Entry</span>
                    </div>
                </div>

                <div class="card-stamp text-muted">
                    <i class="fa-regular fa-clock"></i> 
                    Roster allocation entry timestamp: <?php echo date("d M Y, h:i A", strtotime($event['joined_at'])); ?>
                </div>

            </div>
        <?php endwhile; ?>
        <?php $stmt->close(); ?>
    <?php else: ?>
        <div class="empty-box shadow-sm">
            <i class="fa-solid fa-calendar-xmark d-block"></i>
            <h4 class="fw-bold mb-2">No Operations Found</h4>
            <p class="mb-4 small text-muted mx-auto" style="max-width: 380px;">
                Your account vector is currently unlinked from active historical operations.
            </p>
            <a href="available-events.php" class="btn btn-sm btn-light fw-bold px-4 py-2" style="border-radius:6px;">
                Acquire Assignment Shifts
            </a>
        </div>
    <?php endif; ?>

</div>

<footer>
    &copy; 2026 Eventix Infrastructure Framework &bull; Distributed Event Execution Environment.
</footer>

</body>
</html>