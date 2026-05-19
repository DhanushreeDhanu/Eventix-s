<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {

    header("Location: ../login.php");
    exit();
}

// Total organizers
$organizers = $conn->query(
    "SELECT COUNT(*) AS total 
     FROM users 
     WHERE role='organizer'"
)->fetch_assoc()['total'];

// Total volunteers
$volunteers = $conn->query(
    "SELECT COUNT(*) AS total 
     FROM users 
     WHERE role='volunteer'"
)->fetch_assoc()['total'];

// Total students/users
$students = $conn->query(
    "SELECT COUNT(*) AS total 
     FROM users"
)->fetch_assoc()['total'];

// Total events
$events = $conn->query(
    "SELECT COUNT(*) AS total 
     FROM events"
)->fetch_assoc()['total'];

// Total registrations
$registrations = $conn->query(
    "SELECT COUNT(*) AS total 
     FROM volunteer_events"
)->fetch_assoc()['total'];

?>

<!DOCTYPE html>
<html>
<head>

<title>Admin Dashboard | Eventix</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f4f7fc;
    font-family:Arial;
}

.navbar{
    background:#111827;
}

.container{
    margin-top:40px;
}

.card-box{
    border-radius:18px;
    padding:30px;
    color:white;
    box-shadow:0 4px 15px rgba(0,0,0,0.1);
    transition:0.3s;
}

.card-box:hover{
    transform:translateY(-5px);
}

.count{
    font-size:42px;
    font-weight:bold;
}

.label{
    font-size:18px;
    margin-top:10px;
}

.bg1{
    background:linear-gradient(135deg,#4f46e5,#7c3aed);
}

.bg2{
    background:linear-gradient(135deg,#059669,#10b981);
}

.bg3{
    background:linear-gradient(135deg,#ea580c,#f97316);
}

.bg4{
    background:linear-gradient(135deg,#dc2626,#ef4444);
}

.bg5{
    background:linear-gradient(135deg,#0284c7,#0ea5e9);
}

</style>

</head>

<body>

<nav class="navbar navbar-dark px-4 py-3">

<span class="navbar-brand fw-bold">
Eventix Admin Dashboard
</span>

<a href="../logout.php" class="btn btn-danger">
Logout
</a>

</nav>

<div class="container">

<div class="row g-4">

<div class="col-md-4">

<div class="card-box bg1">

<div class="count">
<?php echo $organizers; ?>
</div>

<div class="label">
Total Organizers
</div>

</div>

</div>

<div class="col-md-4">

<div class="card-box bg2">

<div class="count">
<?php echo $volunteers; ?>
</div>

<div class="label">
Total Volunteers
</div>

</div>

</div>

<div class="col-md-4">

<div class="card-box bg3">

<div class="count">
<?php echo $students; ?>
</div>

<div class="label">
Total Users
</div>

</div>

</div>

<div class="col-md-6">

<div class="card-box bg4">

<div class="count">
<?php echo $events; ?>
</div>

<div class="label">
Total Events
</div>

</div>

</div>

<div class="col-md-6">

<div class="card-box bg5">

<div class="count">
<?php echo $registrations; ?>
</div>

<div class="label">
Event Registrations
</div>

</div>

</div>

</div>

</div>

</body>
</html>