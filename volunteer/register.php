<?php
session_start();
include('../config/db.php');

$message = "";

if (isset($_POST['register'])) {

    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $college = trim($_POST['college']);
    $skills = trim($_POST['skills']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (!preg_match("/^[A-Za-z ]+$/", $full_name)) {
        $message = "Full name should contain only letters.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {
        $message = "Phone number must contain exactly 10 digits only.";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
    } else {

        $check = $conn->prepare("SELECT id FROM volunteers WHERE email=?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "Email already registered.";
        } else {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO volunteers
                (full_name, email, phone, college, skills, password, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())");

            $stmt->bind_param(
                "ssssss",
                $full_name,
                $email,
                $phone,
                $college,
                $skills,
                $hashed_password
            );

            if ($stmt->execute()) {

                echo "
                <!DOCTYPE html>
                <html lang='en'>
                <head>
                    <meta charset='UTF-8'>
                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <title>Registration Success | Eventix</title>
                    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                </head>
                <body>
                    <script>
                        Swal.fire({
                            title: 'Registration Successful!',
                            text: 'Your volunteer account has been created successfully.',
                            icon: 'success',
                            confirmButtonText: 'Login Now',
                            confirmButtonColor: '#7c3aed',
                            allowOutsideClick: false
                        }).then(() => {
                            window.location.href = 'login.php';
                        });
                    </script>
                </body>
                </html>";
                exit();

            } else {
                $message = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Volunteer Register | Eventix</title>
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
            padding: 55px;
        }

        .section-heading {
            color: #4f46e5;
            font-weight: 900;
            margin: 28px 0 18px;
            border-left: 5px solid #7c3aed;
            padding-left: 12px;
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

        textarea.form-control {
            resize: vertical;
        }

        .btn-main {
            background: linear-gradient(135deg, #7c3aed, #ec4899);
            border: none;
            padding: 15px;
            border-radius: 999px;
            color: white;
            font-weight: 900;
            margin-top: 10px;
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
        <a href="login.php" class="btn btn-light btn-sm">Login</a>
    </div>
</nav>

<section class="wrapper">
    <div class="container">
        <div class="row main-card">

            <div class="col-lg-5 left-panel">
                <h1>Join events. Build experience.</h1>

                <p>
                    Register as a volunteer, explore college events,
                    join opportunities, upload your payment scanner,
                    and track attendance/payment status.
                </p>

                <div class="feature">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Join events posted by organizers.</span>
                </div>

                <div class="feature">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Track joined events from your dashboard.</span>
                </div>

                <div class="feature">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Upload UPI scanner for volunteer payment.</span>
                </div>

                <div class="feature">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Get attendance and payment status updates.</span>
                </div>
            </div>

            <div class="col-lg-7 form-panel">

                <h3 class="text-center fw-bold mb-2">Volunteer Registration</h3>
                <p class="text-center text-muted mb-4">
                    Create your volunteer account
                </p>

                <?php if ($message != "") { ?>
                    <script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Registration Failed!',
                            text: '<?php echo $message; ?>',
                            confirmButtonText: 'Try Again',
                            confirmButtonColor: '#7c3aed',
                            timer: 3500,
                            timerProgressBar: true
                        });
                    </script>
                <?php } ?>

                <form method="POST" autocomplete="off">

                    <input type="text" name="fakeuser" style="display:none">
                    <input type="password" name="fakepass" style="display:none">

                    <h5 class="section-heading">Personal Details</h5>

                    <div class="mb-3">
                        <label>Full Name</label>
                        <input type="text"
                               name="full_name"
                               class="form-control"
                               placeholder="Enter your full name"
                               pattern="[A-Za-z ]+"
                               title="Only letters are allowed"
                               autocomplete="off"
                               required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Email Address</label>
                            <input type="email"
                                   name="email"
                                   class="form-control"
                                   placeholder="Enter valid email address"
                                   autocomplete="off"
                                   autocorrect="off"
                                   autocapitalize="off"
                                   spellcheck="false"
                                   required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Phone Number</label>
                            <input type="text"
                                   name="phone"
                                   class="form-control"
                                   placeholder="10 digit phone number"
                                   pattern="[0-9]{10}"
                                   maxlength="10"
                                   title="Enter exactly 10 digits only"
                                   autocomplete="off"
                                   required>
                        </div>
                    </div>

                    <h5 class="section-heading">Education & Skills</h5>

                    <div class="mb-3">
                        <label>College / Institution</label>
                        <input type="text"
                               name="college"
                               class="form-control"
                               placeholder="Enter college or institution name"
                               autocomplete="off"
                               required>
                    </div>

                    <div class="mb-3">
                        <label>Skills / Interests</label>
                        <textarea name="skills"
                                  class="form-control"
                                  rows="4"
                                  placeholder="Example: Crowd management, Technical support, Public speaking..."
                                  required></textarea>
                    </div>

                    <h5 class="section-heading">Security Details</h5>

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label>Password</label>
                            <div class="position-relative">
                                <input type="password"
                                       name="password"
                                       id="password"
                                       class="form-control"
                                       placeholder="Create password"
                                       autocomplete="new-password"
                                       required>

                                <i class="fa-solid fa-eye position-absolute top-50 end-0 translate-middle-y me-3"
                                   style="cursor:pointer;"
                                   onclick="togglePassword('password', this)"></i>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Confirm Password</label>
                            <div class="position-relative">
                                <input type="password"
                                       name="confirm_password"
                                       id="confirm_password"
                                       class="form-control"
                                       placeholder="Confirm password"
                                       autocomplete="new-password"
                                       required>

                                <i class="fa-solid fa-eye position-absolute top-50 end-0 translate-middle-y me-3"
                                   style="cursor:pointer;"
                                   onclick="togglePassword('confirm_password', this)"></i>
                            </div>
                        </div>

                    </div>

                    <button type="submit"
                            name="register"
                            class="btn btn-main w-100">
                        <i class="fa-solid fa-user-plus me-2"></i>
                        Register as Volunteer
                    </button>

                    <div class="text-center mt-4">
                        <small class="text-muted">
                            Already have an account?
                            <a href="login.php"
                               class="text-decoration-none fw-bold">
                                Login here
                            </a>
                        </small>
                    </div>

                </form>

            </div>
        </div>
    </div>
</section>

<footer>
    © 2026 Eventix | Volunteer Registration
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