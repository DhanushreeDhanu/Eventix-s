<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role']!=='admin'){ header('Location: login.php'); exit; }
$event_id=(int)($_GET['id'] ?? 0);
$q=$conn->prepare("SELECT e.event_name,e.organizer_id,ve.volunteer_id FROM events e LEFT JOIN volunteer_events ve ON ve.event_id=e.id WHERE e.id=?");
$q->bind_param('i',$event_id); $q->execute(); $rs=$q->get_result();
$event_name=''; $organizer_id=0; $volunteers=[];
while($r=$rs->fetch_assoc()){ $event_name=$r['event_name']; $organizer_id=(int)$r['organizer_id']; if($r['volunteer_id']) $volunteers[]=(int)$r['volunteer_id']; }
if($event_name){
  notify_user($conn,$organizer_id,'Event deleted by admin',"Your event '$event_name' was deleted by admin.");
  foreach(array_unique($volunteers) as $vid){ notify_user($conn,$vid,'Event cancelled',"The event '$event_name' was deleted by admin."); }
  $d=$conn->prepare('DELETE FROM events WHERE id=?'); $d->bind_param('i',$event_id); $d->execute();
}
header('Location: manage-events.php?deleted=1');
?>
