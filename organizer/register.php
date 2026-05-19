<?php
session_start();
include('../config/db.php');

$message = "";
$alert_type = ""; 

if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Comprehensive backend validation matrix logic
    if (!preg_match("/^[A-Za-z ]+$/", $name)) {
        $message = "Name should contain only letters and spaces.";
        $alert_type = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email address formatting pattern.";
        $alert_type = "error";
    } elseif (!preg_match("/^[0-9]{10}$/", $phone)) { // FIXED: Enforced a strong numeric constraint check on the backend
        $message = "Phone number must be exactly 10 digits.";
        $alert_type = "error";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
        $alert_type = "error";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters long.";
        $alert_type = "error";
    } else {
        // Evaluate pre-existing identity constraints via clean database lookup bindings
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = "This email address is already registered.";
            $alert_type = "error";
        } else {
            // Apply standard bcrypt hashing functions to store passwords safely
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO users (name, email, phone, password, role, status)
                 VALUES (?, ?, ?, ?, 'organizer', 'pending')"
            );
            $stmt->bind_param("ssss", $name, $email, $phone, $hashed_password);

            if ($stmt->execute()) {
                $message = "Organizer application registered successfully! Awaiting administrative approval.";
                $alert_type = "success";
            } else {
                $message = "Critical infrastructure save error encountered during registration.";
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
    <title>Organizer Registration | Eventix</title>
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
            border-bottom: 1px solid rgba(255,255,255,.08);
        }
        .brand-box {
            width: 42px; height: 42px; border-radius: 14px;
            background: linear-gradient(135deg, #7c3aed, #22d3ee);
            display: inline-grid; place-items: center; margin-right: 10px;
        }
        .register-wrapper {
            min-height: calc(100vh - 75px);
            display: flex; align-items: center; padding: 50px 0;
        }
        .register-card {
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.18);
            backdrop-filter: blur(18px);
            border-radius: 30px; overflow: hidden;
            box-shadow: 0 30px 90px rgba(0,0,0,.35);
        }
        .info-panel {
            padding: 55px;
            background:
                radial-gradient(circle at top left, rgba(124,58,237,.45), transparent 35%),
                radial-gradient(circle at bottom right, rgba(34,211,238,.25), transparent 30%);
        }
        .info-panel h1 { font-size: 46px; font-weight: 900; line-height: 1.1; }
        .info-panel p { color: #d1d5db; margin-top: 20px; font-size: 17px; line-height: 1.6; }
        .feature { display: flex; gap: 14px; margin-top: 22px; color: #cbd5e1; }
        .feature i { color: #22d3ee; margin-top: 4px; }
        .form-panel { background: rgba(255,255,255,.96); color: #111827; padding: 45px; }
        .form-panel h3 { font-weight: 900; color: #0f172a; }
        label { font-weight: 700; color: #334155; margin-bottom: 4px; font-size: 14px; }
        .form-control {
            padding: 12px 14px; border-radius: 14px; border: 1px solid #cbd5e1;
            background-color: #f8fafc; color: #0f172a; transition: all 0.2s ease;
        }
        .form-control:focus {
            background-color: #fff; border-color: #7c3aed;
            box-shadow: 0 0 0 4px rgba(124,58,237,0.15);
        }
        .input-group .form-control { border-radius: 14px 0 0 14px; }
        .input-group .btn { border-radius: 0 14px 14px 0; border-color: #cbd5e1; background: #f1f5f9; color: #64748b; }
        .input-group .btn:hover { background: #e2e8f0; }
        .btn-main {
            background: linear-gradient(135deg, #7c3aed, #ec4899);
            border: none; padding: 14px; border-radius: 999px;
            color: white; font-weight: 800; transition: all 0.2s;
        }
        .btn-main:hover { color: white; transform: translateY(-2px); box-shadow: 0 4px 15px rgba(124,58,237,0.3); }
        .error-msg { display: block; font-size: 12px; font-weight: 600; margin-top: 4px; color: #dc2626; }
        footer { background: rgba(5,8,22,.88); color: #9ca3af; text-align: center; padding: 15px; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark px-4 py-3">
    <a class="navbar-brand fw-bold d-flex align-items-center" href="../index.php">
        <span class="brand-box"><i class="fa-solid fa-bolt"></i></span> Eventix
    </a>
    <div>
        <a href="../index.php" class="btn btn-outline-light btn-sm me-2 px-3 rounded-pill">Home</a>
        <a href="login.php" class="btn btn-light btn-sm px-3 rounded-pill">Organizer Login</a>
    </div>
</nav>

<section class="register-wrapper">
    <div class="container">
        <div class="row register-card">

            <div class="col-lg-6 info-panel d-flex flex-column justify-content-center">
                <h1>Deploy and manage your next structural event.</h1>
                <p>Register an administrative control profile to configure schedules, track volunteer application clusters, and clear digital payout balances.</p>

                <div class="feature"><i class="fa-solid fa-square-check"></i><span>Generate custom track frameworks</span></div>
                <div class="feature"><i class="fa-solid fa-square-check"></i><span>Audit active student attendance metrics</span></div>
                <div class="feature"><i class="fa-solid fa-square-check"></i><span>Configure volunteer capacity ceilings</span></div>
            </div>

            <div class="col-lg-6 form-panel">
                <h3 class="text-center mb-1">Organizer Portal Request</h3>
                <p class="text-center text-muted mb-4" style="font-size: 14px;">Establish an administrative verification token</p>

                <form method="POST" autocomplete="off" onsubmit="return validateForm();">
                    <div class="mb-3">
                        <label for="name">Legal Full Name</label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="John Doe" required>
                        <span class="error-msg" id="nameError"></span>
                    </div>

                    <div class="mb-3">
                        <label for="email">Institutional Email Address</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="organizer@domain.edu" required>
                        <span class="error-msg" id="emailError"></span>
                    </div>

                    <div class="mb-3">
                        <label for="phone">10-Digit Mobile Number</label>
                        <input type="text" name="phone" id="phone" class="form-control" placeholder="9876543210" maxlength="10" required>
                        <span class="error-msg" id="phoneError"></span>
                    </div>

                    <div class="mb-3">
                        <label for="password">Account Security Password</label>
                        <div class="input-group">
                            <input type="password" name="password" id="password" class="form-control" placeholder="Minimum 8 characters" autocomplete="new-password" required>
                            <button type="button" class="btn" onclick="togglePassword('password', this)"><i class="fa-solid fa-eye"></i></button>
                        </div>
                        <span class="error-msg" id="passwordError"></span>
                    </div>

                    <div class="mb-4">
                        <label for="confirm_password">Verify Access Password</label>
                        <div class="input-group">
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Re-enter password" autocomplete="new-password" required>
                            <button type="button" class="btn" onclick="togglePassword('confirm_password', this)"><i class="fa-solid fa-eye"></i></button>
                        </div>
                        <span class="error-msg" id="confirmPasswordError"></span>
                    </div>

                    <button type="submit" name="register" class="btn btn-main w-100 shadow-sm">
                        Submit Structural Credentials
                    </button>
                </form>

                <p class="text-center mt-4 mb-0" style="font-size: 14px;">
                    Already retain workspace tokens? <a href="login.php" class="fw-bold text-decoration-none">Login here</a>
                </p>
            </div>

        </div>
    </div>
</section>

<footer>
    © 2026 Eventix | Distributed Identity Provisioning Interface
</footer>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Execute sweetAlert routing hooks depending on backend parameters cleanly
<?php if ($message != "" && $alert_type != "") { ?>
    Swal.fire({
        icon: '<?php echo $alert_type; ?>',
        title: '<?php echo ($alert_type == "success") ? "Profile Saved" : "Execution Halted"; ?>',
        text: '<?php echo addslashes($message); ?>',
        confirmButtonColor: '#7c3aed',
        confirmButtonText: '<?php echo ($alert_type == "success") ? "Proceed to Login" : "Re-evaluate"; ?>',
        allowOutsideClick: false
    }).then(() => {
        <?php if ($alert_type == "success") { ?>
            window.location.href = 'login.php';
        <?php } ?>
    });
<?php } ?>

// FIXED: Use dynamic input event interceptors to prevent bypass actions (such as drag-and-drop or right-click pasting)
document.getElementById('name').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^a-zA-Z ]/g, '');
});

document.getElementById('phone').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});

function togglePassword(inputId, btnElement) {
    const field = document.getElementById(inputId);
    const icon = btnElement.querySelector('i');
    if (!field || !icon) return;

    if (field.type === "password") {
        field.type = "text";
        icon.className = "fa-solid fa-eye-slash";
    } else {
        field.type = "password";
        icon.className = "fa-solid fa-eye";
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
        document.getElementById("nameError").innerText = "Name must contain only alphabets and spaces (min 3 chars).";
        valid = false;
    }

    if (!/^[^ ]+@[^ ]+\.[a-z]{2,}$/i.test(email)) {
        document.getElementById("emailError").innerText = "Please supply a valid communication email address.";
        valid = false;
    }

    if (!/^[0-9]{10}$/.test(phone)) {
        document.getElementById("phoneError").innerText = "Phone configuration parameter must match a 10-digit mask.";
        valid = false;
    }

    if (password.length < 8) {
        document.getElementById("passwordError").innerText = "Security parameters dictate a minimum length of 8 characters.";
        valid = false;
    }

    if (password !== confirmPassword) {
        document.getElementById("confirmPasswordError").innerText = "Tokens do not match security verify requirements.";
        valid = false;
    }

    return valid;
}
</script>
</body>
</html>