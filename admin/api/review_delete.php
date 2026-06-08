<?php
require_once __DIR__ . '/../../includes/db.php';
require_login();
$d = json_in();
$id = trim($d['id'] ?? '');
if ($id === '') json_out(['ok'=>false,'error'=>'缺少 id'], 400);
try {
  db()->prepare('DELETE FROM review_scores WHERE review_id=?')->execute([$id]);
  db()->prepare('DELETE FROM reviews WHERE id=?')->execute([$id]);
  write_log('review_delete', '', $id);
  json_out(['ok'=>true]);
} catch (Exception $e) { json_out(['ok'=>false,'error'=>$e->getMessage()],500); }
