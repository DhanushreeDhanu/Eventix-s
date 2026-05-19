<?php
session_start();
include('../config/db.php');

// Check organizer login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'organizer') {

    header("Location: ../login.php");
    exit();

}

// Organizer ID
$organizer_id = $_SESSION['user_id'];

$sql = "SELECT * FROM events WHERE organizer_id = ? ORDER BY event_date DESC, id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$result = $stmt->get_result();

function showValue($value) {
    return !empty($value) ? htmlspecialchars($value) : "Not added";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Events | Eventix</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

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

        .page-title {
            font-weight: 900;
        }

        .event-card {
            background: rgba(255,255,255,.96);
            color: #111827;
            border-radius: 28px;
            padding: 28px;
            height: 100%;
            box-shadow: 0 25px 70px rgba(0,0,0,.25);
            transition: .3s;
        }

        .event-card:hover {
            transform: translateY(-7px);
        }

        .event-type {
            display: inline-block;
            background: rgba(124,58,237,.12);
            color: #6d28d9;
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 800;
        }

        .status-badge {
            padding: 7px 14px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 800;
        }

        .upcoming {
            background: #dcfce7;
            color: #166534;
        }

        .completed {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        .info-line {
            margin-top: 12px;
            color: #4b5563;
        }

        .info-line i {
            width: 24px;
            color: #7c3aed;
        }

.section-box {
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 18px;
    padding: 20px 22px;
    margin-top: 18px;
    color: #374151;

    white-space: pre-wrap;
    overflow-wrap: anywhere;
    word-break: break-word;

    line-height: 1.8;
    font-size: 15px;
    text-align: left;

    display: flex;
    flex-direction: column;
    align-items: flex-start;
}
        .payment-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 18px;
            padding: 15px;
            margin-top: 16px;
            color: #166534;
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
        <a href="create-event.php" class="btn btn-light btn-sm me-2">Create Event</a>
        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
</nav>

<section class="wrapper">
    <div class="container">

        <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
            <div>
                <h1 class="page-title">My Events</h1>
                <p class="text-light mb-0">
                    Manage all events created by you.
                </p>
            </div>

            <a href="create-event.php" class="btn-main mt-3 mt-md-0">
                <i class="fa-solid fa-plus me-2"></i>
                Create New Event
            </a>
        </div>

        <?php if ($result->num_rows > 0) { ?>

            <div class="row g-4">
                <?php while ($event = $result->fetch_assoc()) { ?>

                    <?php
                    $statusClass = strtolower($event['status']);
                    ?>

                    <div class="col-lg-6">
                        <div class="event-card">

                            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                <div>
                                    <span class="event-type">
                                        <?php echo showValue($event['event_type']); ?>
                                    </span>

                                    <h3 class="mt-3 mb-1">
                                        <?php echo showValue($event['event_name']); ?>
                                    </h3>
                                </div>

                                <span class="status-badge <?php echo $statusClass; ?>">
                                    <?php echo ucfirst($event['status']); ?>
                                </span>
                            </div>

                            <div class="info-line">
                                <i class="fa-solid fa-calendar-days"></i>
                                <strong>Date:</strong>
                                <?php echo date("d M Y", strtotime($event['event_date'])); ?>
                            </div>

                            <div class="info-line">
                                <i class="fa-solid fa-clock"></i>
                                <strong>Time:</strong>
                                <?php echo showValue($event['event_time']); ?>
                            </div>

                            <div class="info-line">
                                <i class="fa-solid fa-location-dot"></i>
                                <strong>Venue:</strong>
                                <?php echo showValue($event['venue']); ?>
                            </div>

                            <?php if (!empty($event['google_map_link'])) { ?>
                                <div class="info-line">
                                    <i class="fa-solid fa-map-location-dot"></i>
                                    <a href="<?php echo htmlspecialchars($event['google_map_link']); ?>"
                                       target="_blank"
                                       class="fw-bold text-decoration-none">
                                        Open Google Map
                                    </a>
                                </div>
                            <?php } ?>

                            <div class="info-line">
                                <i class="fa-solid fa-users"></i>
                                <strong>Required Volunteers:</strong>
                                <?php echo showValue($event['required_volunteers']); ?>
                            </div>

                            <div class="info-line">
                                <i class="fa-solid fa-chair"></i>
                                <strong>Event Capacity:</strong>
                                <?php echo isset($event['capacity']) ? showValue($event['capacity']) : "Not added"; ?>
                            </div>

                            <div class="info-line">
                                <i class="fa-solid fa-user-tie"></i>
                                <strong>Contact:</strong>
                                <?php echo isset($event['contact_person']) ? showValue($event['contact_person']) : "Not added"; ?>
                                -
                                <?php echo isset($event['contact_phone']) ? showValue($event['contact_phone']) : "Not added"; ?>
                            </div>

                            <div class="payment-box">
                                <strong>
                                    <i class="fa-solid fa-money-bill-transfer me-2"></i>
                                    Volunteer Payment
                                </strong>

                                <div class="mt-2">
                                    <strong>Amount Per Volunteer:</strong>
                                    ₹<?php echo isset($event['volunteer_payment']) ? showValue($event['volunteer_payment']) : "0"; ?>
                                </div>

                                <div>
                                    <strong>Payment Timeline:</strong>
                                    <?php echo isset($event['payment_timeline']) ? showValue($event['payment_timeline']) : "Not added"; ?>
                                </div>

                                <div>
                                    <strong>Payment Method:</strong>
                                    <?php echo isset($event['payment_method']) ? showValue($event['payment_method']) : "Not added"; ?>
                                </div>
                            </div>

                           <?php if (!empty($event['volunteer_duties'])) { ?>
    <div class="section-box">
        <div class="fw-bold text-dark mb-2">Volunteer Duties:</div>
        <div>
            <?php echo nl2br(htmlspecialchars($event['volunteer_duties'])); ?>
        </div>
    </div>
<?php } ?>

<?php if (!empty($event['instructions'])) { ?>
    <div class="section-box">
        <div class="fw-bold text-dark mb-2">Special Instructions:</div>
        <div>
            <?php echo nl2br(htmlspecialchars($event['instructions'])); ?>
        </div>
    </div>
<?php } ?>

<div class="section-box">
    <div class="fw-bold text-dark mb-2">Event Description:</div>
    <div>
        <?php echo nl2br(htmlspecialchars($event['description'])); ?>
    </div>
</div>

                            <div class="d-flex flex-wrap gap-2 mt-4">
                               <a href="volunteers.php?event_id=<?php echo $event['id']; ?>"
   class="btn btn-primary btn-sm rounded-pill px-3">
    <i class="fa-solid fa-users me-1"></i>
    View Volunteers
</a>

                              <a href="edit-event.php?id=<?php echo $event['id']; ?>"
   class="btn btn-outline-secondary btn-sm rounded-pill px-3">
    <i class="fa-solid fa-pen-to-square me-1"></i>
    Edit
</a>

                       <a href="delete-event.php?id=<?php echo $event['id']; ?>"
   class="btn btn-outline-danger btn-sm rounded-pill px-3">
    <i class="fa-solid fa-trash me-1"></i>
    Delete
</a>
                            </div>

                        </div>
                    </div>

                <?php } ?>
            </div>

        <?php } else { ?>

            <div class="empty-box">
                <i class="fa-solid fa-calendar-xmark fa-4x mb-4 text-info"></i>
                <h3>No Events Created Yet</h3>
                <p class="text-light">
                    Start by creating your first event. Once created, it will appear here.
                </p>

                <a href="create-event.php" class="btn-main mt-3">
                    Create Your First Event
                </a>
            </div>

        <?php } ?>

    </div>
</section>

<footer>
    © 2026 Eventix | My Events
</footer>

</body>
</html>