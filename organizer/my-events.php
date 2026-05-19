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

// FETCH ORGANIZER EVENTS
$events = $conn->query("
    SELECT * FROM events
    WHERE organizer_id='$organizer_id'
    ORDER BY event_date DESC
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Events</title>

    <style>

        body{
            margin:0;
            padding:30px;
            background:#0f0c29;
            font-family:Arial;
            color:white;
        }

        .container{
            width:95%;
            margin:auto;
        }

        .title-box{
            background:rgba(255,255,255,0.1);
            padding:25px;
            border-radius:20px;
            margin-bottom:30px;
        }

        .event-card{
            background:white;
            color:black;
            padding:25px;
            border-radius:20px;
            margin-bottom:25px;
        }

        .volunteer-box{
            margin-top:20px;
            border:1px solid #ddd;
            border-radius:15px;
            padding:15px;
            background:#f9fafb;
        }

        table{
            width:100%;
            border-collapse:collapse;
            margin-top:15px;
        }

        table th,
        table td{
            border:1px solid #ddd;
            padding:12px;
            text-align:left;
        }

        table th{
            background:#7c3aed;
            color:white;
        }

        .empty{
            padding:15px;
            background:#fff3cd;
            border-radius:10px;
            color:#856404;
            margin-top:15px;
        }

    </style>

</head>

<body>

<div class="container">

    <div class="title-box">

        <h1>My Events 🎉</h1>
        <p>See joined volunteers details.</p>

    </div>

    <?php while($event = $events->fetch_assoc()) { ?>

        <div class="event-card">

            <h2>
                <?php echo $event['event_name']; ?>
            </h2>

            <p>
                📅 <?php echo $event['event_date']; ?>
            </p>

            <p>
                📍 <?php echo $event['venue']; ?>
            </p>

            <?php

            // FETCH JOINED VOLUNTEERS
            $event_id = $event['id'];

            $volunteers = $conn->query("
                SELECT users.name,
                       users.email,
                       users.phone
                FROM volunteer_events
                JOIN users
                ON volunteer_events.volunteer_id = users.id
                WHERE volunteer_events.event_id = '$event_id'
            ");

            ?>

            <div class="volunteer-box">

                <h3>
                    Joined Volunteers:
                    <?php echo $volunteers->num_rows; ?>
                </h3>

                <?php if($volunteers->num_rows > 0){ ?>

                    <table>

                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                        </tr>

                        <?php while($vol = $volunteers->fetch_assoc()) { ?>

                        <tr>

                            <td>
                                <?php echo $vol['name']; ?>
                            </td>

                            <td>
                                <?php echo $vol['email']; ?>
                            </td>

                            <td>
                                <?php echo $vol['phone']; ?>
                            </td>

                        </tr>

                        <?php } ?>

                    </table>

                <?php } else { ?>

                    <div class="empty">

                        No volunteers joined yet.

                    </div>

                <?php } ?>

            </div>

        </div>

    <?php } ?>

</div>

</body>
</html>