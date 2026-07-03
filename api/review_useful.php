<?php
// 前台：给测评点「有用」
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$me = current_user();
if (!$me) json_out(['ok'=>false,'need_login'=>true,'msg'=>'请先登录']);

$in = json_in();
$rid = trim($in['review_id'] ?? '');

$q = db()->prepare("SELECT id,user_id FROM broker_reviews WHERE id=? AND status='approved' LIMIT 1");
$q->execute([$rid]);
$rev = $q->fetch();
if (!$rev) json_out(['ok'=>false,'msg'=>'测评不存在']);
if ($rev['user_id'] === $me['id']) json_out(['ok'=>false,'msg'=>'不能给自己的测评点有用']);

try {
    db()->prepare('INSERT INTO review_votes (review_id,user_id,created_at) VALUES (?,?,?)')
        ->execute([$rid, $me['id'], now_ts()]);
} catch (PDOException $e) {
    json_out(['ok'=>false,'msg'=>'你已经点过了']); // 主键冲突
}

db()->prepare('UPDATE broker_reviews SET useful_count=useful_count+1 WHERE id=?')->execute([$rid]);
$c = db()->prepare('SELECT useful_count FROM broker_reviews WHERE id=?');
$c->execute([$rid]);
json_out(['ok'=>true,'count'=>(int)$c->fetchColumn()]);
