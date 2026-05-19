<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include('../config/db.php');

if (!isset($_SESSION['organizer_id'])) {
    echo "Organizer not logged in.";
    exit();
}

$organizer_id = $_SESSION['organizer_id'];

$events = $conn->query("
    SELECT * FROM events
    WHERE organizer_id='$organizer_id'
    ORDER BY event_date DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Events | Eventix</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            margin: 0;
            padding: 40px 20px;
            background: linear-gradient(135deg, #050816, #111326, #1f1b4d);
            min-height: 100vh;
            font-family: Arial, sans-serif;
            color: #f3f4f6;
        }

        .container {
            max-width: 1100px;
            margin: auto;
        }

        .back-btn {
            display: inline-block;
            margin-bottom: 25px;
            padding: 10px 18px;
            background: rgba(255,255,255,0.1);
            color: white;
            text-decoration: none;
            border-radius: 10px;
        }

        .title-box {
            background: rgba(255,255,255,0.05);
            padding: 30px;
            border-radius: 22px;
            margin-bottom: 30px;
        }

        .event-card {
            background: rgba(255,255,255,0.06);
            padding: 30px;
            border-radius: 22px;
            margin-bottom: 30px;
        }

        .event-card h2 {
            border-left: 4px solid #ec4899;
            padding-left: 12px;
        }

        .event-meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        .meta-item {
            background: rgba(0,0,0,0.25);
            padding: 15px;
            border-radius: 14px;
        }

        .empty {
            padding: 15px;
            background: rgba(245,158,11,0.15);
            color: #fcd34d;
            border-radius: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(0,0,0,0.25);
            border-radius: 14px;
            overflow: hidden;
        }

        th, td {
            padding: 14px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        th {
            color: #c084fc;
            text-align: left;
        }
    </style>
</head>

<body>

<div class="container">

    <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>

    <div class="title-box">
        <h1>My Events 🎉</h1>
        <p>Monitor your events and joined volunteers.</p>
    </div>

    <?php if ($events && $events->num_rows > 0): ?>

        <?php while ($event = $events->fetch_assoc()): ?>

            <?php
            $event_id = $event['id'];

            $volunteers = $conn->query("
                SELECT 
                    volunteers.full_name AS name,
                    volunteers.email,
                    volunteers.phone
                FROM volunteer_events
                JOIN volunteers 
                    ON volunteer_events.volunteer_id = volunteers.id
                WHERE volunteer_events.event_id = '$event_id'
                AND volunteer_events.status = 'joined'
            ");
            ?>

            <div class="event-card">

                <h2><?php echo htmlspecialchars($event['event_name']); ?></h2>

                <div class="event-meta-grid">
                    <div class="meta-item">
                        📅 <strong>Date:</strong> <?php echo htmlspecialchars($event['event_date']); ?>
                    </div>

                    <div class="meta-item">
                        📍 <strong>Venue:</strong> <?php echo htmlspecialchars($event['venue']); ?>
                    </div>

                    <div class="meta-item">
                        👥 <strong>Volunteers:</strong> <?php echo $volunteers->num_rows; ?>
                    </div>
                </div>

                <h3>Roster List</h3>

                <?php if ($volunteers->num_rows > 0): ?>

                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email Address</th>
                                <th>Phone Number</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php while ($vol = $volunteers->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($vol['name']); ?></td>
                                    <td><?php echo htmlspecialchars($vol['email']); ?></td>
                                    <td><?php echo htmlspecialchars($vol['phone']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>

                <?php else: ?>

                    <div class="empty">⚠ No volunteers have joined this event yet.</div>

                <?php endif; ?>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <div class="empty">No events created yet.</div>

    <?php endif; ?>

</div>

</body>
</html>