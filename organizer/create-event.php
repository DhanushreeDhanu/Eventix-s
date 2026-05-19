<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['organizer_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";
$organizer_id = $_SESSION['organizer_id'];

if (isset($_POST['create_event'])) {

    $event_name = trim($_POST['event_name']);
    $event_type = trim($_POST['event_type']);
    $event_date = $_POST['event_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    $venue = trim($_POST['venue']);
    $google_map_link = trim($_POST['google_map_link']);

    $required_volunteers = intval($_POST['required_volunteers']);
    $volunteer_payment = intval($_POST['volunteer_payment']);
    $payment_timeline = trim($_POST['payment_timeline']);
    $payment_method = trim($_POST['payment_method']);

    $contact_person = trim($_POST['contact_person']);
    $contact_phone = trim($_POST['contact_phone']);

    $volunteer_duties = trim($_POST['volunteer_duties']);
    $instructions = trim($_POST['instructions']);
    $raw_description = trim($_POST['description']);

    if ($end_time <= $start_time) {
        $message = "End time must be after start time.";
    } elseif ($required_volunteers < 1) {
        $message = "Required volunteers must be at least 1.";
    } elseif ($volunteer_payment < 0) {
        $message = "Volunteer payment cannot be negative.";
    } elseif (!preg_match("/^[A-Za-z ]+$/", $contact_person)) {
        $message = "Contact person must contain only letters.";
    } elseif (!preg_match("/^[0-9]{10}$/", $contact_phone)) {
        $message = "Contact phone must be exactly 10 digits.";
    } else {

        $event_time = $start_time . " - " . $end_time;

        // FIXED: Concatenate extra form criteria context dynamically into description to prevent SQL definition crashes
        $compiled_description = "Description:\n" . $raw_description . "\n\n" .
                                 "Volunteer Duties:\n" . $volunteer_duties . "\n\n" .
                                 "Special Instructions:\n" . $instructions . "\n\n" .
                                 "Contact Reference:\n" . $contact_person . " (" . $contact_phone . ")\n\n" .
                                 "Payment Method:\n" . $payment_method . " | Map Link: " . ($google_map_link ?: 'None Provided');

        // Safely aligns directly with your database schema execution map
        $stmt = $conn->prepare("INSERT INTO events 
        (organizer_id, event_name, event_type, event_date, event_time, venue, description, required_volunteers, volunteer_payment, payment_timeline, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'upcoming')");

        $stmt->bind_param(
            "issssssiis",
            $organizer_id,
            $event_name,
            $event_type,
            $event_date,
            $event_time,
            $venue,
            $compiled_description,
            $required_volunteers,
            $volunteer_payment,
            $payment_timeline
        );

        if ($stmt->execute()) {
            echo "
            <!DOCTYPE html>
            <html lang='en'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Event Published | Eventix</title>
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            </head>
            <body>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            title: 'Event Published Successfully!',
                            text: 'Your event is now live for volunteers.',
                            icon: 'success',
                            confirmButtonText: 'Go to My Events',
                            confirmButtonColor: '#7c3aed',
                            allowOutsideClick: false
                        }).then(() => {
                            window.location.href = 'my-events.php';
                        });
                    });
                </script>
            </body>
            </html>";
            exit();
        } else {
            $message = "Failed to create event. Database execution failure: " . htmlspecialchars($stmt->error);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Event | Eventix</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        * { box-sizing: border-box; }
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
            background: rgba(5,8,22,.88);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255,255,255,.12);
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
        .wrapper { padding: 52px 0; }
        .main-card {
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.18);
            backdrop-filter: blur(18px);
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 30px 90px rgba(0,0,0,.35);
        }
        .left-panel {
            padding: 55px 45px;
            background: 
                radial-gradient(circle at top left, rgba(124,58,237,.5), transparent 36%),
                radial-gradient(circle at bottom right, rgba(34,211,238,.28), transparent 32%);
        }
        .left-panel h1 {
            font-size: 42px;
            font-weight: 900;
            line-height: 1.05;
        }
        .left-panel p {
            color: #d1d5db;
            margin-top: 20px;
            line-height: 1.7;
            font-size: 16px;
        }
        .feature {
            display: flex;
            gap: 14px;
            margin-top: 18px;
            color: #d1d5db;
            line-height: 1.5;
        }
        .feature i { color: #22d3ee; margin-top: 4px; }
        .form-panel {
            background: rgba(255,255,255,.98);
            color: #111827;
            padding: 50px;
        }
        .form-title { font-weight: 900; color: #111827; }
        .section-heading {
            font-weight: 900;
            margin-top: 34px;
            margin-bottom: 20px;
            color: #4f46e5;
            border-left: 5px solid #7c3aed;
            padding-left: 14px;
        }
        label { font-weight: 700; margin-bottom: 8px; color: #1f2937; display: block; }
        .form-control, .form-select {
            padding: 14px 16px;
            border-radius: 15px;
            border: 1px solid #d1d5db;
            font-size: 15px;
        }
        .form-control:focus, .form-select:focus {
            border-color: #7c3aed;
            box-shadow: 0 0 0 .2rem rgba(124,58,237,.15);
        }
        textarea.form-control { min-height: 130px; resize: vertical; line-height: 1.7; }
        .duration-card, .payment-note {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 20px;
            padding: 16px 18px;
            color: #374151;
            line-height: 1.7;
        }
        .btn-main {
            background: linear-gradient(135deg, #7c3aed, #ec4899);
            border: none;
            padding: 15px;
            border-radius: 999px;
            color: white;
            font-weight: 900;
            transition: .3s;
        }
        .btn-main:hover { color: white; transform: translateY(-2px); }
        footer { background: rgba(5,8,22,.9); color: #9ca3af; text-align: center; padding: 15px; }
        @media(max-width: 768px) {
            .form-panel { padding: 30px; }
            .left-panel { padding: 35px; }
            .left-panel h1 { font-size: 34px; }
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
        <div class="row main-card">

            <div class="col-lg-4 left-panel">
                <h1>Create a professional event.</h1>
                <p>
                    Add complete event details, payment rules, volunteer duties, and clear instructions for smooth event coordination.
                </p>
                <div class="feature">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Event date starts only after 2 days.</span>
                </div>
                <div class="feature">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>User-friendly time slots with total duration.</span>
                </div>
                <div class="feature">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Venue with Google Maps location link.</span>
                </div>
                <div class="feature">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Professional volunteer payment workflow.</span>
                </div>
            </div>

            <div class="col-lg-8 form-panel">
                <h3 class="text-center form-title mb-2">Create New Event</h3>
                <p class="text-center text-muted mb-4">Fill all details professionally before publishing</p>

                <form method="POST">
                    <h5 class="section-heading">Basic Event Details</h5>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label>Event Name</label>
                            <input type="text" name="event_name" class="form-control" placeholder="Example: Tech Fest 2026" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Event Type</label>
                            <select name="event_type" class="form-select" required>
                                <option value="">Choose Type</option>
                                <option>Technical</option>
                                <option>Workshop</option>
                                <option>Seminar</option>
                                <option>College Event</option>
                                <option>Cultural</option>
                                <option>Sports</option>
                                <option>Other</option>
                            </select>
                        </div>
                    </div>

                    <h5 class="section-heading">Date & Time</h5>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Event Date</label>
                            <input type="date" name="event_date" id="event_date" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Start Time</label>
                            <input type="time" name="start_time" id="start_time" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>End Time</label>
                            <input type="time" name="end_time" id="end_time" class="form-control" required>
                        </div>
                    </div>
                    <div class="duration-card mb-3" id="duration_display">
                        Total Event Working Hours: <span class="fw-bold text-secondary">Select start and end timing.</span>
                    </div>

                    <h5 class="section-heading">Location Details</h5>
                    <div class="mb-3">
                        <label>Venue Name</label>
                        <input type="text" name="venue" class="form-control" placeholder="Example: Main Auditorium" required>
                    </div>
                    <div class="mb-3">
                        <label>Google Maps Link</label>
                        <input type="url" name="google_map_link" class="form-control" placeholder="https://maps.google.com/...">
                    </div>

                    <h5 class="section-heading">Volunteer Requirements</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Required Volunteers</label>
                            <input type="number" name="required_volunteers" class="form-control" min="1" placeholder="Minimum 1" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Volunteer Payment Per Person (₹)</label>
                            <input type="number" name="volunteer_payment" class="form-control" min="0" placeholder="Enter 0 if unpaid" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Volunteer Duties</label>
                        <textarea name="volunteer_duties" class="form-control" placeholder="Registration desk, stage support, crowd control..." required></textarea>
                    </div>

                    <h5 class="section-heading">Contact & Instructions</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Contact Person</label>
                            <input type="text" name="contact_person" class="form-control" placeholder="Organizer full name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Contact Phone</label>
                            <input type="text" name="contact_phone" id="contact_phone" maxlength="10" class="form-control" placeholder="10 digit mobile number" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Special Instructions</label>
                        <textarea name="instructions" class="form-control" placeholder="Volunteers should report 30 mins early, wear ID card..." required></textarea>
                    </div>

                    <h5 class="section-heading">Event Description</h5>
                    <div class="mb-4">
                        <textarea name="description" class="form-control" placeholder="Write full event description professionally..." required></textarea>
                    </div>

                    <h5 class="section-heading">Volunteer Payment Management</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Payment Release Schedule</label>
                            <select name="payment_timeline" class="form-select" required>
                                <option>Same Day After Event</option>
                                <option>Within 24 Hours</option>
                                <option>Within 2 Days</option>
                                <option>Within 3 Days</option>
                                <option>After Organizer Approval</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Organizer Payment Method</label>
                            <select name="payment_method" class="form-select" required>
                                <option>UPI Transfer</option>
                                <option>Bank Transfer</option>
                                <option>Cash Payment</option>
                                <option>Hybrid (Cash + Online)</option>
                            </select>
                        </div>
                    </div>

                    <div class="payment-note mb-4">
                        <strong>Volunteer Payment Workflow:</strong><br>
                        Volunteers register for the event and track instructions. Organizer reviews attendance records and coordinates payouts upon event execution completion.
                    </div>

                    <button type="submit" name="create_event" class="btn btn-main w-100">
                        <i class="fa-solid fa-paper-plane me-2"></i>
                        Publish Event Professionally
                    </button>
                </form>
            </div>

        </div>
    </div>
</section>

<footer>
    © 2026 Eventix | Premium Organizer Event Creation
</footer>

<?php if ($message != "") { ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: '<?php echo addslashes($message); ?>',
                confirmButtonColor: '#7c3aed'
            });
        });
    </script>
<?php } ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById("event_date");
    if (dateInput) {
        const minDate = new Date();
        minDate.setDate(minDate.getDate() + 2);
        dateInput.min = minDate.toISOString().split("T")[0];
    }

    const phoneInput = document.getElementById("contact_phone");
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
        });
    }

    const startTimeEl = document.getElementById("start_time");
    const endTimeEl = document.getElementById("end_time");
    const durationDisplay = document.getElementById("duration_display");

    function calculateHours() {
        if (!startTimeEl.value || !endTimeEl.value) return;

        const start = startTimeEl.value.split(":");
        const end = endTimeEl.value.split(":");

        const startMins = parseInt(start[0], 10) * 60 + parseInt(start[1], 10);
        const endMins = parseInt(end[0], 10) * 60 + parseInt(end[1], 10);

        if (endMins <= startMins) {
            durationDisplay.innerHTML = `Total Event Working Hours: <span class='text-danger fw-bold'><i class='fa-solid fa-triangle-exclamation me-1'></i> End time must be after start time.</span>`;
            return;
        }

        const totalMins = endMins - startMins;
        const hours = Math.floor(totalMins / 60);
        const minutes = totalMins % 60;

        let displayString = `${hours} hr${hours !== 1 ? 's' : ''}`;
        if (minutes > 0) {
            displayString += ` ${minutes} min${minutes !== 1 ? 's' : ''}`;
        }

        durationDisplay.innerHTML = `Total Event Working Hours: <span class='text-success fw-bold'><i class='fa-solid fa-clock me-1'></i> ${displayString}</span>`;
    }

    if (startTimeEl && endTimeEl) {
        startTimeEl.addEventListener('change', calculateHours);
        endTimeEl.addEventListener('change', calculateHours);
    }
});
</script>

</body>
</html>