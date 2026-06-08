<?php
require_once __DIR__ . '/../../includes/db.php';
require_login();

$d = json_in();
$title = trim($d['title'] ?? '');
if ($title === '') json_out(['ok'=>false,'error'=>'标题不能为空'], 400);

$id = trim($d['id'] ?? '');
$now = now_ts();
$status = in_array($d['status'] ?? 'draft', ['draft','scheduled','published'], true) ? $d['status'] : 'draft';

// 定时发布时间
$publish_at = null;
if (!empty($d['publish_at'])) {
    $ts = strtotime($d['publish_at']);
    if ($ts) $publish_at = $ts;
}
if ($status === 'published' && !$publish_at) $publish_at = $now;

// 总分：手动或自动聚合
$scores = is_array($d['scores'] ?? null) ? $d['scores'] : [];
$overall = null;
if (!empty($d['overall_manual']) && $d['overall_score'] !== '') {
    $overall = round((float)$d['overall_score'], 1);
} elseif ($scores) {
    $overall = aggregate_review_score($scores);
} elseif ($d['overall_score'] !== '') {
    $overall = round((float)$d['overall_score'], 1);
}

$pros = json_encode(array_values(array_filter((array)($d['pros'] ?? []))), JSON_UNESCAPED_UNICODE);
$cons = json_encode(array_values(array_filter((array)($d['cons'] ?? []))), JSON_UNESCAPED_UNICODE);
$broker_id = trim($d['broker_id'] ?? '') ?: null;
$slug = slugify($d['slug'] ?? $title);

$fields = [
  'broker_id'=>$broker_id,
  'title'=>$title,
  'slug'=>mb_substr($slug,0,150),
  'cover'=>trim($d['cover'] ?? ''),
  'verdict'=>mb_substr(trim($d['verdict'] ?? ''),0,300),
  'overall_score'=>$overall,
  'summary'=>trim($d['summary'] ?? ''),
  'content'=>$d['content'] ?? '',
  'pros'=>$pros,
  'cons'=>$cons,
  'tags'=>mb_substr(trim($d['tags'] ?? ''),0,255),
  'author'=>trim($d['author'] ?? '评测组') ?: '评测组',
  'read_time'=>max(1,(int)($d['read_time'] ?? 5)),
  'status'=>$status,
  'publish_at'=>$publish_at,
  'updated_at'=>$now,
];

try {
  if ($id) {
    $set = implode(',', array_map(fn($k)=>"`$k`=:$k", array_keys($fields)));
    $sql = "UPDATE reviews SET $set WHERE id=:id";
    $stmt = db()->prepare($sql);
    $fields['id'] = $id;
    $stmt->execute($fields);
    write_log('review_update', $title, $id);
  } else {
    $id = gen_id();
    $fields['id'] = $id;
    $fields['created_at'] = $now;
    $cols = implode(',', array_map(fn($k)=>"`$k`", array_keys($fields)));
    $ph   = implode(',', array_map(fn($k)=>":$k", array_keys($fields)));
    db()->prepare("INSERT INTO reviews ($cols) VALUES ($ph)")->execute($fields);
    write_log('review_create', $title, $id);
  }

  // 评分维度：先删后插
  db()->prepare('DELETE FROM review_scores WHERE review_id=?')->execute([$id]);
  if ($scores) {
    $ins = db()->prepare('INSERT INTO review_scores (review_id,dimension,score,weight,sort_order) VALUES (?,?,?,?,?)');
    foreach ($scores as $i=>$s) {
      $ins->execute([$id, mb_substr($s['dimension'] ?? '',0,40), round((float)($s['score'] ?? 0),1), (float)($s['weight'] ?? 1), $i]);
    }
  }

  // 同步经纪商综合评分缓存
  if ($broker_id && $overall !== null && $status === 'published') {
    db()->prepare('UPDATE brokers SET score=?, updated_at=? WHERE id=?')->execute([$overall,$now,$broker_id]);
  }

  json_out(['ok'=>true,'id'=>$id]);
} catch (Exception $e) {
  json_out(['ok'=>false,'error'=>$e->getMessage()], 500);
}
