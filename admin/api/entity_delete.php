<?php
require_once __DIR__ . '/../../includes/db.php';
require_login();
$d=json_in(); $id=(int)($d['id'] ?? 0);
if(!$id) json_out(['ok'=>false,'error'=>'缺少 id'],400);
try {
  db()->prepare('DELETE FROM broker_entity_map WHERE entity_id=?')->execute([$id]);
  db()->prepare('DELETE FROM reg_entities WHERE id=?')->execute([$id]);
  db()->query("UPDATE regulators r SET entity_count=(SELECT COUNT(*) FROM reg_entities e WHERE e.regulator_id=r.id)");
  write_log('entity_delete','',(string)$id);
  json_out(['ok'=>true]);
} catch(Exception $e){ json_out(['ok'=>false,'error'=>$e->getMessage()],500); }
