<?php
session_start();

// 1. Unset specific admin session variables
if (isset($_SESSION['admin_id'])) {
    unset($_SESSION['admin_id']);
}
if (isset($_SESSION['admin_name'])) {
    unset($_SESSION['admin_name']);
}

// 2. Clear out the entire session array just to be safe
$_SESSION = array();

// 3. Destroy the session on the server
session_destroy();

// 4. Redirect the user cleanly back to the admin login page
header("Location: login.php");
exit();
?>