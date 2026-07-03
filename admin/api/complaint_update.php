<?php
require_once __DIR__ . '/../../includes/db.php';
require_login();

$id=trim($_POST['id']??'');
$status=$_POST['status']??'pending';
if(!in_array($status,['pending','processing','resolved','rejected'],true)) $status='pending';
$reply=trim($_POST['admin_reply']??'');
$resolved=($_POST['resolved_amount']??'')!=='' ? (float)$_POST['resolved_amount'] : null;

$q=db()->prepare('SELECT broker_id FROM complaints WHERE id=? LIMIT 1');
$q->execute([$id]); $row=$q->fetch();
if(!$row){ http_response_code(404); die('不存在'); }

db()->prepare('UPDATE complaints SET status=?, admin_reply=?, resolved_amount=?, updated_at=? WHERE id=?')
   ->execute([$status,$reply,$resolved,now_ts(),$id]);
if($row['broker_id']) recompute_broker_complaints($row['broker_id']);
write_log('complaint_update',$status,$id);
header('Location: /admin/complaints.php?id='.$id);
