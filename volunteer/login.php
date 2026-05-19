<?php
session_start();
include('../config/db.php');

$message = "";

if (isset($_POST['login'])) {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email address.";
    } else {

        $stmt = $conn->prepare("SELECT * FROM volunteers WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $volunteer = $result->fetch_assoc();

            if (password_verify($password, $volunteer['password'])) {

                $_SESSION['volunteer_id'] = $volunteer['id'];
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
                            title: 'Login Successful!',
                            text: 'Welcome back to Eventix Volunteer Dashboard.',
                            icon: 'success',
                            confirmButtonText: 'Continue',
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
                $message = "Incorrect password.";
            }

        } else {
            $message = "Volunteer account not found.";
        }
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
            padding: 70px 0;
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
            padding: 55px;
        }

        .form-title {
            font-weight: 900;
        }

        label {
            font-weight: 700;
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            padding: 14px 16px;
            border-radius: 15px;
            border: 1px solid #d1d5db;
        }

        .btn-main {
            background: linear-gradient(135deg, #7c3aed, #ec4899);
            border: none;
            padding: 15px;
            border-radius: 999px;
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
        }
    </style>
</head>

<body>

<nav class="navbar navbar-dark px-4 py-3">
    <a class="navbar-brand fw-bold d-flex align-items-center" href="../index.php">
        <span class="brand-box">
            <i class="fa-solid fa-bolt"></i>
        </span>
        Eventix Volunteer
    </a>

    <div>
        <a href="../index.php" class="btn btn-outline-light btn-sm me-2">Home</a>
        <a href="register.php" class="btn btn-light btn-sm">Register</a>
    </div>
</nav>

<section class="wrapper">
    <div class="container">
        <div class="row main-card">

            <div class="col-lg-5 left-panel">
                <h1>Welcome back volunteer.</h1>

                <p>
                    Login to browse events, join volunteer opportunities,
                    upload your payment scanner, track attendance,
                    and manage your volunteer journey professionally.
                </p>

                <div class="feature">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Browse upcoming organizer events.</span>
                </div>

                <div class="feature">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Track attendance approval.</span>
                </div>

                <div class="feature">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Monitor payment status.</span>
                </div>

                <div class="feature">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Professional volunteer dashboard.</span>
                </div>
            </div>

            <div class="col-lg-7 form-panel">

                <h3 class="text-center form-title mb-2">Volunteer Login</h3>
                <p class="text-center text-muted mb-4">
                    Access your volunteer dashboard
                </p>

               <?php if ($message != "") { ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Login Failed!',
            text: '<?php echo $message; ?>',
            confirmButtonText: 'Try Again',
            confirmButtonColor: '#7c3aed',
            background: '#ffffff',
            color: '#111827',
            timer: 3500,
            timerProgressBar: true
        });
    </script>
<?php } ?>

                <form method="POST" autocomplete="off">

                    <input type="text" name="fakeuser" style="display:none">
                    <input type="password" name="fakepass" style="display:none">

                    <div class="mb-4">
                        <label>Email Address</label>
                        <input type="email"
                               name="email"
                               class="form-control"
                               placeholder="Enter registered email"
                               autocomplete="off"
                               autocorrect="off"
                               autocapitalize="off"
                               spellcheck="false"
                               required>
                    </div>

                    <div class="mb-4">
                        <label>Password</label>
                        <input type="password"
                               name="password"
                               class="form-control"
                               placeholder="Enter password"
                               autocomplete="new-password"
                               required>
                    </div>

                    <button type="submit"
                            name="login"
                            class="btn btn-main w-100">
                        <i class="fa-solid fa-right-to-bracket me-2"></i>
                        Login to Dashboard
                    </button>

                    <div class="text-center mt-4">
                        <small class="text-muted">
                            Don’t have an account?
                            <a href="register.php"
                               class="text-decoration-none fw-bold">
                                Register here
                            </a>
                        </small>
                    </div>

                </form>

            </div>
        </div>
    </div>
</section>

<footer>
    © 2026 Eventix | Volunteer Login
</footer>

</body>
</html>