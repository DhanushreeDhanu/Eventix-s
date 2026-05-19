<?php
session_start();
include('config/db.php');+

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage-organizers.php");
    exit();
}

$organizer_id = intval($_GET['id']);

$stmt = $conn->prepare("UPDATE users SET organizer_status='blocked' WHERE id=? AND role='organizer'");
$stmt->bind_param("i", $organizer_id);

if ($stmt->execute()) {
    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Organizer Blocked!',
                text: 'Organizer account blocked successfully.',
                confirmButtonColor: '#2563eb'
            }).then(() => {
                window.location.href='manage-organizers.php';
            });
        });
    </script>";
} else {
    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Failed!',
                text: 'Something went wrong.',
                confirmButtonColor: '#dc2626'
            }).then(() => {
                window.location.href='manage-organizers.php';
            });
        });
    </script>";
}
?>