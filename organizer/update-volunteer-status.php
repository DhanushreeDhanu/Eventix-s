<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role']!=='organizer'){ header('Location: login.php'); exit; }
$organizer_id=(int)$_SESSION['user_id'];
$join_id=(int)($_POST['join_id'] ?? 0);
$action=$_POST['action'] ?? '';
$allowed=['present','absent','paid','remove'];
if(!in_array($action,$allowed,true)){ die('Invalid action'); }

$q=$conn->prepare("SELECT ve.*, e.event_name, e.organizer_id, u.id volunteer_id
 FROM volunteer_events ve JOIN events e ON e.id=ve.event_id JOIN users u ON u.id=ve.volunteer_id
 WHERE ve.id=? AND e.organizer_id=?");
$q->bind_param('ii',$join_id,$organizer_id); $q->execute();
$row=$q->get_result()->fetch_assoc();
if(!$row){ die('Not allowed'); }

if($action==='present' || $action==='absent'){
  $stmt=$conn->prepare("UPDATE volunteer_events SET attendance_status=?, attendance_marked_at=NOW() WHERE id=?");
  $stmt->bind_param('si',$action,$join_id);
  $msg="Attendance marked as $action for {$row['event_name']}";
}elseif($action==='paid'){
  $stmt=$conn->prepare("UPDATE volunteer_events SET payment_status='paid', payment_marked_at=NOW() WHERE id=?");
  $stmt->bind_param('i',$join_id);
  $msg="Payment marked as paid for {$row['event_name']}";
}else{
  $stmt=$conn->prepare("UPDATE volunteer_events SET status='removed' WHERE id=?");
  $stmt->bind_param('i',$join_id);
  $msg="You were removed from {$row['event_name']} by organizer";
}
$stmt->execute();
notify_user($conn,(int)$row['volunteer_id'],'Event update',$msg);
notify_role($conn,'admin','Organizer updated volunteer',$msg);
header('Location: volunteers.php?id='.$row['event_id'].'&updated=1');
?>
