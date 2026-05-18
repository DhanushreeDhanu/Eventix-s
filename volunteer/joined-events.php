<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['volunteer_id'])) {
    header("Location: login.php");
    exit();
}

$volunteer_id = $_SESSION['volunteer_id'];

// Volunteer info
$vstmt = $conn->prepare("SELECT full_name FROM volunteers WHERE id=?");
$vstmt->bind_param("i", $volunteer_id);
$vstmt->execute();
$volunteer = $vstmt->get_result()->fetch_assoc();
$volunteer_name = $volunteer['full_name'] ?? 'Volunteer';

// Joined events
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
    WHERE ve.volunteer_id=?
    ORDER BY ve.joined_at DESC
");
$stmt->bind_param("i", $volunteer_id);
$stmt->execute();
$events = $stmt->get_result();

function safe($value) {
    return !empty($value) ? htmlspecialchars($value) : "Not available";
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
            /* Premium Corporate Dark Blue System */
            --bg-main: #0b1329;         
            --bg-card: #1c2541;         
            --border-color: #2e3b5e;    
            
            --primary: #5bc0be;         /* Ice Blue */
            --text-main: #f1f5f9;       
            --text-muted: #94a3b8;      
            
            --success-bg: rgba(46, 213, 115, 0.15);
            --success-text: #2ed573;
            --warning-bg: rgba(ffa502, 0.15);
            --warning-text: #ffa502;
        }

        body {
            background-color: var(--bg-main);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Prevent system overrides from destroying visibility */
        .main-wrapper, h1, h4, strong, .text-light {
            color: var(--text-main) !important;
        }
        p, small, span, .text-muted {
            color: var(--text-muted) !important;
        }

        /* Fixed Top Navigation Alignment */
        .topbar {
            background-color: var(--bg-card);
            padding: 0.75rem 2rem;
            border-bottom: 1px solid var(--border-color);
        }

        .brand-icon {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            background: var(--primary);
            color: var(--bg-main);
            display: inline-grid;
            place-items: center;
            font-size: 0.9rem;
            font-weight: bold;
        }

        /* Strictly Managed Grid Container */
        .main-wrapper {
            flex: 1;
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .page-header-block {
            padding-bottom: 1.25rem;
            margin-bottom: 2rem;
            border-bottom: 1px solid var(--border-color);
        }

        /* Modern Row-Based Event Layout */
        .event-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.25rem;
        }

        /* Left-to-Right Balanced Alignment Flex Header */
        .card-prime-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 1rem;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .event-title {
            font-size: 1.3rem;
            font-weight: 700;
            letter-spacing: -0.01em;
        }

        /* Meta Alignment Row */
        .meta-strip {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 1.25rem;
            margin-top: 0.5rem;
            font-size: 0.85rem;
        }

        .meta-strip span {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .meta-strip i {
            color: var(--primary) !important;
        }

        /* Badges Alignment */
        .status-badge-container {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .status-badge {
            padding: 0.35rem 0.65rem;
            border-radius: 5px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
        }
        .status-badge.pending { background: var(--warning-bg); color: var(--warning-text) !important; }
        .status-badge.approved, .status-badge.paid { background: var(--success-bg); color: var(--success-text) !important; }

        /* Clean Fixed Description Alignment */
        .description-block {
            font-size: 0.9rem;
            line-height: 1.5;
            color: #cbd5e1 !important;
            margin-bottom: 1.25rem;
        }

        /* 3-Column Balanced Specifications Grid */
        .spec-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            border-top: 1px dashed var(--border-color);
            padding-top: 1rem;
        }

        @media (max-width: 768px) {
            .spec-grid {
                grid-template-columns: 1fr;
            }
        }

        .spec-cell {
            font-size: 0.85rem;
        }

        .spec-label {
            color: var(--primary) !important;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            margin-bottom: 0.25rem;
        }

        /* Footer Stamp alignment */
        .card-stamp {
            font-size: 0.75rem;
            margin-top: 1rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        /* Professional Buttons */
        .btn-corporate {
            background: transparent;
            color: var(--text-main) !important;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 0.4rem 1rem;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.15s ease-in-out;
        }
        .btn-corporate:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: var(--primary);
        }

        /* Empty Framework States */
        .empty-box {
            text-align: center;
            padding: 4.5rem 1.5rem;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 10px;
        }
        .empty-box i {
            font-size: 2.5rem;
            opacity: 0.2;
            margin-bottom: 1rem;
        }

        footer {
            background: var(--bg-card);
            border-top: 1px solid var(--border-color);
            color: var(--text-muted) !important;
            text-align: center;
            padding: 1.25rem;
            font-size: 0.85rem;
            margin-top: auto;
        }
    </style>
</head>
<body>

<nav class="topbar d-flex justify-content-between align-items-center">
    <a href="dashboard.php" class="d-flex align-items-center gap-2 text-decoration-none fw-bold text-white fs-5">
        <span class="brand-icon"><i class="fa-solid fa-bolt" style="color: #0b1329 !important;"></i></span>
        Eventix
    </a>

    <div class="d-flex gap-2">
        <a href="dashboard.php" class="btn-corporate">Dashboard</a>
        <a href="available-events.php" class="btn-corporate d-none d-sm-inline-block">Browse Events</a>
        <a href="logout.php" class="btn btn-sm btn-danger px-3 fw-medium" style="border-radius:6px; display:inline-flex; align-items:center;">Logout</a>
    </div>
</nav>

<div class="main-wrapper">

    <header class="page-header-block">
        <h1 class="fw-bold fs-3 mb-1">My Joined Events</h1>
        <p class="mb-0 small">Account: <strong><?php echo safe($volunteer_name); ?></strong> &bull; Systematic overview of your operations.</p>
    </header>

    <?php if ($events->num_rows > 0): ?>

        <?php while ($event = $events->fetch_assoc()): ?>

            <div class="event-card">

                <div class="card-prime-header">
                    <div>
                        <div class="event-title"><?php echo safe($event['event_name']); ?></div>
                        
                        <div class="meta-strip">
                            <span><i class="fa-regular fa-calendar"></i><?php echo date("d M Y", strtotime($event['event_date'])); ?></span>
                            <span><i class="fa-regular fa-clock"></i><?php echo safe($event['event_time']); ?></span>
                            <span><i class="fa-solid fa-location-dot"></i><?php echo safe($event['venue']); ?></span>
                        </div>
                    </div>

                    <div class="status-badge-container">
                        <span class="status-badge <?php echo strtolower($event['attendance_status']); ?>">
                            Attendance: &nbsp;<strong><?php echo ucfirst($event['attendance_status']); ?></strong>
                        </span>
                        <span class="status-badge <?php echo strtolower($event['payment_status']); ?>">
                            Payment: &nbsp;<strong><?php echo ucfirst($event['payment_status']); ?></strong>
                        </span>
                    </div>
                </div>

                <div class="description-block">
                    <?php echo nl2br(safe($event['description'])); ?>
                </div>

                <div class="spec-grid">
                    <div class="spec-cell">
                        <div class="spec-label">Organizer Coordination</div>
                        <strong class="text-white d-block"><?php echo safe($event['contact_person']); ?></strong>
                        <span><?php echo safe($event['contact_phone']); ?></span>
                    </div>

                    <div class="spec-cell">
                        <div class="spec-label">Financial Allocation</div>
                        <strong class="text-white d-block">₹<?php echo safe($event['volunteer_payment']); ?></strong>
                        <span><?php echo safe($event['payment_timeline']); ?></span>
                    </div>

                    <div class="spec-cell">
                        <div class="spec-label">Disbursal Stream</div>
                        <strong class="text-white d-block"><?php echo safe($event['payment_method']); ?></strong>
                        <span>Direct Bank Settlement</span>
                    </div>
                </div>

                <div class="card-stamp text-muted">
                    <i class="fa-regular fa-clock" style="color: var(--text-muted) !important;"></i> 
                    System join logged: <?php echo date("d M Y, h:i A", strtotime($event['joined_at'])); ?>
                </div>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <div class="empty-box">
            <i class="fa-solid fa-calendar-xmark d-block"></i>
            <h4 class="fw-bold mb-2">No operations found</h4>
            <p class="mb-3 small text-muted mx-auto" style="max-width: 350px;">
                Your account workspace is not matched with any active event portfolios currently.
            </p>
            <a href="available-events.php" class="btn btn-sm btn-light fw-bold px-3 py-1.5" style="border-radius:6px;">
                Find Operational Roles
            </a>
        </div>

    <?php endif; ?>

</div>

<footer>
    &copy; 2026 Eventix Infrastructure Framework &bull; All Data Segments Aligned.
</footer>

</body>
</html>