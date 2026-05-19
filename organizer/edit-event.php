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
$message = "";

// Pull the existing record map cleanly matching the table definition
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ? AND organizer_id = ?");
$stmt->bind_param("ii", $event_id, $organizer_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    header("Location: my-events.php");
    exit();
}

if (isset($_POST['update_event'])) {
    $event_name = trim($_POST['event_name']);
    $event_type = trim($_POST['event_type']);
    $event_date = $_POST['event_date'];
    $event_time = trim($_POST['event_time']);
    $venue = trim($_POST['venue']);
    
    // Extra metrics captured safely from form parameters
    $google_map_link = trim($_POST['google_map_link']);
    $required_volunteers = intval($_POST['required_volunteers']);
    $capacity = intval($_POST['capacity']);
    $volunteer_payment = intval($_POST['volunteer_payment']);
    $payment_timeline = trim($_POST['payment_timeline']);
    $payment_method = trim($_POST['payment_method']);

    $volunteer_duties = trim($_POST['volunteer_duties']);
    $instructions = trim($_POST['instructions']);
    $raw_description = trim($_POST['description']);

    // Automatic status logic based on date
    $currentDate = date("Y-m-d");
    if ($event_date < $currentDate) {
        $status = "completed";
    } else {
        $status = "upcoming";
    }

    if (isset($_POST['manual_cancel']) && $_POST['manual_cancel'] == "yes") {
        $status = "cancelled";
    }

    // FIXED: Form metadata parameters cleanly packed into description to avoid SQL schema definition crashes
    $compiled_description = "Description:\n" . $raw_description . "\n\n" .
                             "Volunteer Duties:\n" . $volunteer_duties . "\n\n" .
                             "Special Instructions:\n" . $instructions . "\n\n" .
                             "Payment Setup:\n" . $payment_method . " (Target Capacity: " . $capacity . ")\n" .
                             "Location Map Link: " . ($google_map_link ?: 'None');

    // FIXED: Updated query mapping string parameters safely matching the target data engine
    $update = $conn->prepare("UPDATE events SET 
        event_name = ?, event_type = ?, event_date = ?, event_time = ?, venue = ?, 
        description = ?, required_volunteers = ?, volunteer_payment = ?, payment_timeline = ?, status = ?
        WHERE id = ? AND organizer_id = ?");

    $update->bind_param(
        "ssssssiissii",
        $event_name,
        $event_type,
        $event_date,
        $event_time,
        $venue,
        $compiled_description,
        $required_volunteers,
        $volunteer_payment,
        $payment_timeline,
        $status,
        $event_id,
        $organizer_id
    );

    if ($update->execute()) {
        echo "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Event Updated | Eventix</title>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css'>
            <style>
                body {
                    min-height: 100vh;
                    margin: 0;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: linear-gradient(135deg, #050816, #15162c, #4f46e5);
                    font-family: 'Segoe UI', sans-serif;
                    overflow: hidden;
                }
                .update-box { text-align: center; color: white; }
                .icon-circle {
                    width: 95px; height: 95px; margin: auto; border-radius: 50%;
                    background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.18);
                    backdrop-filter: blur(12px); display: flex; align-items: center;
                    justify-content: center; font-size: 38px; color: #22d3ee; margin-bottom: 25px;
                }
                .progress-container { width: 280px; height: 8px; background: rgba(255,255,255,.15); border-radius: 999px; overflow: hidden; margin: auto; }
                .progress-bar { height: 100%; width: 100%; background: linear-gradient(90deg, #22d3ee, #7c3aed, #ec4899); animation: loadBar 2.5s linear forwards; }
                @keyframes loadBar { from { width: 100%; } to { width: 0%; } }
            </style>
        </head>
        <body>
            <div class='update-box'>
                <div class='icon-circle'><i class='fa-solid fa-pen-to-square'></i></div>
                <h1>Updating Your Event...</h1>
                <p>Please wait while Eventix saves your changes.</p>
                <div class='progress-container'><div class='progress-bar'></div></div>
            </div>
            <script>
                setTimeout(() => {
                    Swal.fire({
                        title: 'Event Updated Successfully!',
                        text: 'Your event changes are now live.',
                        icon: 'success',
                        confirmButtonText: 'Back to My Events',
                        confirmButtonColor: '#7c3aed',
                        allowOutsideClick: false
                    }).then(() => {
                        window.location.href = 'my-events.php';
                    });
                }, 2500);
            </script>
        </body>
        </html>";
        exit();
    } else {
        $message = 'Failed to update event. Database processing error.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Event | Eventix</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
            border-bottom: 1px solid rgba(255,255,255,.12);
        }
        .brand-box {
            width: 42px; height: 42px; border-radius: 14px;
            background: linear-gradient(135deg, #7c3aed, #22d3ee);
            display: inline-grid; place-items: center; margin-right: 10px;
        }
        .wrapper { padding: 50px 0; }
        .main-card {
            background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.18);
            backdrop-filter: blur(18px); border-radius: 30px; overflow: hidden;
            box-shadow: 0 30px 90px rgba(0,0,0,.35);
        }
        .left-panel {
            padding: 50px;
            background:
                radial-gradient(circle at top left, rgba(124,58,237,.45), transparent 35%),
                radial-gradient(circle at bottom right, rgba(34,211,238,.25), transparent 30%);
        }
        .left-panel h1 { font-size: 42px; font-weight: 900; line-height: 1; }
        .left-panel p { color: #d1d5db; margin-top: 22px; font-size: 17px; }
        .feature { display: flex; gap: 14px; margin-top: 18px; color: #d1d5db; }
        .feature i { color: #22d3ee; margin-top: 4px; }
        .form-panel { background: rgba(255,255,255,.97); color: #111827; padding: 42px; }
        .section-heading {
            font-weight: 850; margin-top: 26px; margin-bottom: 16px;
            color: #4f46e5; border-left: 5px solid #7c3aed; padding-left: 12px;
        }
        label { font-weight: 650; color: #1f2937; margin-bottom: 7px; }
        .form-control, .form-select { padding: 13px; border-radius: 14px; border: 1px solid #d1d5db; }
        .form-control:focus, .form-select:focus { border-color: #7c3aed; box-shadow: 0 0 0 .2rem rgba(124,58,237,.15); }
        textarea.form-control { min-height: 110px; }
        .status-note { background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 18px; padding: 16px; color: #374151; margin-bottom: 22px; }
        .btn-main {
            background: linear-gradient(135deg, #7c3aed, #ec4899); border: none;
            padding: 14px; border-radius: 999px; color: white; font-weight: 850; transition: .3s;
        }
        .btn-main:hover { color: white; transform: translateY(-2px); }
        .btn-back { border-radius: 999px; padding: 12px; font-weight: 750; }
        footer { background: rgba(5,8,22,.9); color: #9ca3af; text-align: center; padding: 15px; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark px-4 py-3">
    <a class="navbar-brand fw-bold d-flex align-items-center" href="dashboard.php">
        <span class="brand-box"><i class="fa-solid fa-bolt"></i></span>
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
        <div class="row main-card">

            <div class="col-lg-4 left-panel">
                <h1>Edit your event details.</h1>
                <p>Update event information, volunteer requirements, location, and automatic operational parameters.</p>

                <div class="feature">
                    <i class="fa-solid fa-pen-to-square"></i>
                    <span>Modify event details anytime.</span>
                </div>
                <div class="feature">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span>Status is decided automatically by date.</span>
                </div>
                <div class="feature">
                    <i class="fa-solid fa-users-gear"></i>
                    <span>Manage volunteer duties and payment details.</span>
                </div>
                <div class="feature">
                    <i class="fa-solid fa-ban"></i>
                    <span>Cancel event manually if needed.</span>
                </div>
            </div>

            <div class="col-lg-8 form-panel">
                <h3 class="text-center fw-bold mb-2">Edit Event</h3>
                <p class="text-center text-muted mb-4">Update details carefully before saving changes</p>

                <?php if ($message != "") { ?>
                    <script>
                        Swal.fire({ icon: 'error', title: 'Oops!', text: '<?php echo $message; ?>', confirmButtonColor: '#7c3aed' });
                    </script>
                <?php } ?>

                <div class="status-note">
                    <strong>Editing:</strong> <?php echo htmlspecialchars($event['event_name']); ?>
                </div>

                <form method="POST">
                    <h5 class="section-heading">Basic Event Details</h5>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label>Event Name</label>
                            <input type="text" name="event_name" class="form-control" value="<?php echo htmlspecialchars($event['event_name']); ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Event Type</label>
                            <select name="event_type" class="form-select" required>
                                <?php
                                $types = ["Technical", "Workshop", "Seminar", "College Event", "Cultural", "Sports", "Other"];
                                foreach ($types as $type) {
                                    $selected = ($event['event_type'] == $type) ? "selected" : "";
                                    echo "<option $selected>$type</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <h5 class="section-heading">Date & Location</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Event Date</label>
                            <input type="date" name="event_date" class="form-control" value="<?php echo $event['event_date']; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Event Time (Duration)</label>
                            <input type="text" name="event_time" class="form-control" value="<?php echo htmlspecialchars($event['event_time']); ?>" placeholder="Example: 09:00 - 17:00" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Venue</label>
                        <input type="text" name="venue" class="form-control" value="<?php echo htmlspecialchars($event['venue']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Google Map Link</label>
                        <input type="url" name="google_map_link" class="form-control" value="<?php echo htmlspecialchars($event['google_map_link'] ?? ''); ?>">
                    </div>

                    <h5 class="section-heading">Volunteer & Payment</h5>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Required Volunteers</label>
                            <input type="number" name="required_volunteers" class="form-control" value="<?php echo htmlspecialchars($event['required_volunteers']); ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Capacity</label>
                            <input type="number" name="capacity" class="form-control" value="<?php echo htmlspecialchars($event['capacity'] ?? $event['required_volunteers']); ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Payment Per Volunteer ₹</label>
                            <input type="number" name="volunteer_payment" class="form-control" value="<?php echo htmlspecialchars($event['volunteer_payment'] ?? 0); ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Payment Timeline</label>
                            <select name="payment_timeline" class="form-select" required>
                                <?php
                                $timelines = ["Same Day After Event", "Within 24 Hours", "Within 2 Days", "Within 3 Days", "After Organizer Approval"];
                                foreach ($timelines as $timeline) {
                                    $selected = (($event['payment_timeline'] ?? '') == $timeline) ? "selected" : "";
                                    echo "<option $selected>$timeline</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Payment Method</label>
                            <select name="payment_method" class="form-select" required>
                                <?php
                                $methods = ["UPI Transfer", "Bank Transfer", "Cash Payment", "Hybrid (Cash + Online)"];
                                foreach ($methods as $method) {
                                    $selected = (($event['payment_method'] ?? '') == $method) ? "selected" : "";
                                    echo "<option $selected>$method</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <h5 class="section-heading">Instructions & Description</h5>
                    <div class="mb-3">
                        <label>Volunteer Duties</label>
                        <textarea name="volunteer_duties" class="form-control" placeholder="Describe the duties..." required><?php echo htmlspecialchars($event['volunteer_duties'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Special Instructions</label>
                        <textarea name="instructions" class="form-control" placeholder="Reporting time, uniform, etc..." required><?php echo htmlspecialchars($event['instructions'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control" required><?php echo htmlspecialchars($event['description']); ?></textarea>
                    </div>

                    <h5 class="section-heading">Event Status Modification</h5>
                    <div class="status-note">
                        <strong>Auto Status Engine:</strong> Past timelines map to Completed status automatically.
                    </div>
                    <div class="mb-4">
                        <label>Cancel Operational Matrix</label>
                        <select name="manual_cancel" class="form-select">
                            <option value="no" <?php echo ($event['status'] !== 'cancelled') ? 'selected' : ''; ?>>Keep Active / Auto Status</option>
                            <option value="yes" <?php echo ($event['status'] === 'cancelled') ? 'selected' : ''; ?>>Cancel this event</option>
                        </select>
                    </div>

                    <button type="submit" name="update_event" class="btn btn-main w-100">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Save Changes Globally
                    </button>

                    <a href="my-events.php" class="btn btn-outline-secondary btn-back w-100 mt-2">
                        Back to My Events
                    </a>
                </form>
            </div>

        </div>
    </div>
</section>

<footer>
    © 2026 Eventix | Edit Infrastructure Node
</footer>

</body>
</html>