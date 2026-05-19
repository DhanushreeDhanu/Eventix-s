<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['volunteer_id'])) {
    header("Location: login.php");
    exit();
}

$volunteer_id = $_SESSION['volunteer_id'];
$message = "";

$stmt = $conn->prepare("SELECT * FROM volunteers WHERE id=?");
$stmt->bind_param("i", $volunteer_id);
$stmt->execute();
$volunteer = $vstmt = $stmt->get_result()->fetch_assoc();

if (isset($_POST['update_profile'])) {

    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $college = trim($_POST['college']);
    $skills = trim($_POST['skills']);

    if (!preg_match("/^[A-Za-z ]+$/", $full_name)) {
        $message = "Full name should contain only letters.";
    } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {
        $message = "Phone number must contain exactly 10 digits only.";
    } elseif (empty($college) || empty($skills)) {
        $message = "College and skills are required.";
    } else {

        $update = $conn->prepare("UPDATE volunteers 
                                  SET full_name=?, phone=?, college=?, skills=? 
                                  WHERE id=?");
        $update->bind_param("ssssi", $full_name, $phone, $college, $skills, $volunteer_id);

        if ($update->execute()) {
            $_SESSION['volunteer_name'] = $full_name;

            echo "
            <!DOCTYPE html>
            <html lang='en'>
            <head>
                <meta charset='UTF-8'>
                <title>Profile Updated | Eventix</title>
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            </head>
            <body>
                <script>
                    Swal.fire({
                        title: 'Profile Updated!',
                        text: 'Your volunteer profile has been updated successfully.',
                        icon: 'success',
                        confirmButtonText: 'Back to Dashboard',
                        confirmButtonColor: '#5bc0be',
                        allowOutsideClick: false
                    }).then(() => {
                        window.location.href = 'dashboard.php';
                    });
                </script>
            </body>
            </html>";
            exit();
        } else {
            $message = "Profile update failed. Please try again.";
        }
    }
}

if (isset($_POST['update_password'])) {

    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (strlen($new_password) < 6) {
        $message = "Password must be at least 6 characters.";
    } elseif ($new_password !== $confirm_password) {
        $message = "Passwords do not match.";
    } else {

        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $pass_update = $conn->prepare("UPDATE volunteers SET password=? WHERE id=?");
        $pass_update->bind_param("si", $hashed_password, $volunteer_id);

        if ($pass_update->execute()) {
            echo "
            <!DOCTYPE html>
            <html lang='en'>
            <head>
                <meta charset='UTF-8'>
                <title>Password Updated | Eventix</title>
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            </head>
            <body>
                <script>
                    Swal.fire({
                        title: 'Password Updated!',
                        text: 'Your password has been changed successfully.',
                        icon: 'success',
                        confirmButtonText: 'Continue',
                        confirmButtonColor: '#5bc0be',
                        allowOutsideClick: false
                    }).then(() => {
                        window.location.href = 'edit-profile.php';
                    });
                </script>
            </body>
            </html>";
            exit();
        } else {
            $message = "Password update failed.";
        }
    }
}

function safe($value) {
    return !empty($value) ? htmlspecialchars($value) : "";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile | Eventix</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            /* Premium Corporate Dark Blue System */
            --bg-main: #0b1329;         
            --bg-card: #1c2541;         
            --border-color: #2e3b5e;    
            
            --primary: #5bc0be;         /* Ice Blue */
            --primary-hover: #48a6a4;
            --text-main: #f1f5f9;       
            --text-muted: #94a3b8;      
        }

        body {
            background-color: var(--bg-main);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Prevent system overrides from destroying visibility */
        .main-wrapper, h1, h4, label, strong, .text-light {
            color: var(--text-main) !important;
        }
        p, small, span, .text-muted {
            color: var(--text-muted) !important;
        }

        /* Top Navigation Alignment */
        .topbar {
            background-color: var(--bg-card);
            padding: 0.75rem 2rem;
            border-bottom: 1px solid var(--border-color);
        }

        .brand-icon {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            background: var(--primary);
            color: var(--bg-main);
            display: inline-grid;
            place-items: center;
            font-size: 0.9rem;
            font-weight: bold;
        }

        /* Strictly Managed Grid Container */
        .main-wrapper {
            flex: 1;
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .page-header-block {
            padding-bottom: 1.25rem;
            margin-bottom: 2rem;
            border-bottom: 1px solid var(--border-color);
        }

        /* Card Layout */
        .card-box {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 1.75rem;
            height: 100%;
        }

        .section-title {
            color: var(--primary) !important;
            font-size: 1.2rem;
            font-weight: 700;
            letter-spacing: -0.01em;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title i {
            color: var(--primary) !important;
        }

        /* Form Inputs Realignment */
        label {
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 0.4rem;
            display: block;
        }

        .form-control {
            background-color: #0b1329;
            border: 1px solid var(--border-color);
            color: var(--text-main) !important;
            padding: 0.65rem 1rem;
            border-radius: 6px;
            font-size: 0.9rem;
        }

        .form-control:focus {
            background-color: #0b1329;
            border-color: var(--primary);
            box-shadow: 0 0 0 0.15rem rgba(91, 192, 190, 0.15);
        }

        .form-control::placeholder {
            color: #4b5563;
        }

        textarea.form-control {
            min-height: 100px;
            resize: none;
        }

        /* Clean Alert Banner */
        .email-note {
            background: rgba(91, 192, 190, 0.05);
            border: 1px dashed var(--border-color);
            border-radius: 8px;
            padding: 0.9rem 1.1rem;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
        }

        .eye-icon {
            cursor: pointer;
            color: var(--text-muted);
            transition: color 0.15s;
        }
        .eye-icon:hover {
            color: var(--primary);
        }

        /* Buttons Structural Logic */
        .btn-corporate {
            background: transparent;
            color: var(--text-main) !important;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 0.4rem 1rem;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.15s ease-in-out;
        }
        .btn-corporate:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: var(--primary);
        }

        .btn-primary-custom {
            background: var(--primary);
            color: var(--bg-main) !important;
            border: 1px solid var(--primary);
            border-radius: 6px;
            padding: 0.6rem 1.2rem;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.15s ease-in-out;
        }
        .btn-primary-custom:hover {
            background: var(--primary-hover);
            border-color: var(--primary-hover);
        }

        .btn-dark-custom {
            background: #0b1329;
            color: var(--text-main) !important;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 0.6rem 1.2rem;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.15s ease-in-out;
        }
        .btn-dark-custom:hover {
            background: #111c3a;
            border-color: var(--primary);
        }

        footer {
            background: var(--bg-card);
            border-top: 1px solid var(--border-color);
            color: var(--text-muted) !important;
            text-align: center;
            padding: 1.25rem;
            font-size: 0.85rem;
            margin-top: auto;
        }
    </style>
</head>

<body>

<nav class="topbar d-flex justify-content-between align-items-center">
    <a href="dashboard.php" class="d-flex align-items-center gap-2 text-decoration-none fw-bold text-white fs-5">
        <span class="brand-icon"><i class="fa-solid fa-bolt" style="color: #0b1329 !important;"></i></span>
        Eventix
    </a>

    <div class="d-flex gap-2">
        <a href="dashboard.php" class="btn-corporate">Dashboard</a>
        <a href="available-events.php" class="btn-corporate d-none d-sm-inline-block">Browse Events</a>
        <a href="logout.php" class="btn btn-sm btn-danger px-3 fw-medium" style="border-radius:6px; display:inline-flex; align-items:center;">Logout</a>
    </div>
</nav>

<div class="main-wrapper">

    <header class="page-header-block">
        <h1 class="fw-bold fs-3 mb-1">Edit Profile</h1>
        <p class="mb-0 small">Securely keep your personal credentials and operational criteria up to date.</p>
    </header>

    <?php if ($message != "") { ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: '<?php echo $message; ?>',
                confirmButtonText: 'Review Input',
                confirmButtonColor: '#5bc0be',
                background: '#1c2541',
                color: '#f1f5f9'
            });
        </script>
    <?php } ?>

    <div class="row g-4">

        <div class="col-lg-7">
            <div class="card-box">
                <h4 class="section-title">
                    <i class="fa-solid fa-user-pen"></i>
                    Personal Details
                </h4>

                <div class="email-note">
                    <span class="text-white d-block mb-1"><strong>Registered Email Identifier:</strong></span>
                    <span class="text-white"><?php echo safe($volunteer['email']); ?></span>
                    <small class="d-block mt-1 text-muted">System locked entry. Changes are restricted for auditing safety protocols.</small>
                </div>

                <form method="POST" autocomplete="off">

                    <div class="mb-3">
                        <label>Full Name</label>
                        <input type="text"
                               name="full_name"
                               class="form-control"
                               value="<?php echo safe($volunteer['full_name']); ?>"
                               pattern="[A-Za-z ]+"
                               title="Only letters are allowed"
                               autocomplete="off"
                               required>
                    </div>

                    <div class="mb-3">
                        <label>Phone Number Reference</label>
                        <input type="text"
                               name="phone"
                               class="form-control"
                               value="<?php echo safe($volunteer['phone']); ?>"
                               pattern="[0-9]{10}"
                               maxlength="10"
                               title="Enter exactly 10 digits only"
                               autocomplete="off"
                               required>
                    </div>

                    <div class="mb-3">
                        <label>College / Institutional Affiliation</label>
                        <input type="text"
                               name="college"
                               class="form-control"
                               value="<?php echo safe($volunteer['college']); ?>"
                               autocomplete="off"
                               required>
                    </div>

                    <div class="mb-4">
                        <label>Functional Core Skills / Core Interests</label>
                        <textarea name="skills"
                                  class="form-control"
                                  placeholder="List technical areas, event fields, management properties..."
                                  required><?php echo safe($volunteer['skills']); ?></textarea>
                    </div>

                    <button type="submit"
                            name="update_profile"
                            class="btn btn-primary-custom w-100">
                        <i class="fa-solid fa-save me-1.5"></i>
                        Commit Profile Metrics
                    </button>

                </form>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="d-flex flex-column gap-4 style-height-lock" style="height:100%;">
                
                <div class="card-box">
                    <h4 class="section-title">
                        <i class="fa-solid fa-lock"></i>
                        Change Passphrase
                    </h4>

                    <form method="POST" autocomplete="off">

                        <div class="mb-3">
                            <label>New Secret Password</label>
                            <div class="position-relative">
                                <input type="password"
                                       name="new_password"
                                       id="new_password"
                                       class="form-control pe-5"
                                       placeholder="Minimum 6 characters"
                                       autocomplete="new-password"
                                       required>

                                <i class="fa-solid fa-eye eye-icon position-absolute top-50 end-0 translate-middle-y me-3"
                                   onclick="togglePassword('new_password', this)"></i>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label>Confirm Verified Password</label>
                            <div class="position-relative">
                                <input type="password"
                                       name="confirm_password"
                                       id="confirm_password"
                                       class="form-control pe-5"
                                       placeholder="Re-enter password string"
                                       autocomplete="new-password"
                                       required>

                                <i class="fa-solid fa-eye eye-icon position-absolute top-50 end-0 translate-middle-y me-3"
                                   onclick="togglePassword('confirm_password', this)"></i>
                            </div>
                        </div>

                        <button type="submit"
                                name="update_password"
                                class="btn btn-dark-custom w-100">
                            <i class="fa-solid fa-key me-1.5"></i>
                            Update Guard Phrase
                        </button>

                    </form>
                </div>

                <div class="card-box d-flex align-items-center justify-content-center py-4">
                    <div class="text-center w-100">
                        <div class="small text-muted mb-3.5">Alternative Payment Ledger Settings</div>
                        <a href="upload-payment-qr.php" class="btn btn-corporate w-100 py-2.5">
                            <i class="fa-solid fa-qrcode me-1.5"></i>
                            Manage Settlement QR Card
                        </a>
                    </div>
                </div>

            </div>
        </div>

    </div>

</div>

<footer>
    &copy; 2026 Eventix Infrastructure Framework &bull; Secure User Profile Control.
</footer>

<script>
function togglePassword(fieldId, icon) {
    const field = document.getElementById(fieldId);

    if (field.type === "password") {
        field.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        field.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}

document.querySelector('input[name="phone"]').addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
});

document.querySelector('input[name="full_name"]').addEventListener('input', function() {
    this.value = this.value.replace(/[^A-Za-z ]/g, '');
});
</script>

</body>
</html> 