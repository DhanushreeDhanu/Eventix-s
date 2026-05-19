<?php
session_start();
include('../config/db.php');

$message = "";
$alert_type = ""; // Initialized to prevent undefined variable errors in JS

if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (!preg_match("/^[A-Za-z ]+$/", $name)) {
        $message = "Name should contain only letters and spaces.";
        $alert_type = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email address.";
        $alert_type = "error";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
        $alert_type = "error";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters.";
        $alert_type = "error";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = "Email already exists.";
            $alert_type = "error";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO users (name, email, phone, password, role, status)
                 VALUES (?, ?, ?, ?, 'organizer', 'pending')"
            );
            $stmt->bind_param("ssss", $name, $email, $phone, $hashed_password);

            if ($stmt->execute()) {
                // Let SweetAlert handle the success message and routing dynamically below
                $message = "Organizer registered successfully!";
                $alert_type = "success";
            } else {
                $message = "Registration failed.";
                $alert_type = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Organizer Register | Eventix</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #050816, #15162c, #4f46e5);
            font-family: "Segoe UI", sans-serif;
            color: white;
        }

        .navbar {
            background: rgba(5,8,22,.88);
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

        .register-wrapper {
            min-height: calc(100vh - 75px);
            display: flex;
            align-items: center;
            padding: 50px 0;
        }

        .register-card {
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.18);
            backdrop-filter: blur(18px);
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 30px 90px rgba(0,0,0,.35);
        }

        .info-panel {
            padding: 55px;
            background:
                radial-gradient(circle at top left, rgba(124,58,237,.45), transparent 35%),
                radial-gradient(circle at bottom right, rgba(34,211,238,.25), transparent 30%);
        }

        .info-panel h1 {
            font-size: 46px;
            font-weight: 900;
            line-height: 1;
        }

        .info-panel p {
            color: #d1d5db;
            margin-top: 20px;
            font-size: 18px;
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
            background: rgba(255,255,255,.96);
            color: #111827;
            padding: 45px;
        }

        .form-panel h3 {
            font-weight: 900;
        }

        .form-control {
            padding: 13px;
            border-radius: 14px;
        }

        .input-group .form-control {
            border-radius: 14px 0 0 14px;
        }

        .input-group .btn {
            border-radius: 0 14px 14px 0;
        }

        .btn-main {
            background: linear-gradient(135deg, #7c3aed, #ec4899);
            border: none;
            padding: 13px;
            border-radius: 999px;
            color: white;
            font-weight: 800;
            transition: .3s;
        }

        .btn-main:hover {
            color: white;
            transform: translateY(-2px);
        }

        small {
            display: block;
            margin-top: 4px;
        }

        footer {
            background: rgba(5,8,22,.88);
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
        Eventix
    </a>

    <div>
        <a href="../index.php" class="btn btn-outline-light btn-sm me-2">Home</a>
        <a href="login.php" class="btn btn-light btn-sm">Organizer Login</a>
    </div>
</nav>

<section class="register-wrapper">
    <div class="container">
        <div class="row register-card">

            <div class="col-lg-6 info-panel">
                <h1>Start managing events with Eventix.</h1>

                <p>
                    Create events, track volunteers,
                    and manage your full event workflow.
                </p>

                <div class="feature">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Create detailed events.</span>
                </div>

                <div class="feature">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Set volunteer requirements.</span>
                </div>

                <div class="feature">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Manage event progress.</span>
                </div>

                <div class="feature">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Track joined volunteers.</span>
                </div>
            </div>

            <div class="col-lg-6 form-panel">
                <h3 class="text-center mb-2">Organizer Registration</h3>
                <p class="text-center text-muted mb-4">
                    Create your organizer account
                </p>

                <form method="POST" autocomplete="off" onsubmit="return validateForm();">

                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text"
                               name="name"
                               id="name"
                               class="form-control"
                               placeholder="Enter full name"
                               autocomplete="off"
                               onkeypress="return onlyLetters(event)"
                               onpaste="return false"
                               required>
                        <small class="text-danger" id="nameError"></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email"
                               name="email"
                               id="email"
                               class="form-control"
                               placeholder="Enter email address"
                               autocomplete="off"
                               required>
                        <small class="text-danger" id="emailError"></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text"
                               name="phone"
                               id="phone"
                               class="form-control"
                               placeholder="Enter phone number"
                               autocomplete="off"
                               maxlength="10"
                               onkeypress="return onlyNumbers(event)"
                               onpaste="return false"
                               required>
                        <small class="text-danger" id="phoneError"></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password"
                                   name="password"
                                   id="password"
                                   class="form-control"
                                   placeholder="Enter password"
                                   autocomplete="new-password"
                                   required>

                            <button type="button"
                                    class="btn btn-outline-secondary"
                                    onclick="togglePassword('password','eye1')">
                                <i class="fa-solid fa-eye" id="eye1"></i>
                            </button>
                        </div>
                        <small class="text-danger" id="passwordError"></small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <input type="password"
                                   name="confirm_password"
                                   id="confirm_password"
                                   class="form-control"
                                   placeholder="Confirm password"
                                   autocomplete="new-password"
                                   required>

                            <button type="button"
                                    class="btn btn-outline-secondary"
                                    onclick="togglePassword('confirm_password','eye2')">
                                <i class="fa-solid fa-eye" id="eye2"></i>
                            </button>
                        </div>
                        <small class="text-danger" id="confirmPasswordError"></small>
                    </div>

                    <button type="submit"
                            name="register"
                            class="btn btn-main w-100">
                        Register as Organizer
                    </button>
                </form>

                <p class="text-center mt-4">
                    Already have an account?
                    <a href="login.php">Login here</a>
                </p>
            </div>

        </div>
    </div>
</section>

<footer>
    © 2026 Eventix | Organizer Registration
</footer>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// SweeAlert event rendering
<?php if ($message != "" && $alert_type != "") { ?>
    Swal.fire({
        icon: '<?php echo $alert_type; ?>',
        title: '<?php echo ($alert_type == "success") ? "Registration Successful!" : "Registration Failed"; ?>',
        text: '<?php echo $message; ?>',
        confirmButtonColor: '#7c3aed',
        confirmButtonText: '<?php echo ($alert_type == "success") ? "Go to Login" : "Try Again"; ?>'
    }).then(() => {
        <?php if ($alert_type == "success") { ?>
            window.location.href = 'login.php';
        <?php } ?>
    });
<?php } ?>

function onlyLetters(event) {
    const char = String.fromCharCode(event.which);
    if (!/[a-zA-Z ]/.test(char)) {
        event.preventDefault();
        return false;
    }
    return true;
}

function onlyNumbers(event) {
    const char = String.fromCharCode(event.which);
    if (!/[0-9]/.test(char)) {
        event.preventDefault();
        return false;
    }
    return true;
}

function togglePassword(inputId, eyeId) {
    const input = document.getElementById(inputId);
    const eye = document.getElementById(eyeId);

    if (input.type === "password") {
        input.type = "text";
        eye.className = "fa-solid fa-eye-slash";
    } else {
        input.type = "password";
        eye.className = "fa-solid fa-eye";
    }
}

function validateForm() {
    let valid = true;

    const name = document.getElementById("name").value.trim();
    const email = document.getElementById("email").value.trim();
    const phone = document.getElementById("phone").value.trim();
    const password = document.getElementById("password").value;
    const confirmPassword = document.getElementById("confirm_password").value;

    document.getElementById("nameError").innerText = "";
    document.getElementById("emailError").innerText = "";
    document.getElementById("phoneError").innerText = "";
    document.getElementById("passwordError").innerText = "";
    document.getElementById("confirmPasswordError").innerText = "";

    if (name.length < 3 || !/^[A-Za-z ]+$/.test(name)) {
        document.getElementById("nameError").innerText = "Name must contain only letters and spaces.";
        valid = false;
    }

    const emailPattern = /^[^ ]+@[^ ]+\.[a-z]{2,}$/i;
    if (!email.match(emailPattern)) {
        document.getElementById("emailError").innerText = "Enter valid email.";
        valid = false;
    }

    const phonePattern = /^[0-9]{10}$/;
    if (!phone.match(phonePattern)) {
        document.getElementById("phoneError").innerText = "Phone must be 10 digits.";
        valid = false;
    }

    if (password.length < 8) {
        document.getElementById("passwordError").innerText = "Password must be at least 8 characters.";
        valid = false;
    }

    if (password !== confirmPassword) {
        document.getElementById("confirmPasswordError").innerText = "Passwords do not match.";
        valid = false;
    }

    return valid;
}
</script>

</body>
</html>