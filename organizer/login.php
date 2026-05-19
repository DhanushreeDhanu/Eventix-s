<?php
session_start();
include('../config/db.php');

$message = "";

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $message = "Please enter email and password.";
    } else {
        // Enforce role separation directly in the prepared query blueprint
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = 'organizer' LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // FIXED: Enforce strict white-list verification mapping ('approved'). 
            // Blocks pending registrations from logging in prematurely before administrative validation.
            if ($user['status'] === 'rejected') {
                $message = "Your organizer account has been blocked or rejected.";
            } elseif ($user['status'] !== 'approved') {
                $message = "Your account registration is currently pending admin review.";
            } elseif (password_verify($password, $user['password'])) {
                // Provision application authorization tokens safely into active session memory
                $_SESSION['organizer_id'] = $user['id'];
                $_SESSION['organizer_name'] = $user['name'];
                $_SESSION['organizer_email'] = $user['email'];

                header("Location: dashboard.php");
                exit();
            } else {
                $message = "Incorrect password.";
            }
        } else {
            $message = "Organizer account not found.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Organizer Login | Eventix</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(59,130,246,.25), transparent 35%),
                radial-gradient(circle at bottom right, rgba(6,182,212,.18), transparent 30%),
                linear-gradient(135deg, #0f172a, #111827, #1e293b);
            font-family: "Segoe UI", sans-serif;
            color: white;
        }
        .navbar {
            background: rgba(15,23,42,.9);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(255,255,255,.08);
        }
        .brand-box {
            width: 42px; height: 42px; border-radius: 14px;
            background: linear-gradient(135deg, #3b82f6, #06b6d4);
            display: inline-grid; place-items: center; margin-right: 10px;
        }
        .wrapper { padding: 70px 0; }
        .main-card {
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.12);
            backdrop-filter: blur(18px);
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 30px 90px rgba(0,0,0,.45);
        }
        .left-panel {
            padding: 55px 45px;
            background:
                radial-gradient(circle at top left, rgba(59,130,246,.35), transparent 35%),
                radial-gradient(circle at bottom right, rgba(6,182,212,.25), transparent 30%);
        }
        .left-panel h1 { font-size: 42px; font-weight: 900; line-height: 1.1; }
        .left-panel p { color: #94a3b8; margin-top: 20px; line-height: 1.7; font-size: 16px; }
        .feature { display: flex; gap: 14px; margin-top: 22px; color: #cbd5e1; }
        .feature i { color: #38bdf8; margin-top: 4px; }
        .form-panel { background: rgba(255,255,255,.98); color: #111827; padding: 55px; }
        label { font-weight: 700; margin-bottom: 8px; color: #1e293b; }
        .form-control {
            padding: 14px 16px; border-radius: 15px; border: 1px solid #cbd5e1;
            background-color: #f8fafc; color: #0f172a; transition: all 0.2s ease;
        }
        .form-control:focus {
            background-color: #ffffff; border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37,99,235,0.15);
        }
        .btn-main {
            background: linear-gradient(135deg, #2563eb, #06b6d4);
            border: none; padding: 15px; border-radius: 999px;
            color: white; font-weight: 900; transition: transform 0.2s ease, opacity 0.2s ease;
        }
        .btn-main:hover { transform: translateY(-1px); opacity: 0.95; color: white; }
        footer { background: rgba(15,23,42,.9); color: #94a3b8; text-align: center; padding: 15px; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark px-4 py-3">
    <a class="navbar-brand fw-bold d-flex align-items-center" href="../index.php">
        <span class="brand-box"><i class="fa-solid fa-bolt"></i></span>
        Eventix Organizer
    </a>
    <div>
        <a href="../index.php" class="btn btn-outline-light btn-sm me-2 px-3 rounded-pill">Home</a>
        <a href="register.php" class="btn btn-light btn-sm px-3 rounded-pill">Register</a>
    </div>
</nav>

<section class="wrapper">
    <div class="container">
        <div class="row main-card">

            <div class="col-lg-5 left-panel">
                <h1>Organizer Control Panel</h1>
                <p>Log in to configure upcoming technical fests, deploy tracks, map student attendances, and execute safe bounty clearance actions.</p>

                <div class="feature">
                    <i class="fa-solid fa-square-poll-vertical"></i>
                    <span>Publish Managed Academic Metrics</span>
                </div>
                <div class="feature">
                    <i class="fa-solid fa-user-shield"></i>
                    <span>Audit Volunteer Attendance Metrics</span>
                </div>
                <div class="feature">
                    <i class="fa-solid fa-wallet"></i>
                    <span>Execute Directly Inspected UPI Payouts</span>
                </div>
            </div>

            <div class="col-lg-7 form-panel">
                <h3 class="text-center fw-bold mb-2">Organizer Login</h3>
                <p class="text-center text-muted mb-4">Access your professional platform environment</p>

                <?php if ($message != "") { ?>
                    <script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Authentication Alert',
                            text: '<?php echo addslashes($message); ?>',
                            confirmButtonColor: '#2563eb'
                        });
                    </script>
                <?php } ?>

                <form method="POST" autocomplete="off">
                    <input type="text" name="fakeuser_enc" style="display:none" autocomplete="off">
                    <input type="password" name="fakepass_enc" style="display:none" autocomplete="off">

                    <div class="mb-3">
                        <label for="email">Account Registered Email Address</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="organizer@institution.edu" required>
                    </div>

                    <div class="mb-4">
                        <label for="password">Account Security Password</label>
                        <div class="position-relative">
                            <input type="password" name="password" id="password" class="form-control" placeholder="••••••••••••" autocomplete="current-password" required>
                            <i class="fa-solid fa-eye position-absolute top-50 end-0 translate-middle-y me-3" style="cursor:pointer; color: #64748b;" onclick="togglePassword(this)"></i>
                        </div>
                    </div>

                    <button type="submit" name="login" class="btn btn-main w-100 shadow-sm">
                        <i class="fa-solid fa-right-to-bracket me-2"></i> Initialize Security Dashboard Session
                    </button>

                    <div class="text-center mt-4">
                        <small class="text-muted">
                            New to our management cluster? 
                            <a href="register.php" class="text-decoration-none fw-bold text-primary">Request structural credentials here</a>
                        </small>
                    </div>
                </form>
            </div>

        </div>
    </div>
</section>

<footer>
    © 2026 Eventix | Premium Infrastructure Operations Gateway
</footer>

<script>
// FIXED: Context-independent runtime DOM node logic
function togglePassword(iconElement) {
    const field = document.getElementById("password");
    if (!field) return;

    if (field.type === "password") {
        field.type = "text";
        iconElement.classList.replace("fa-eye", "fa-eye-slash");
    } else {
        field.type = "password";
        iconElement.classList.replace("fa-eye-slash", "fa-eye");
    }
}
</script>

</body>
</html>