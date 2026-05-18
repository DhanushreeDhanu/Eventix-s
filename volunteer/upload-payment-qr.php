<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['volunteer_id'])) {
    header("Location: login.php");
    exit();
}

$volunteer_id = $_SESSION['volunteer_id'];
$message = "";

/* Fetch volunteer */
$stmt = $conn->prepare("SELECT * FROM volunteers WHERE id=?");
$stmt->bind_param("i", $volunteer_id);
$stmt->execute();
$volunteer = $stmt->get_result()->fetch_assoc();

/* Upload QR */
if (isset($_POST['upload_qr'])) {

    if (!isset($_FILES['payment_qr']) || $_FILES['payment_qr']['error'] != 0) {
        $message = "Please select a valid QR image.";
    } else {

        $file_name = $_FILES['payment_qr']['name'];
        $file_tmp = $_FILES['payment_qr']['tmp_name'];
        $file_size = $_FILES['payment_qr']['size'];

        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $message = "Only JPG, PNG, JPEG, and WEBP files are allowed.";
        } elseif ($file_size > 2 * 1024 * 1024) {
            $message = "QR image must be less than 2MB.";
        } else {

            $upload_dir = "../uploads/volunteer_qr/";

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $new_file_name = "volunteer_" . $volunteer_id . "_" . time() . "." . $ext;
            $upload_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $upload_path)) {

                $update = $conn->prepare("UPDATE volunteers SET payment_qr=? WHERE id=?");
                $update->bind_param("si", $new_file_name, $volunteer_id);

                if ($update->execute()) {
                    echo "
                    <!DOCTYPE html>
                    <html lang='en'>
                    <head>
                        <meta charset='UTF-8'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                        <title>QR Uploaded | Eventix</title>
                        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                    </head>
                    <body>
                        <script>
                            Swal.fire({
                                title: 'QR Uploaded Successfully!',
                                text: 'Your payment scanner has been saved.',
                                icon: 'success',
                                confirmButtonText: 'Back to Dashboard',
                                confirmButtonColor: '#7c3aed',
                                allowOutsideClick: false
                            }).then(() => {
                                window.location.href = 'dashboard.php';
                            });
                        </script>
                    </body>
                    </html>";
                    exit();
                } else {
                    $message = "Failed to save QR in database.";
                }

            } else {
                $message = "Failed to upload QR image.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Payment QR | Eventix</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

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
            padding: 60px 0;
        }

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
                radial-gradient(circle at top left, rgba(124,58,237,.45), transparent 35%),
                radial-gradient(circle at bottom right, rgba(34,211,238,.25), transparent 30%);
        }

        .left-panel h1 {
            font-size: 42px;
            font-weight: 900;
        }

        .left-panel p {
            color: #d1d5db;
            margin-top: 20px;
            line-height: 1.7;
        }

        .feature {
            display: flex;
            gap: 14px;
            margin-top: 18px;
            color: #d1d5db;
        }

        .feature i {
            color: #22d3ee;
            margin-top: 4px;
        }

        .form-panel {
            background: rgba(255,255,255,.98);
            color: #111827;
            padding: 50px;
        }

        label {
            font-weight: 700;
            margin-bottom: 8px;
            display: block;
            color: #1f2937;
        }

        .form-control {
            padding: 14px 16px;
            border-radius: 15px;
            border: 1px solid #d1d5db;
        }

        .preview-box {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 22px;
            padding: 22px;
            text-align: center;
            margin-bottom: 25px;
        }

        .qr-img {
            max-width: 220px;
            max-height: 220px;
            border-radius: 18px;
            border: 1px solid #e5e7eb;
            object-fit: cover;
        }

        .note-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
            border-radius: 18px;
            padding: 16px;
            line-height: 1.7;
        }

        .btn-main {
            background: linear-gradient(135deg, #7c3aed, #ec4899);
            border: none;
            border-radius: 999px;
            padding: 14px;
            color: white;
            font-weight: 900;
        }

        .btn-main:hover {
            color: white;
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
        Eventix Volunteer
    </a>

    <div>
        <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">Dashboard</a>
        <a href="joined-events.php" class="btn btn-light btn-sm me-2">My Events</a>
        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
</nav>

<section class="wrapper">
    <div class="container">
        <div class="row main-card">

            <div class="col-lg-5 left-panel">
                <h1>Upload your payment scanner.</h1>

                <p>
                    Add your UPI QR code so organizers can send your volunteer payment
                    after attendance approval.
                </p>

                <div class="feature">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Upload JPG, PNG, JPEG, or WEBP QR image.</span>
                </div>

                <div class="feature">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Used only for organizer payment transfer.</span>
                </div>

                <div class="feature">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>You can replace your QR anytime.</span>
                </div>
            </div>

            <div class="col-lg-7 form-panel">

                <h3 class="text-center fw-bold mb-2">Payment QR Scanner</h3>
                <p class="text-center text-muted mb-4">
                    Upload your personal UPI payment scanner
                </p>

                <?php if ($message != "") { ?>
                    <script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops!',
                            text: '<?php echo $message; ?>',
                            confirmButtonColor: '#7c3aed'
                        });
                    </script>
                <?php } ?>

                <div class="preview-box">
                    <h5 class="fw-bold mb-3">Current QR</h5>

                    <?php if (!empty($volunteer['payment_qr'])) { ?>
                        <img src="../uploads/volunteer_qr/<?php echo htmlspecialchars($volunteer['payment_qr']); ?>"
                             class="qr-img"
                             alt="Payment QR">
                    <?php } else { ?>
                        <p class="text-muted mb-0">
                            No payment QR uploaded yet.
                        </p>
                    <?php } ?>
                </div>

                <form method="POST" enctype="multipart/form-data">

                    <div class="mb-4">
                        <label>Choose QR Image</label>
                        <input type="file"
                               name="payment_qr"
                               class="form-control"
                               accept="image/*"
                               required>
                    </div>

                    <div class="note-box mb-4">
                        <strong>Note:</strong><br>
                        Organizer will view this QR only after you join an event.
                        Payment is processed after attendance approval.
                    </div>

                    <button type="submit"
                            name="upload_qr"
                            class="btn btn-main w-100">
                        <i class="fa-solid fa-upload me-2"></i>
                        Upload / Replace QR
                    </button>

                </form>

            </div>
        </div>
    </div>
</section>

<footer>
    © 2026 Eventix | Volunteer Payment QR
</footer>

</body>
</html>