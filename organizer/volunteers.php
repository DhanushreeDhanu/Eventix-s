<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['organizer_id'])) {
    header("Location: login.php");
    exit();
}

$organizer_id = (int)$_SESSION['organizer_id'];

$events_sql = "
    SELECT *
    FROM events
    WHERE organizer_id = ?
    ORDER BY event_date ASC
";

$stmt = $conn->prepare($events_sql);
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$events = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Joined Volunteers</title>
    <style>
        /* Modern Premium Dark Theme Variables */
        :root {
            --bg-main: #0b0f19;
            --bg-card: #131a26;
            --bg-nested: #1c2533;
            --border-color: #243145;
            
            --text-title: #ffffff;
            --text-body: #94a3b8;
            --text-muted: #64748b;
            
            --accent-primary: #6366f1;
            --accent-hover: #4f46e5;
            
            /* Status Accents */
            --status-joined: #10b981;
            --status-joined-bg: rgba(16, 185, 129, 0.15);
            --status-pending: #f59e0b;
            --status-pending-bg: rgba(245, 158, 11, 0.15);
            --status-paid: #3b82f6;
            --status-paid-bg: rgba(59, 130, 246, 0.15);
        }

        body {
            margin: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--bg-main);
            color: var(--text-body);
            padding: 40px 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        /* Top Bar Navigation */
        .top-navigation {
            margin-bottom: 24px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            background: var(--bg-card);
            color: var(--text-title);
            text-decoration: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            border: 1px solid var(--border-color);
            transition: all 0.2s ease;
        }

        .back-btn:hover {
            background: var(--bg-nested);
            border-color: var(--accent-primary);
        }

        /* Header Layout */
        .header {
            margin-bottom: 40px;
        }

        .header h1 {
            color: var(--text-title);
            font-size: 36px;
            font-weight: 800;
            margin: 0 0 8px 0;
            letter-spacing: -1px;
        }

        .header p {
            margin: 0;
            color: var(--text-muted);
            font-size: 16px;
        }

        /* Main Event Card */
        .event-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 32px;
            margin-bottom: 32px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .event-title {
            color: var(--text-title);
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 16px 0;
        }

        /* Aligned Meta Items */
        .event-meta {
            display: flex;
            gap: 24px;
            margin-bottom: 32px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 500;
        }

        .vol-section-title {
            color: var(--text-title);
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 16px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Volunteer Card Layout */
        .volunteer-card {
            background: var(--bg-nested);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 24px;
            margin-top: 16px;
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
        }

        @media (min-width: 768px) {
            .volunteer-card {
                grid-template-columns: 1.5fr 1fr;
                align-items: start;
            }
        }

        .vol-info h4 {
            color: var(--text-title);
            font-size: 18px;
            font-weight: 600;
            margin: 0 0 12px 0;
        }

        .vol-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 8px;
        }

        .vol-grid p {
            margin: 0;
            font-size: 14px;
        }

        .vol-grid strong {
            color: var(--text-muted);
            font-weight: 400;
            margin-right: 4px;
        }

        /* Refined Status Pill Badges */
        .badge-group {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 14px;
        }

        .badge {
            font-size: 12px;
            font-weight: 600;
            padding: 6px 14px;
            border-radius: 30px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
        }

        .badge.joined, .badge.approved, .badge.marked {
            background: var(--status-joined-bg);
            color: var(--status-joined);
        }

        .badge.pending {
            background: var(--status-pending-bg);
            color: var(--status-pending);
        }

        .badge.paid {
            background: var(--status-paid-bg);
            color: var(--status-paid);
        }

        /* Action UI Buttons Row */
        .vol-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            justify-content: flex-end;
            height: 100%;
        }

        @media (min-width: 768px) {
            .vol-actions {
                align-items: flex-end;
            }
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            width: 100%;
            max-width: 200px;
            text-align: center;
        }

        .btn-green {
            background: var(--status-joined);
            color: #000;
        }

        .btn-green:hover {
            background: #059669;
            transform: translateY(-1px);
        }

        .btn-orange {
            background: transparent;
            border: 1px solid var(--status-pending);
            color: var(--status-pending);
        }

        .btn-orange:hover {
            background: var(--status-pending-bg);
            transform: translateY(-1px);
        }

        /* Clean Empty Feedback State */
        .empty {
            background: rgba(245, 158, 11, 0.05);
            border: 1px dashed var(--status-pending);
            color: var(--status-pending);
            padding: 24px;
            border-radius: 14px;
            text-align: center;
            font-size: 14px;
            font-weight: 500;
        }
    </style>
</head>
<body>

<div class="container">

    <div class="top-navigation">
        <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
    </div>

    <div class="header">
        <h1>My Events 🎉</h1>
        <p>Manage and monitor your project volunteers updates seamlessly.</p>
    </div>

    <?php if ($events->num_rows > 0): ?>
        <?php while ($event = $events->fetch_assoc()): ?>

            <?php
            $event_id = (int)$event['id'];

            $vol_sql = "
                SELECT 
                    ve.id AS registration_id,
                    ve.status AS join_status,
                    ve.attendance_status,
                    ve.payment_status,
                    ve.joined_at,
                    v.full_name AS volunteer_name,
                    v.email AS volunteer_email,
                    v.phone AS volunteer_mobile
                FROM volunteer_events ve
                JOIN volunteers v ON ve.volunteer_id = v.id
                WHERE ve.event_id = ?
                AND ve.status IN ('joined','approved')
                ORDER BY ve.joined_at DESC
            ";

            $vol_stmt = $conn->prepare($vol_sql);
            $vol_stmt->bind_param("i", $event_id);
            $vol_stmt->execute();
            $volunteers = $vol_stmt->get_result();

            $joined_count = $volunteers->num_rows;
            ?>

            <div class="event-card">
                <h2 class="event-title"><?php echo htmlspecialchars($event['event_name']); ?></h2>

                <div class="event-meta">
                    <div class="meta-item"><span>📅</span> <?php echo htmlspecialchars($event['event_date']); ?></div>
                    <div class="meta-item"><span>📍</span> <?php echo htmlspecialchars($event['venue']); ?></div>
                </div>

                <div class="vol-box">
                    <h3 class="vol-section-title">Volunteers Registered (<?php echo $joined_count; ?>)</h3>

                    <?php if ($joined_count > 0): ?>
                        <?php while ($vol = $volunteers->fetch_assoc()): ?>

                            <div class="volunteer-card">
                                <div class="vol-info">
                                    <h4><?php echo htmlspecialchars($vol['volunteer_name']); ?></h4>
                                    
                                    <div class="vol-grid">
                                        <p><strong>Email:</strong> <?php echo htmlspecialchars($vol['volunteer_email']); ?></p>
                                        <p><strong>Mobile:</strong> <?php echo htmlspecialchars($vol['volunteer_mobile']); ?></p>
                                        <p><strong>Joined:</strong> <?php echo htmlspecialchars($vol['joined_at']); ?></p>
                                    </div>

                                    <div class="badge-group">
                                        <span class="badge joined">
                                            <?php echo htmlspecialchars($vol['join_status']); ?>
                                        </span>
                                        <span class="badge <?php echo htmlspecialchars($vol['attendance_status']); ?>">
                                            Attendance: <?php echo htmlspecialchars($vol['attendance_status']); ?>
                                        </span>
                                        <span class="badge <?php echo htmlspecialchars($vol['payment_status']); ?>">
                                            Payment: <?php echo htmlspecialchars($vol['payment_status']); ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="vol-actions">
                                    <?php if ($vol['attendance_status'] != 'marked'): ?>
                                        <a class="btn btn-green"
                                           href="update-attendance.php?id=<?php echo $vol['registration_id']; ?>">
                                            Mark Attendance
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($vol['payment_status'] != 'paid'): ?>
                                        <a class="btn btn-orange"
                                           href="update-payment.php?id=<?php echo $vol['registration_id']; ?>">
                                            Mark Paid
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>

                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty">No volunteers joined yet.</div>
                    <?php endif; ?>
                </div>
            </div>

        <?php endwhile; ?>
    <?php else: ?>
        <div class="event-card" style="text-align: center;">
            <h2 style="margin: 0; color: var(--text-muted);">No events created yet.</h2>
        </div>
    <?php endif; ?>

</div>

</body>
</html>