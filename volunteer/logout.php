<?php
session_start();

// FIXED: Inject explicit security parameters to stop back-button rendering of cached visual layouts
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// FIXED: Cleanly wipe the active variable scope stack from system memory
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logging Out | Eventix Volunteer</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(circle at top left, rgba(124,58,237,.35), transparent 35%),
                radial-gradient(circle at bottom right, rgba(34,211,238,.22), transparent 30%),
                linear-gradient(135deg, #050816, #15162c, #4f46e5);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            overflow: hidden;
        }

        .logout-box {
            text-align: center;
            color: white;
            padding: 20px;
        }

        .icon-circle {
            width: 100px; height: 100px;
            margin: 0 auto 25px auto;
            border-radius: 50%;
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.18);
            backdrop-filter: blur(12px);
            display: flex; align-items: center; justify-content: center;
            font-size: 42px; color: #22d3ee;
        }

        h1 { font-size: 34px; font-weight: 900; margin-bottom: 10px; letter-spacing: -0.01em; }
        p { color: #d1d5db; margin-bottom: 28px; font-size: 16px; }

        .progress-container { width: 280px; height: 8px; background: rgba(255,255,255,.15); border-radius: 999px; overflow: hidden; margin: auto; }
        .progress-bar {
            height: 100%; width: 100%;
            background: linear-gradient(90deg, #22d3ee, #7c3aed, #ec4899);
            animation: loadBar 1.8s linear forwards;
        }

        @keyframes loadBar {
            from { width: 100%; }
            to { width: 0%; }
        }
    </style>
</head>
<body>

<div class="logout-box">
    <div class="icon-circle">
        <i class="fa-solid fa-right-from-bracket"></i>
    </div>

    <h1>Disconnecting Session...</h1>
    <p>Please wait while Eventix securely flushes your volunteer session credentials.</p>

    <div class="progress-container">
        <div class="progress-bar"></div>
    </div>
</div>

<script>
    // Reduced delay slightly to keep the application snappy while maintaining the visual confirmation
    setTimeout(() => {
        Swal.fire({
            title: 'Session Terminated',
            text: 'You have safely detached your profile from the volunteer network.',
            icon: 'success',
            confirmButtonText: 'Return Home',
            confirmButtonColor: '#7c3aed',
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then(() => {
            // FIXED: Replaced standard href routing with location.replace to scrub the back-button state context
            window.location.replace('../index.php');
        });
    }, 1800);
</script>

</body>
</html>