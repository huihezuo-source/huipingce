<?php
require_once __DIR__ . '/../../includes/db.php';
require_login();
$in = json_in();
$id = trim($in['id'] ?? '');
$act = $in['action'] ?? '';

$q=db()->prepare('SELECT broker_id FROM broker_reviews WHERE id=? LIMIT 1');
$q->execute([$id]); $row=$q->fetch();
if(!$row) json_out(['ok'=>false,'error'=>'测评不存在']);
$bid=$row['broker_id'];

if ($act==='approve') {
    db()->prepare("UPDATE broker_reviews SET status='approved',updated_at=? WHERE id=?")->execute([now_ts(),$id]);
    write_log('review_approve','',$id);
} elseif ($act==='reject') {
    db()->prepare("UPDATE broker_reviews SET status='rejected',updated_at=? WHERE id=?")->execute([now_ts(),$id]);
    write_log('review_reject','',$id);
} elseif ($act==='delete') {
    db()->prepare('DELETE FROM broker_reviews WHERE id=?')->execute([$id]);
    db()->prepare('DELETE FROM review_votes WHERE review_id=?')->execute([$id]);
    write_log('review_delete','',$id);
} else {
    json_out(['ok'=>false,'error'=>'未知操作']);
}
recompute_broker_rating($bid);
json_out(['ok'=>true]);
