<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['organizer_id'])) {
    header("Location: login.php");
    exit();
}

$organizer_id = $_SESSION['organizer_id'];

if (!isset($_GET['event_id'])) {
    header("Location: my-events.php");
    exit();
}

$event_id = intval($_GET['event_id']);

// Authenticate and verify that the requested event asset belongs explicitly to this active organizer
$event_stmt = $conn->prepare("SELECT * FROM events WHERE id = ? AND organizer_id = ?");
$event_stmt->bind_param("ii", $event_id, $organizer_id);
$event_stmt->execute();
$event = $event_stmt->get_result()->fetch_assoc();

if (!$event) {
    header("Location: my-events.php");
    exit();
}

// Stream mapped volunteer entities associated with the targeted cluster node
$sql = "SELECT 
            ve.id AS join_id,
            ve.joined_at,
            ve.payment_status,
            ve.volunteer_qr,
            u.name,
            u.email,
            u.phone
        FROM volunteer_events ve
        JOIN users u ON ve.volunteer_id = u.id
        WHERE ve.event_id = ?
        ORDER BY ve.joined_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

function safe($value) {
    return (!empty($value) || $value === 0 || $value === '0') ? htmlspecialchars($value) : "Not added";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Volunteers | Eventix</title>
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
        .wrapper { padding: 50px 0; }
        .hero-card {
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.15);
            backdrop-filter: blur(18px);
            border-radius: 30px; padding: 32px; margin-bottom: 35px;
            box-shadow: 0 25px 70px rgba(0,0,0,.3);
        }
        .page-title { font-weight: 900; }
        .event-pill {
            display: inline-block; padding: 8px 16px; border-radius: 999px;
            background: rgba(34,211,238,.16); color: #67e8f9; font-weight: 800; margin-bottom: 12px;
        }
        .vol-card {
            background: rgba(255,255,255,.98); color: #111827;
            border-radius: 28px; padding: 26px; height: 100%;
            box-shadow: 0 25px 65px rgba(0,0,0,.25);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex; flex-direction: column;
        }
        .vol-card:hover { transform: translateY(-5px); }
        .avatar {
            width: 54px; height: 54px; border-radius: 16px;
            background: linear-gradient(135deg, #7c3aed, #22d3ee); color: white;
            display: grid; place-items: center; font-size: 22px; font-weight: 900;
        }
        .info-line { margin-top: 10px; color: #4b5563; font-size: 14.5px; }
        .info-line i { width: 24px; color: #7c3aed; }
        .payment-badge { padding: 6px 14px; border-radius: 999px; font-size: 12.5px; font-weight: 800; }
        .paid { background: #dcfce7; color: #166534; }
        .pending { background: #fef3c7; color: #92400e; }
        .qr-box {
            margin-top: 16px; background: #f8fafc; border: 1px solid #e2e8f0;
            border-radius: 20px; padding: 14px; text-align: center;
        }
        .qr-img { max-width: 130px; max-height: 130px; border-radius: 12px; border: 1px solid #e2e8f0; object-fit: cover; }
        .card-actions { margin-top: auto; padding-top: 20px; }
        .empty-box {
            background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.12);
            border-radius: 30px; padding: 55px; text-align: center;
        }
        .btn-main {
            background: linear-gradient(135deg, #7c3aed, #ec4899); border: none;
            border-radius: 999px; color: white; font-weight: 800;
            padding: 10px 22px; text-decoration: none; display: inline-block; transition: all 0.2s;
        }
        .btn-main:hover { color: white; transform: translateY(-2px); box-shadow: 0 4px 15px rgba(124,58,237,0.3); }
        footer { background: rgba(5,8,22,.9); color: #9ca3af; text-align: center; padding: 15px; margin-top: 40px; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark px-4 py-3">
    <a class="navbar-brand fw-bold d-flex align-items-center" href="dashboard.php">
        <span class="brand-box"><i class="fa-solid fa-bolt"></i></span> Eventix Organizer
    </a>
    <div>
        <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2 px-3 rounded-pill">Dashboard</a>
        <a href="my-events.php" class="btn btn-light btn-sm me-2 px-3 rounded-pill">My Events</a>
        <a href="logout.php" class="btn btn-danger btn-sm px-3 rounded-pill">Logout</a>
    </div>
</nav>

<section class="wrapper">
    <div class="container">

        <div class="hero-card">
            <span class="event-pill"><i class="fa-solid fa-users-viewfinder me-2"></i>Deployment Registration Profile</span>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1 class="page-title mb-1"><?php echo safe($event['event_name']); ?></h1>
                    <p class="text-light-50 mb-0">Audit verified volunteer configurations, inspect identity parameters, and verify digital payment statuses.</p>
                </div>
                <a href="my-events.php" class="btn-main"><i class="fa-solid fa-arrow-left me-2"></i>Return to Matrix</a>
            </div>
        </div>

        <?php if ($result->num_rows > 0) { ?>
            <div class="row g-4">
                <?php while ($vol = $result->fetch_assoc()) { 
                    $status = !empty($vol['payment_status']) ? strtolower($vol['payment_status']) : "pending";
                ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="vol-card">

                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div class="d-flex gap-2 align-items-center">
                                    <div class="avatar"><?php echo strtoupper(substr($vol['name'], 0, 1)); ?></div>
                                    <div>
                                        <h5 class="mb-0 fw-bold text-dark"><?php echo safe($vol['name']); ?></h5>
                                        <small class="text-muted" style="font-size: 11px;">Node Map ID: #<?php echo intval($vol['join_id']); ?></small>
                                    </div>
                                </div>
                                <span class="payment-badge <?php echo ($status === 'paid') ? 'paid' : 'pending'; ?>">
                                    <i class="fa-solid <?php echo ($status === 'paid') ? 'fa-circle-check' : 'fa-circle-dot'; ?> me-1"></i><?php echo ucfirst($status); ?>
                                </span>
                            </div>

                            <div class="info-line"><i class="fa-solid fa-envelope"></i><?php echo safe($vol['email']); ?></div>
                            <div class="info-line"><i class="fa-solid fa-phone"></i><?php echo safe($vol['phone']); ?></div>
                            <div class="info-line">
                                <i class="fa-solid fa-clock"></i><span class="text-secondary" style="font-size: 13.5px;">Registered: <?php echo date("d M Y, h:i A", strtotime($vol['joined_at'])); ?></span>
                            </div>
                            <div class="info-line">
                                <i class="fa-solid fa-indian-rupee-sign"></i><strong>Bounty Balance:</strong> ₹<?php echo isset($event['volunteer_payment']) ? safe($event['volunteer_payment']) : "0"; ?>
                            </div>

                            <div class="qr-box">
                                <div class="fw-bold text-dark mb-2" style="font-size: 13px;"><i class="fa-solid fa-qrcode me-1 text-primary"></i>User-Submitted Payment Token</div>
                                <div>
                                    <?php if (!empty($vol['volunteer_qr'])) { ?>
                                        <img src="../uploads/volunteer_qr/<?php echo htmlspecialchars($vol['volunteer_qr']); ?>" class="qr-img" alt="Token Map Image">
                                    <?php } else { ?>
                                        <span class="text-muted d-block py-3" style="font-size: 12.5px;">No identity QR uploaded by candidate.</span>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="d-flex gap-2 card-actions">
                                <?php if ($status !== 'paid') { ?>
                                    <a href="mark-paid.php?id=<?php echo $vol['join_id']; ?>&event_id=<?php echo $event_id; ?>" 
                                       class="btn btn-success btn-sm rounded-pill px-3 fw-bold shadow-sm" 
                                       onclick="return confirm('Confirm clearing outstanding balance records for this volunteer token?');">
                                        <i class="fa-solid fa-check-double me-1"></i> Confirm Payment
                                    </a>
                                <?php } else { ?>
                                    <button class="btn btn-secondary btn-sm rounded-pill px-3 fw-bold" disabled>
                                        <i class="fa-solid fa-ban me-1"></i> Balance Cleared
                                    </button>
                                <?php } ?>

                                <a href="mailto:<?php echo htmlspecialchars($vol['email']); ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold">
                                    <i class="fa-solid fa-paper-plane me-1"></i> Contact
                                </a>
                            </div>

                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } else { ?>
            <div class="empty-box">
                <i class="fa-solid fa-user-slash fa-4x mb-4 text-info opacity-75"></i>
                <h3 class="fw-bold">No Active Registrations Found</h3>
                <p class="text-light-50">No volunteer nodes have bound themselves to this cluster event. Registered candidates will stream here dynamically.</p>
                <a href="my-events.php" class="btn-main mt-2"><i class="fa-solid fa-circle-arrow-left me-2"></i>Check Alternative Datasets</a>
            </div>
        <?php } ?>

    </div>
</section>

<footer>
    © 2026 Eventix | Secure Administration Logistics Node
</footer>

</body>
</html>