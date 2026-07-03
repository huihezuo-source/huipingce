<?php
require_once __DIR__ . '/../../includes/db.php';
require_login();
$d = json_in();
$action = $d['action'] ?? '';
$bid = trim($d['broker_id'] ?? '');
$eid = (int)($d['entity_id'] ?? 0);
if ($bid==='' || !$eid) json_out(['ok'=>false,'error'=>'参数缺失'],400);
try {
  if ($action==='link') {
    db()->prepare('INSERT IGNORE INTO broker_entity_map (broker_id,entity_id,created_at) VALUES (?,?,?)')
        ->execute([$bid,$eid,now_ts()]);
    write_log('entity_link',"e=$eid",$bid);
  } elseif ($action==='unlink') {
    db()->prepare('DELETE FROM broker_entity_map WHERE broker_id=? AND entity_id=?')->execute([$bid,$eid]);
    write_log('entity_unlink',"e=$eid",$bid);
  } else json_out(['ok'=>false,'error'=>'未知操作'],400);
  json_out(['ok'=>true]);
} catch (Exception $e){ json_out(['ok'=>false,'error'=>$e->getMessage()],500); }
