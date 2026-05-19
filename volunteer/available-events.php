<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['volunteer_id'])) {
    header("Location: login.php");
    exit();
}

$volunteer_id = (int)$_SESSION['volunteer_id'];

$events = mysqli_query($conn, "
    SELECT * FROM events
    WHERE status = 'upcoming'
    ORDER BY event_date ASC
");

function value($row, $keys, $default = '') {
    foreach ($keys as $key) {
        if (isset($row[$key]) && $row[$key] !== '') {
            return $row[$key];
        }
    }
    return $default;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Available Events | Eventix</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #100b2b;
            color: #111;
        }

        .container {
            width: 90%;
            margin: 30px auto;
        }

        h1 {
            color: white;
            margin-bottom: 25px;
        }

        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
            gap: 25px;
        }

        .event-card {
            background: white;
            padding: 28px;
            border-radius: 18px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        }

        .event-card h2 {
            margin-top: 0;
            color: #222;
        }

        .info {
            margin: 12px 0;
            font-size: 16px;
            color: #333;
        }

        .box {
            background: #f8f9fa;
            border: 1px solid #ddd;
            padding: 16px;
            border-radius: 14px;
            margin-top: 18px;
            line-height: 1.7;
        }

        .payment {
            background: #eafaf0;
            border: 1px solid #b7efc5;
            color: #075e2d;
            padding: 16px;
            border-radius: 14px;
            margin-top: 18px;
            line-height: 1.7;
        }

        .btn {
            display: block;
            width: 100%;
            margin-top: 20px;
            padding: 13px;
            border-radius: 7px;
            border: none;
            text-align: center;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
        }

        .join-btn {
            background: #6c2ff2;
            color: white;
        }

        .joined-btn {
            background: #9aa0a6;
            color: white;
        }

        .blocked-btn {
            background: #ff9800;
            color: white;
        }

        .full-btn {
            background: #dc3545;
            color: white;
        }

        .map-btn {
            display: inline-block;
            margin-top: 15px;
            padding: 8px 12px;
            border: 1px solid #0d6efd;
            color: #0d6efd;
            text-decoration: none;
            border-radius: 6px;
        }
    </style>
</head>

<body>

<div class="container">
    <h1>Available Events</h1>

    <div class="events-grid">

        <?php while ($row = mysqli_fetch_assoc($events)): ?>

            <?php
            $event_id = (int)$row['id'];
            $event_date = value($row, ['event_date']);
            $required_volunteers = (int)value($row, ['required_volunteers'], 0);

            // CHECK ONLY THIS EVENT JOINED
            $check_join = $conn->prepare("
                SELECT id 
                FROM volunteer_events 
                WHERE volunteer_id = ? 
                AND event_id = ? 
                AND status = 'joined'
            ");
            $check_join->bind_param("ii", $volunteer_id, $event_id);
            $check_join->execute();
            $joined_result = $check_join->get_result();
            $is_joined = $joined_result->num_rows > 0;

            // CHECK SAME DATE OTHER EVENT
            $check_same_date = $conn->prepare("
                SELECT ve.id
                FROM volunteer_events ve
                JOIN events e ON ve.event_id = e.id
                WHERE ve.volunteer_id = ?
                AND e.event_date = ?
                AND ve.event_id != ?
                AND ve.status = 'joined'
            ");
            $check_same_date->bind_param("isi", $volunteer_id, $event_date, $event_id);
            $check_same_date->execute();
            $same_date_result = $check_same_date->get_result();
            $same_date_blocked = $same_date_result->num_rows > 0;

            // CHECK VOLUNTEER LIMIT
            $count_stmt = $conn->prepare("
                SELECT COUNT(*) AS total
                FROM volunteer_events
                WHERE event_id = ?
                AND status = 'joined'
            ");
            $count_stmt->bind_param("i", $event_id);
            $count_stmt->execute();
            $joined_count = (int)$count_stmt->get_result()->fetch_assoc()['total'];

            $is_full = ($required_volunteers > 0 && $joined_count >= $required_volunteers);
            ?>

            <div class="event-card">

                <h2><?php echo htmlspecialchars(value($row, ['event_name', 'name'], 'Event Name')); ?></h2>

                <p class="info">📅 <?php echo htmlspecialchars($event_date); ?></p>
                <p class="info">🕘 <?php echo htmlspecialchars(value($row, ['start_time', 'event_time'], '')); ?></p>
                <p class="info">📍 <?php echo htmlspecialchars(value($row, ['venue', 'venue_name'], '')); ?></p>
                <p class="info">👥 Required Volunteers: <?php echo $required_volunteers; ?></p>

                <div class="box">
                    <strong>Volunteer Duties:</strong><br>
                    <?php echo htmlspecialchars(value($row, ['volunteer_duties'], 'Not specified')); ?>
                </div>

                <div class="box">
                    <strong>Special Instructions:</strong><br>
                    <?php echo htmlspecialchars(value($row, ['special_instructions'], 'Not specified')); ?>
                </div>

                <div class="payment">
                    <strong>Volunteer Payment:</strong>
                    ₹<?php echo htmlspecialchars(value($row, ['volunteer_payment', 'payment_per_person'], '0')); ?> per person<br>

                    <strong>Payment Schedule:</strong>
                    <?php echo htmlspecialchars(value($row, ['payment_schedule'], 'Within 3 Days')); ?><br>

                    <strong>Payment Method:</strong>
                    <?php echo htmlspecialchars(value($row, ['payment_method'], 'UPI Transfer')); ?>
                </div>

                <?php if (value($row, ['google_map_link']) != ''): ?>
                    <a class="map-btn" href="<?php echo htmlspecialchars($row['google_map_link']); ?>" target="_blank">
                        🗺 View Location
                    </a>
                <?php endif; ?>

                <?php if ($is_joined): ?>

                    <button class="btn joined-btn" disabled>✅ Already Joined</button>

                <?php elseif ($same_date_blocked): ?>

                    <button class="btn blocked-btn" disabled>⚠ Same Date Event Already Joined</button>

                <?php elseif ($is_full): ?>

                    <button class="btn full-btn" disabled>❌ Volunteer Limit Full</button>

                <?php else: ?>

                    <a class="btn join-btn"
                       href="join-event.php?event_id=<?php echo $event_id; ?>">
                        Join Event
                    </a>

                <?php endif; ?>

            </div>

        <?php endwhile; ?>

    </div>
</div>

</body>
</html>