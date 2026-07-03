<?php
require_once __DIR__ . '/../../includes/db.php';
require_login();
$d=json_in(); $id=trim($d['id'] ?? '');
if($id==='') json_out(['ok'=>false,'error'=>'缺少 id'],400);
try {
  $n=(int)(function()use($id){$s=db()->prepare('SELECT COUNT(*) FROM reg_entities WHERE regulator_id=?');$s->execute([$id]);return $s->fetchColumn();})();
  if($n>0) json_out(['ok'=>false,'error'=>"该监管下还有 $n 个实体，请先清理"],400);
  db()->prepare('DELETE FROM regulators WHERE id=?')->execute([$id]);
  write_log('regulator_delete','',$id);
  json_out(['ok'=>true]);
} catch(Exception $e){ json_out(['ok'=>false,'error'=>$e->getMessage()],500); }
