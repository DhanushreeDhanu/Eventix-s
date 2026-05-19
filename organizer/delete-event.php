<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['organizer_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: my-events.php");
    exit();
}

$organizer_id = $_SESSION['organizer_id'];
$event_id = intval($_GET['id']);

/* Permanent delete */
$stmt = $conn->prepare("DELETE FROM events WHERE id=? AND organizer_id=?");
$stmt->bind_param("ii", $event_id, $organizer_id);

if ($stmt->execute()) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Deleted | Eventix</title>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(circle at top left, rgba(239,68,68,.28), transparent 35%),
                radial-gradient(circle at bottom right, rgba(220,38,38,.22), transparent 30%),
                linear-gradient(135deg, #050816, #15162c, #7f1d1d);
            font-family: "Segoe UI", sans-serif;
            overflow: hidden;
        }

        .delete-box {
            text-align: center;
            color: white;
        }

        .icon-circle {
            width: 100px;
            height: 100px;
            margin: auto;
            border-radius: 50%;
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.18);
            backdrop-filter: blur(12px);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 42px;
            color: #f87171;
            margin-bottom: 25px;
        }

        h1 {
            font-size: 34px;
            font-weight: 900;
            margin-bottom: 10px;
        }

        p {
            color: #d1d5db;
            margin-bottom: 28px;
            font-size: 16px;
        }

        .progress-container {
            width: 280px;
            height: 8px;
            background: rgba(255,255,255,.15);
            border-radius: 999px;
            overflow: hidden;
            margin: auto;
        }

        .progress-bar {
            height: 100%;
            width: 100%;
            background: linear-gradient(90deg, #ef4444, #dc2626, #7f1d1d);
            animation: loadBar 2.3s linear forwards;
        }

        @keyframes loadBar {
            from { width: 100%; }
            to { width: 0%; }
        }
    </style>
</head>

<body>

<div class="delete-box">

    <div class="icon-circle">
        <i class="fa-solid fa-trash-can"></i>
    </div>

    <h1>Deleting Event...</h1>

    <p>
        Please wait while Eventix permanently removes your event.
    </p>

    <div class="progress-container">
        <div class="progress-bar"></div>
    </div>

</div>

<script>
    setTimeout(() => {
        Swal.fire({
            title: 'Event Deleted Successfully!',
            text: 'Your event has been permanently removed.',
            icon: 'success',
            confirmButtonText: 'Back to My Events',
            confirmButtonColor: '#dc2626',
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then(() => {
            window.location.href = 'my-events.php';
        });
    }, 2300);
</script>

</body>
</html>

<?php
} else {
    header("Location: my-events.php");
    exit();
}
?>