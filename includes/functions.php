<?php
function safe($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
function notify_user($conn,$user_id,$title,$message){
  $stmt=$conn->prepare("INSERT INTO notifications(user_id,title,message) VALUES(?,?,?)");
  $stmt->bind_param("iss",$user_id,$title,$message); $stmt->execute();
}
function notify_role($conn,$role,$title,$message){
  $stmt=$conn->prepare("INSERT INTO notifications(role,title,message) VALUES(?,?,?)");
  $stmt->bind_param("sss",$role,$title,$message); $stmt->execute();
}
?>
