<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'organizer') {

    header("Location: ../login.php");
    exit();
}

$organizer_id = $_SESSION['user_id'];

if (!isset($_GET['event_id'])) {

    header("Location: my-events.php");
    exit();
}

$event_id = intval($_GET['event_id']);

$event_stmt = $conn->prepare(
    "SELECT * FROM events 
     WHERE id = ? AND organizer_id = ?"
);

$event_stmt->bind_param(
    "ii",
    $event_id,
    $organizer_id
);

$event_stmt->execute();

$event = $event_stmt
            ->get_result()
            ->fetch_assoc();

if (!$event) {

    echo "Event not found";
    exit();
}

$sql = "
SELECT 
    ve.joined_at,
    ve.attendance_status,
    ve.payment_status,
    u.name,
    u.email,
    u.phone

FROM volunteer_events ve

INNER JOIN users u
ON ve.volunteer_id = u.id

WHERE ve.event_id = ?

ORDER BY ve.joined_at DESC
";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $event_id);

$stmt->execute();

$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>

<title>Registered Volunteers</title>

<style>

body{
    font-family:Arial;
    background:#f4f7fc;
    margin:0;
}

.container{
    width:90%;
    margin:40px auto;
}

.card{
    background:white;
    padding:25px;
    border-radius:15px;
    box-shadow:0 4px 12px rgba(0,0,0,0.1);
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
}

th{
    background:#007bff;
    color:white;
    padding:14px;
}

td{
    padding:14px;
    border-bottom:1px solid #ddd;
}

tr:hover{
    background:#f1f5ff;
}

.empty{
    text-align:center;
    padding:30px;
    color:#666;
}

.btn{
    display:inline-block;
    margin-top:20px;
    background:#007bff;
    color:white;
    padding:12px 20px;
    border-radius:8px;
    text-decoration:none;
}

</style>

</head>

<body>

<div class="container">

<div class="card">

<h1>
<?php echo htmlspecialchars($event['event_name']); ?>
</h1>

<p>
<strong>Date:</strong>
<?php echo htmlspecialchars($event['event_date']); ?>
</p>

<p>
<strong>Venue:</strong>
<?php echo htmlspecialchars($event['venue']); ?>
</p>

<?php if($result->num_rows > 0){ ?>

<table>

<tr>
<th>#</th>
<th>Name</th>
<th>Email</th>
<th>Phone</th>
<th>Joined At</th>
</tr>

<?php
$i = 1;

while($row = $result->fetch_assoc()){
?>

<tr>

<td><?php echo $i++; ?></td>

<td>
<?php echo htmlspecialchars($row['name']); ?>
</td>

<td>
<?php echo htmlspecialchars($row['email']); ?>
</td>

<td>
<?php echo htmlspecialchars($row['phone']); ?>
</td>

<td>
<?php echo htmlspecialchars($row['joined_at']); ?>
</td>

</tr>

<?php } ?>

</table>

<?php } else { ?>

<div class="empty">

No volunteers registered yet.

</div>

<?php } ?>

<a href="my-events.php" class="btn">

← Back

</a>

</div>

</div>

</body>
</html> 