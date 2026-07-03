<?php
// 前台：提交/更新用户评分 + 测评
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$me = current_user();
if (!$me) json_out(['ok'=>false,'need_login'=>true,'msg'=>'请先登录'], 200);

$in = json_in();
$bid   = trim($in['broker_id'] ?? '');
$stars = (int)($in['stars'] ?? 0);
$content = trim((string)($in['content'] ?? ''));

if ($stars < 1 || $stars > 5) json_out(['ok'=>false,'msg'=>'请选择 1-5 星']);
if (mb_strlen($content) > 2000) json_out(['ok'=>false,'msg'=>'测评内容过长']);

// 交易商存在？
$q = db()->prepare('SELECT id FROM brokers WHERE id=? LIMIT 1');
$q->execute([$bid]);
if (!$q->fetch()) json_out(['ok'=>false,'msg'=>'交易商不存在']);

// 审核策略：有内容且开启审核 → pending；纯打分或未开审核 → approved
$moderate = setting('reviewModeration','0') === '1';
$status = ($moderate && $content !== '') ? 'pending' : 'approved';

// upsert（每人每交易商一条）
$ex = db()->prepare('SELECT id FROM broker_reviews WHERE broker_id=? AND user_id=? LIMIT 1');
$ex->execute([$bid, $me['id']]);
$row = $ex->fetch();

if ($row) {
    db()->prepare('UPDATE broker_reviews SET stars=?, content=?, status=?, updated_at=? WHERE id=?')
        ->execute([$stars, $content, $status, now_ts(), $row['id']]);
} else {
    db()->prepare('INSERT INTO broker_reviews (id,broker_id,user_id,stars,content,status,ip,created_at) VALUES (?,?,?,?,?,?,?,?)')
        ->execute(['rv_'.gen_id(), $bid, $me['id'], $stars, $content, $status, client_ip(), now_ts()]);
}

$stats = recompute_broker_rating($bid);
json_out(['ok'=>true,'status'=>$status,'score10'=>$stats['score10'],'count'=>$stats['count']]);
