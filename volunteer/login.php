<?php
session_start();
include('../config/db.php');

// Redirect authenticated active user sessions to clear interface overhead
if (isset($_SESSION['volunteer_id'])) {
    header("Location: dashboard.php");
    exit();
}

$message = "";

// ----------------------------------------------------
// AUTHENTICATION INTERACTIVE DISPATCHER
// ----------------------------------------------------
if (isset($_POST['login'])) {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please supply a valid communication email layout format.";
    } else {
        $stmt = $conn->prepare("SELECT id, full_name, password FROM volunteers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $volunteer = $result->fetch_assoc();

            // FIXED: Unified tracking returns to defeat timing-based account harvesting operations
            if (password_verify($password, $volunteer['password'])) {
                $_SESSION['volunteer_id']   = intval($volunteer['id']);
                $_SESSION['volunteer_name'] = $volunteer['full_name'];

                echo "
                <!DOCTYPE html>
                <html lang='en'>
                <head>
                    <meta charset='UTF-8'>
                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <title>Login Success | Eventix</title>
                    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                </head>
                <body>
                    <script>
                        Swal.fire({
                            title: 'Access Granted!',
                            text: 'Synchronizing volunteer dashboard configuration matrices.',
                            icon: 'success',
                            confirmButtonText: 'Initialize Workspace',
                            confirmButtonColor: '#7c3aed',
                            allowOutsideClick: false
                        }).then(() => {
                            window.location.href = 'dashboard.php';
                        });
                    </script>
                </body>
                </html>";
                $stmt->close();
                exit();
            }
        }
        
        // Generic response statement deployment
        $message = "Invalid email address or operational password credential string.";
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Volunteer Login | Eventix</title>
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
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: white;
        }

        .navbar {
            background: rgba(5,8,22,.9);
            backdrop-filter: blur(15px);
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .brand-box {
            width: 42px; height: 42px; border-radius: 14px;
            background: linear-gradient(135deg, #7c3aed, #22d3ee);
            display: inline-grid; place-items: center; margin-right: 10px;
        }

        .wrapper { padding: 70px 0; }

        .main-card {
            background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.18);
            backdrop-filter: blur(18px); border-radius: 32px; overflow: hidden;
            box-shadow: 0 30px 90px rgba(0,0,0,.35);
        }

        .left-panel {
            padding: 55px 45px;
            background:
                radial-gradient(circle at top left, rgba(124,58,237,.45), transparent 35%),
                radial-gradient(circle at bottom right, rgba(34,211,238,.25), transparent 30);
        }
        .left-panel h1 { font-size: 42px; font-weight: 900; letter-spacing: -0.02em; }
        .left-panel p { color: #d1d5db; margin-top: 20px; line-height: 1.7; }

        .feature { display: flex; gap: 14px; margin-top: 18px; color: #d1d5db; align-items: center; }
        .feature i { color: #22d3ee; }

        .form-panel { background: rgba(255,255,255,.98); color: #111827; padding: 55px; }
        .form-title { font-weight: 900; letter-spacing: -0.01em; }

        label { font-size: 0.85rem; font-weight: 700; margin-bottom: 8px; display: block; text-transform: uppercase; letter-spacing: 0.5px; color: #4b5563; }
        .form-control { padding: 14px 16px; border-radius: 12px; border: 1px solid #d1d5db; font-size: 0.95rem; }
        .form-control:focus { box-shadow: 0 0 0 3px rgba(124,58,237,0.15); border-color: #7c3aed; }

        .btn-main {
            background: linear-gradient(135deg, #7c3aed, #ec4899);
            border: none; padding: 15px; border-radius: 999px; color: white !important;
            font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;
            transition: opacity 0.15s ease-in-out; cursor: pointer;
        }
        .btn-main:hover { opacity: 0.95; }

        footer { background: rgba(5,8,22,.9); color: #9ca3af; text-align: center; padding: 15px; border-top: 1px solid rgba(255,255,255,0.05); }
    </style>
</head>
<body>

<nav class="navbar navbar-dark px-4 py-3">
    <a class="navbar-brand fw-bold d-flex align-items-center" href="../index.php">
        <span class="brand-box"><i class="fa-solid fa-bolt"></i></span> Eventix Volunteer
    </a>
    <div>
        <a href="../index.php" class="btn btn-outline-light btn-sm me-2">Home</a>
        <a href="register.php" class="btn btn-light btn-smfw-semibold">Register Node</a>
    </div>
</nav>

<section class="wrapper">
    <div class="container">
        <div class="row main-card">

            <div class="col-lg-5 left-panel d-flex flex-column justify-content-center">
                <h1>Welcome back, volunteer.</h1>
                <p>
                    Authenticate your connection stream to scan available event rosters, log completion checkpoints, and monitor verification settlement pipelines securely.
                </p>

                <div class="feature"><i class="fa-solid fa-square-check"></i><span>Audit upcoming infrastructure events.</span></div>
                <div class="feature"><i class="fa-solid fa-square-check"></i><span>Track attendance approval structures.</span></div>
                <div class="feature"><i class="fa-solid fa-square-check"></i><span>Monitor real-time payout status flags.</span></div>
                <div class="feature"><i class="fa-solid fa-square-check"></i><span>Access centralized workspace matrix.</span></div>
            </div>

            <div class="col-lg-7 form-panel">
                <h3 class="text-center form-title mb-2">Volunteer Authentication</h3>
                <p class="text-center text-muted mb-5">Supply authorization tokens below to unlock system context</p>

                <form method="POST" autocomplete="on">
                    <div class="mb-4">
                        <label>Secure Identifier (Email)</label>
                        <input type="email" name="email" class="form-control" placeholder="name@domain.com" autocomplete="username" required>
                    </div>

                    <div class="mb-5">
                        <label>Guard Pass Phrase (Password)</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••••••" autocomplete="current-password" required>
                    </div>

                    <button type="submit" name="login" class="btn btn-main w-100">
                        <i class="fa-solid fa-right-to-bracket me-2"></i> Establish Session Connection
                    </button>

                    <div class="text-center mt-4">
                        <small class="text-muted">
                            New component vector? 
                            <a href="register.php" class="text-decoration-none fw-bold text-primary">Request System Registration</a>
                        </small>
                    </div>
                </form>
            </div>

        </div>
    </div>
</section>

<footer>
    &copy; 2026 Eventix Infrastructure Framework &bull; Secure Access Boundary Control.
</footer>

<?php if ($message != "") { ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Authentication Denied',
            text: '<?php echo $message; ?>',
            confirmButtonText: 'Retry Submission',
            confirmButtonColor: '#7c3aed',
            background: '#ffffff',
            color: '#111827'
        });
    </script>
<?php } ?>

</body>
</html>