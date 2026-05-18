<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
$message = "";

$stmt = $conn->prepare("SELECT * FROM users WHERE id=? AND role='admin'");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

if (isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);

    if (!preg_match("/^[A-Za-z ]+$/", $name)) {
        $message = "Name should contain only letters.";
    } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {
        $message = "Phone number must be exactly 10 digits.";
    } else {
        $update = $conn->prepare("UPDATE users SET name=?, phone=? WHERE id=? AND role='admin'");
        $update->bind_param("ssi", $name, $phone, $admin_id);

        if ($update->execute()) {
            $_SESSION['admin_name'] = $name;

            echo "
            <!DOCTYPE html>
            <html>
            <head>
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            </head>
            <body>
                <script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Profile Updated!',
                        text: 'Admin profile updated successfully.',
                        confirmButtonColor: '#2563eb'
                    }).then(() => {
                        window.location.href='settings.php';
                    });
                </script>
            </body>
            </html>";
            exit();
        } else {
            $message = "Profile update failed.";
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

        $update_pass = $conn->prepare("UPDATE users SET password=? WHERE id=? AND role='admin'");
        $update_pass->bind_param("si", $hashed_password, $admin_id);

        if ($update_pass->execute()) {
            echo "
            <!DOCTYPE html>
            <html>
            <head>
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            </head>
            <body>
                <script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Password Updated!',
                        text: 'Admin password changed successfully.',
                        confirmButtonColor: '#2563eb'
                    }).then(() => {
                        window.location.href='settings.php';
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
    <title>Settings | Eventix Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            background: #f4f7fb;
            font-family: "Segoe UI", sans-serif;
            color: #111827;
        }

        .topbar {
            background: #111827;
            padding: 16px 35px;
        }

        .brand {
            color: white;
            font-size: 24px;
            font-weight: 900;
            text-decoration: none;
        }

        .brand i {
            background: linear-gradient(135deg, #2563eb, #06b6d4);
            padding: 10px;
            border-radius: 14px;
            margin-right: 8px;
        }

        .page {
            max-width: 1100px;
            margin: auto;
            padding: 35px 15px;
        }

        .hero {
            background: linear-gradient(135deg, #111827, #2563eb);
            color: white;
            border-radius: 28px;
            padding: 32px;
            margin-bottom: 25px;
            box-shadow: 0 18px 50px rgba(17,24,39,.22);
        }

        .hero h1 {
            font-weight: 900;
        }

        .hero p {
            color: #dbeafe;
            margin-bottom: 0;
        }

        .card-box {
            background: white;
            border-radius: 24px;
            padding: 28px;
            height: 100%;
            box-shadow: 0 8px 30px rgba(0,0,0,.07);
            border: 1px solid #e5e7eb;
        }

        .section-title {
            font-weight: 900;
            color: #2563eb;
            margin-bottom: 20px;
        }

        label {
            font-weight: 700;
            margin-bottom: 8px;
        }

        .form-control {
            padding: 14px 16px;
            border-radius: 15px;
            border: 1px solid #d1d5db;
        }

        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 .2rem rgba(37,99,235,.15);
        }

        .btn-main {
            background: linear-gradient(135deg, #2563eb, #06b6d4);
            border: none;
            color: white;
            border-radius: 999px;
            padding: 13px 22px;
            font-weight: 900;
        }

        .btn-main:hover {
            color: white;
            opacity: .95;
        }

        .btn-dark-main {
            background: #111827;
            border: none;
            color: white;
            border-radius: 999px;
            padding: 13px 22px;
            font-weight: 900;
        }

        .btn-dark-main:hover {
            background: #1f2937;
            color: white;
        }

        .info-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
            border-radius: 18px;
            padding: 16px;
            margin-bottom: 20px;
        }

        .warning-box {
            background: #fff7ed;
            border: 1px solid #fed7aa;
            color: #9a3412;
            border-radius: 18px;
            padding: 16px;
            margin-top: 20px;
        }

        .eye-icon {
            cursor: pointer;
            color: #6b7280;
        }

        footer {
            background: #111827;
            color: #9ca3af;
            text-align: center;
            padding: 15px;
            margin-top: 35px;
        }
    </style>
</head>

<body>

<nav class="topbar d-flex justify-content-between align-items-center flex-wrap gap-3">
    <a href="dashboard.php" class="brand">
        <i class="fa-solid fa-bolt"></i>
        Eventix Admin
    </a>

    <div>
        <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">Dashboard</a>
        <a href="manage-events.php" class="btn btn-outline-info btn-sm me-2">Events</a>
        <a href="manage-organizers.php" class="btn btn-outline-warning btn-sm me-2">Organizers</a>
        <a href="manage-volunteers.php" class="btn btn-outline-success btn-sm me-2">Volunteers</a>
        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
</nav>

<div class="page">

    <section class="hero">
        <h1>Admin Settings</h1>
        <p>Update your admin profile and secure your password.</p>
    </section>

    <?php if ($message != "") { ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Oops!',
                text: '<?php echo $message; ?>',
                confirmButtonColor: '#2563eb'
            });
        </script>
    <?php } ?>

    <div class="row g-4">

        <div class="col-lg-7">
            <div class="card-box">
                <h4 class="section-title">
                    <i class="fa-solid fa-user-shield me-2"></i>
                    Admin Profile
                </h4>

                <div class="info-box">
                    <strong>Email:</strong>
                    <?php echo safe($admin['email']); ?><br>
                    <small>Email cannot be changed from settings.</small>
                </div>

                <form method="POST" autocomplete="off">

                    <div class="mb-3">
                        <label>Admin Name</label>
                        <input type="text"
                               name="name"
                               class="form-control"
                               value="<?php echo safe($admin['name']); ?>"
                               pattern="[A-Za-z ]+"
                               title="Only letters are allowed"
                               required>
                    </div>

                    <div class="mb-4">
                        <label>Phone Number</label>
                        <input type="text"
                               name="phone"
                               class="form-control"
                               value="<?php echo safe($admin['phone']); ?>"
                               pattern="[0-9]{10}"
                               maxlength="10"
                               title="Enter exactly 10 digits only"
                               required>
                    </div>

                    <button type="submit"
                            name="update_profile"
                            class="btn btn-main w-100">
                        <i class="fa-solid fa-save me-2"></i>
                        Save Profile
                    </button>

                </form>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card-box">
                <h4 class="section-title">
                    <i class="fa-solid fa-lock me-2"></i>
                    Change Password
                </h4>

                <form method="POST" autocomplete="off">

                    <div class="mb-3">
                        <label>New Password</label>
                        <div class="position-relative">
                            <input type="password"
                                   name="new_password"
                                   id="new_password"
                                   class="form-control"
                                   placeholder="Enter new password"
                                   autocomplete="new-password"
                                   required>

                            <i class="fa-solid fa-eye eye-icon position-absolute top-50 end-0 translate-middle-y me-3"
                               onclick="togglePassword('new_password', this)"></i>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label>Confirm Password</label>
                        <div class="position-relative">
                            <input type="password"
                                   name="confirm_password"
                                   id="confirm_password"
                                   class="form-control"
                                   placeholder="Confirm new password"
                                   autocomplete="new-password"
                                   required>

                            <i class="fa-solid fa-eye eye-icon position-absolute top-50 end-0 translate-middle-y me-3"
                               onclick="togglePassword('confirm_password', this)"></i>
                        </div>
                    </div>

                    <button type="submit"
                            name="update_password"
                            class="btn btn-dark-main w-100">
                        <i class="fa-solid fa-key me-2"></i>
                        Update Password
                    </button>

                </form>

                <div class="warning-box">
                    <strong>Security Tip:</strong><br>
                    Use a strong password and never share admin login details.
                </div>
            </div>
        </div>

    </div>

</div>

<footer>
    © 2026 Eventix | Admin Settings
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

document.querySelector('input[name="name"]').addEventListener('input', function() {
    this.value = this.value.replace(/[^A-Za-z ]/g, '');
});
</script>

</body>
</html>