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

$event_stmt = $conn->prepare("SELECT * FROM events WHERE id=? AND organizer_id=?");
$event_stmt->bind_param("ii", $event_id, $organizer_id);
$event_stmt->execute();
$event = $event_stmt->get_result()->fetch_assoc();

if (!$event) {
    header("Location: my-events.php");
    exit();
}

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
    return !empty($value) ? htmlspecialchars($value) : "Not added";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Volunteers | Eventix</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

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

        .wrapper {
            padding: 50px 0;
        }

        .hero-card {
            background: rgba(255,255,255,.1);
            border: 1px solid rgba(255,255,255,.18);
            backdrop-filter: blur(18px);
            border-radius: 30px;
            padding: 32px;
            margin-bottom: 35px;
            box-shadow: 0 25px 70px rgba(0,0,0,.25);
        }

        .page-title {
            font-weight: 900;
        }

        .event-pill {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 999px;
            background: rgba(34,211,238,.16);
            color: #67e8f9;
            font-weight: 800;
            margin-bottom: 12px;
        }

        .vol-card {
            background: rgba(255,255,255,.97);
            color: #111827;
            border-radius: 28px;
            padding: 26px;
            height: 100%;
            box-shadow: 0 25px 65px rgba(0,0,0,.24);
            transition: .3s;
        }

        .vol-card:hover {
            transform: translateY(-7px);
        }

        .avatar {
            width: 62px;
            height: 62px;
            border-radius: 20px;
            background: linear-gradient(135deg, #7c3aed, #22d3ee);
            color: white;
            display: grid;
            place-items: center;
            font-size: 25px;
            font-weight: 900;
        }

        .info-line {
            margin-top: 12px;
            color: #4b5563;
        }

        .info-line i {
            width: 24px;
            color: #7c3aed;
        }

        .payment-badge {
            padding: 7px 14px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 800;
        }

        .paid {
            background: #dcfce7;
            color: #166534;
        }

        .pending {
            background: #fef3c7;
            color: #92400e;
        }

        .qr-box {
            margin-top: 16px;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 20px;
            padding: 16px;
            text-align: center;
        }

        .qr-img {
            max-width: 150px;
            max-height: 150px;
            border-radius: 15px;
            border: 1px solid #e5e7eb;
            object-fit: cover;
        }

        .empty-box {
            background: rgba(255,255,255,.1);
            border: 1px solid rgba(255,255,255,.18);
            border-radius: 30px;
            padding: 55px;
            text-align: center;
        }

        .btn-main {
            background: linear-gradient(135deg, #7c3aed, #ec4899);
            border: none;
            border-radius: 999px;
            color: white;
            font-weight: 800;
            padding: 10px 22px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-main:hover {
            color: white;
            transform: translateY(-2px);
        }

        footer {
            background: rgba(5,8,22,.9);
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
        <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">Dashboard</a>
        <a href="my-events.php" class="btn btn-light btn-sm me-2">My Events</a>
        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
</nav>

<section class="wrapper">
    <div class="container">

        <div class="hero-card">
            <span class="event-pill">
                <i class="fa-solid fa-users me-2"></i>
                Joined Volunteers
            </span>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1 class="page-title mb-2">
                        <?php echo safe($event['event_name']); ?>
                    </h1>
                    <p class="text-light mb-0">
                        View volunteers registered for this event and manage payment details.
                    </p>
                </div>

                <a href="my-events.php" class="btn-main">
                    <i class="fa-solid fa-arrow-left me-2"></i>
                    Back to Events
                </a>
            </div>
        </div>

        <?php if ($result->num_rows > 0) { ?>

            <div class="row g-4">
                <?php while ($vol = $result->fetch_assoc()) { 
                    $status = !empty($vol['payment_status']) ? strtolower($vol['payment_status']) : "pending";
                ?>

                    <div class="col-lg-4 col-md-6">
                        <div class="vol-card">

                            <div class="d-flex justify-content-between align-items-start">
                                <div class="d-flex gap-3 align-items-center">
                                    <div class="avatar">
                                        <?php echo strtoupper(substr($vol['name'], 0, 1)); ?>
                                    </div>

                                    <div>
                                        <h4 class="mb-1">
                                            <?php echo safe($vol['name']); ?>
                                        </h4>
                                        <small class="text-muted">
                                            Volunteer ID: <?php echo $vol['join_id']; ?>
                                        </small>
                                    </div>
                                </div>

                                <span class="payment-badge <?php echo $status == 'paid' ? 'paid' : 'pending'; ?>">
                                    <?php echo ucfirst($status); ?>
                                </span>
                            </div>

                            <div class="info-line">
                                <i class="fa-solid fa-envelope"></i>
                                <?php echo safe($vol['email']); ?>
                            </div>

                            <div class="info-line">
                                <i class="fa-solid fa-phone"></i>
                                <?php echo safe($vol['phone']); ?>
                            </div>

                            <div class="info-line">
                                <i class="fa-solid fa-clock"></i>
                                Joined:
                                <?php echo date("d M Y, h:i A", strtotime($vol['joined_at'])); ?>
                            </div>

                            <div class="info-line">
                                <i class="fa-solid fa-indian-rupee-sign"></i>
                                Payment:
                                ₹<?php echo isset($event['volunteer_payment']) ? safe($event['volunteer_payment']) : "0"; ?>
                            </div>

                            <div class="qr-box">
                                <strong>
                                    <i class="fa-solid fa-qrcode me-1"></i>
                                    Volunteer Payment Scanner
                                </strong>

                                <div class="mt-3">
                                    <?php if (!empty($vol['volunteer_qr'])) { ?>
                                        <img src="../uploads/volunteer_qr/<?php echo htmlspecialchars($vol['volunteer_qr']); ?>"
                                             class="qr-img"
                                             alt="Volunteer QR">
                                    <?php } else { ?>
                                        <p class="text-muted mb-0">
                                            No QR uploaded by volunteer.
                                        </p>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <a href="mark-paid.php?id=<?php echo $vol['join_id']; ?>&event_id=<?php echo $event_id; ?>"
                                   class="btn btn-success btn-sm rounded-pill px-3">
                                    <i class="fa-solid fa-check me-1"></i>
                                    Mark Paid
                                </a>

                                <a href="mailto:<?php echo htmlspecialchars($vol['email']); ?>"
                                   class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                    <i class="fa-solid fa-envelope me-1"></i>
                                    Email
                                </a>
                            </div>

                        </div>
                    </div>

                <?php } ?>
            </div>

        <?php } else { ?>

            <div class="empty-box">
                <i class="fa-solid fa-user-slash fa-4x mb-4 text-info"></i>
                <h3>No Volunteers Joined Yet</h3>
                <p class="text-light">
                    Once volunteers join this event, their details will appear here.
                </p>

                <a href="my-events.php" class="btn-main mt-3">
                    Back to My Events
                </a>
            </div>

        <?php } ?>

    </div>
</section>

<footer>
    © 2026 Eventix | Event Volunteers
</footer>

</body>
</html>