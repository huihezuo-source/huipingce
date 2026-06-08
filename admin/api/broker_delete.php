<?php
require_once __DIR__ . '/../../includes/db.php';
require_login();
$d = json_in(); $id = trim($d['id'] ?? '');
if ($id==='') json_out(['ok'=>false,'error'=>'缺少 id'],400);
try {
  db()->prepare('DELETE FROM broker_entity_map WHERE broker_id=?')->execute([$id]);
  db()->prepare('UPDATE reviews SET broker_id=NULL WHERE broker_id=?')->execute([$id]);
  db()->prepare('DELETE FROM brokers WHERE id=?')->execute([$id]);
  write_log('broker_delete','',$id);
  json_out(['ok'=>true]);
} catch (Exception $e){ json_out(['ok'=>false,'error'=>$e->getMessage()],500); }
