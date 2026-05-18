<?php
session_start();
include('../config/db.php');

$message = "";

/*
IMPORTANT:
Make sure you have ONE admin user in `users` table:

role = admin

Example SQL:
INSERT INTO users (name, email, phone, password, role)
VALUES (
    'Admin',
    'admin@eventix.com',
    '9999999999',
    '$2y$10$examplehashedpassword',
    'admin'
);

Recommended:
Create admin password using PHP:
password_hash('admin123', PASSWORD_DEFAULT)
*/

if (isset($_POST['login'])) {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $message = "Please fill in all fields.";
    } else {

        $stmt = $conn->prepare("SELECT * FROM users WHERE email=? AND role='admin' LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $admin = $result->fetch_assoc();

            if (password_verify($password, $admin['password'])) {

                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['name'];

                header("Location: dashboard.php");
                exit();

            } else {
                $message = "Incorrect password.";
            }

        } else {
            $message = "Admin account not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login | Eventix</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(37,99,235,.20), transparent 30%),
                radial-gradient(circle at bottom right, rgba(6,182,212,.18), transparent 30%),
                linear-gradient(135deg, #0f172a, #111827, #1e293b);
            font-family: "Segoe UI", sans-serif;
            color: white;
        }

        .navbar {
            background: rgba(15,23,42,.92);
            backdrop-filter: blur(12px);
        }

        .brand-box {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            background: linear-gradient(135deg, #2563eb, #06b6d4);
            display: inline-grid;
            place-items: center;
            margin-right: 10px;
        }

        .wrapper {
            padding: 70px 0;
        }

        .main-card {
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.12);
            backdrop-filter: blur(18px);
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 30px 90px rgba(0,0,0,.35);
        }

        .left-panel {
            padding: 55px 45px;
            background:
                radial-gradient(circle at top left, rgba(37,99,235,.35), transparent 35%),
                radial-gradient(circle at bottom right, rgba(6,182,212,.25), transparent 30%);
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
            color: #38bdf8;
            margin-top: 4px;
        }

        .form-panel {
            background: rgba(255,255,255,.98);
            color: #111827;
            padding: 55px;
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
            background: linear-gradient(135deg, #2563eb, #06b6d4);
            border: none;
            padding: 15px;
            border-radius: 999px;
            color: white;
            font-weight: 900;
        }

        .btn-main:hover {
            color: white;
            opacity: .95;
        }

        footer {
            background: rgba(15,23,42,.92);
            color: #94a3b8;
            text-align: center;
            padding: 15px;
        }

        .security-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
            border-radius: 16px;
            padding: 14px;
            margin-bottom: 20px;
            font-size: 14px;
        }
    </style>
</head>

<body>

<nav class="navbar navbar-dark px-4 py-3">
    <a class="navbar-brand fw-bold d-flex align-items-center" href="../index.php">
        <span class="brand-box">
            <i class="fa-solid fa-shield-halved"></i>
        </span>
        Eventix Admin
    </a>

    <div>
        <a href="../index.php" class="btn btn-outline-light btn-sm">Home</a>
    </div>
</nav>

<section class="wrapper">
    <div class="container">
        <div class="row main-card">

            <div class="col-lg-5 left-panel">
                <h1>Admin Control Center</h1>

                <p>
                    Monitor organizers, volunteers, events, payment timelines,
                    fraud detection, and overall Eventix platform integrity.
                </p>

                <div class="feature">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Monitor all organizer accounts</span>
                </div>

                <div class="feature">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Block suspicious organizers</span>
                </div>

                <div class="feature">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Track volunteer registrations</span>
                </div>

                <div class="feature">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Manage platform-wide events</span>
                </div>

                <div class="feature">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Ensure payment accountability</span>
                </div>
            </div>

            <div class="col-lg-7 form-panel">

                <h3 class="text-center fw-bold mb-2">Admin Login</h3>
                <p class="text-center text-muted mb-4">
                    Secure administrator access
                </p>

                <div class="security-box">
                    <i class="fa-solid fa-lock me-2"></i>
                    Restricted access for authorized administrators only.
                </div>

                <?php if ($message != "") { ?>
                    <script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Login Failed!',
                            text: '<?php echo $message; ?>',
                            confirmButtonColor: '#2563eb'
                        });
                    </script>
                <?php } ?>

                <form method="POST" autocomplete="off">

                    <!-- Anti browser autofill -->
                    <input type="text" name="fakeuser" style="display:none">
                    <input type="password" name="fakepass" style="display:none">

                    <div class="mb-3">
                        <label>Email Address</label>
                        <input type="email"
                               name="email"
                               class="form-control"
                               placeholder="Enter admin email"
                               autocomplete="off"
                               required>
                    </div>

                    <div class="mb-4">
                        <label>Password</label>
                        <div class="position-relative">
                            <input type="password"
                                   name="password"
                                   id="password"
                                   class="form-control"
                                   placeholder="Enter password"
                                   autocomplete="new-password"
                                   required>

                            <i class="fa-solid fa-eye position-absolute top-50 end-0 translate-middle-y me-3"
                               style="cursor:pointer;"
                               onclick="togglePassword()"></i>
                        </div>
                    </div>

                    <button type="submit"
                            name="login"
                            class="btn btn-main w-100">
                        <i class="fa-solid fa-right-to-bracket me-2"></i>
                        Login as Admin
                    </button>

                </form>

            </div>

        </div>
    </div>
</section>

<footer>
    © 2026 Eventix | Admin Secure Login
</footer>

<script>
function togglePassword() {
    const field = document.getElementById("password");
    const icon = document.querySelector(".fa-eye, .fa-eye-slash");

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
</script>

</body>
</html>