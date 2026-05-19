<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role']!=='volunteer'){ header('Location: login.php'); exit; }
$volunteer_id=(int)$_SESSION['user_id'];
$event_id=(int)($_GET['id'] ?? 0);

$conn->begin_transaction();
try{
  $stmt=$conn->prepare("SELECT e.*, u.id organizer_id, u.name organizer_name
    FROM events e JOIN users u ON u.id=e.organizer_id
    WHERE e.id=? AND e.status='upcoming' AND e.event_date>=CURDATE() FOR UPDATE");
  $stmt->bind_param('i',$event_id); $stmt->execute();
  $event=$stmt->get_result()->fetch_assoc();
  if(!$event) throw new Exception('Event not available.');

  $same=$conn->prepare("SELECT ve.id FROM volunteer_events ve JOIN events e ON e.id=ve.event_id
    WHERE ve.volunteer_id=? AND e.event_date=? AND ve.status='joined'");
  $same->bind_param('is',$volunteer_id,$event['event_date']); $same->execute();
  if($same->get_result()->num_rows>0) throw new Exception('You can join only one event on the same date.');

  $count=$conn->prepare("SELECT COUNT(*) total FROM volunteer_events WHERE event_id=? AND status='joined'");
  $count->bind_param('i',$event_id); $count->execute();
  $joined=(int)$count->get_result()->fetch_assoc()['total'];
  if($joined >= (int)$event['required_volunteers']) throw new Exception('Volunteer limit is full.');

  $ins=$conn->prepare("INSERT INTO volunteer_events(volunteer_id,event_id,attendance_status,payment_status,status,joined_at)
    VALUES(?,?,'pending','pending','joined',NOW())");
  $ins->bind_param('ii',$volunteer_id,$event_id); $ins->execute();
  notify_user($conn,(int)$event['organizer_id'],'New volunteer joined',$_SESSION['name'].' joined '.$event['event_name']);
  notify_role($conn,'admin','Volunteer joined event',$_SESSION['name'].' joined '.$event['event_name']);
  $conn->commit();
  header("Location: joined-events.php?joined=1"); exit;
}catch(Exception $e){
  $conn->rollback();
  header('Location: available-events.php?error='.urlencode($e->getMessage())); exit;
}
?>
