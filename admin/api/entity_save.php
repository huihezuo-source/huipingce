<?php
require_once __DIR__ . '/../../includes/db.php';
require_login();
$name=trim($_POST['name'] ?? ''); $reg=trim($_POST['regulator_id'] ?? '');
if($name===''||$reg==='') { http_response_code(400); die('实体名与监管机构必填'); }
$id=(int)($_POST['id'] ?? 0);
$status=in_array($_POST['status'] ?? 'active',['active','suspended','revoked','expired'],true)?$_POST['status']:'active';
$reg_date = !empty($_POST['reg_date']) ? date('Y-m-d',strtotime($_POST['reg_date'])) : null;
$f=[
  'name'=>mb_substr($name,0,220),'regulator_id'=>$reg,
  'license_no'=>mb_substr(trim($_POST['license_no'] ?? ''),0,120),
  'license_type'=>mb_substr(trim($_POST['license_type'] ?? ''),0,300),
  'client_type'=>mb_substr(trim($_POST['client_type'] ?? ''),0,80),'status'=>$status,'reg_date'=>$reg_date,
  'country'=>trim($_POST['country'] ?? ''),'city'=>trim($_POST['city'] ?? ''),
  'website'=>trim($_POST['website'] ?? ''),'source'=>trim($_POST['source'] ?? 'manual'),
];
try {
  if($id){
    $set=implode(',',array_map(fn($k)=>"`$k`=:$k",array_keys($f))); $f['id']=$id;
    db()->prepare("UPDATE reg_entities SET $set WHERE id=:id")->execute($f);
    write_log('entity_update',$name,(string)$id);
  } else {
    $cols=implode(',',array_map(fn($k)=>"`$k`",array_keys($f)));
    $ph=implode(',',array_map(fn($k)=>":$k",array_keys($f)));
    db()->prepare("INSERT INTO reg_entities ($cols) VALUES ($ph)")->execute($f);
    write_log('entity_create',$name,'');
  }
  db()->query("UPDATE regulators r SET entity_count=(SELECT COUNT(*) FROM reg_entities e WHERE e.regulator_id=r.id)");
  header('Location: /admin/reg_entities.php?saved=1');
} catch(Exception $e){ http_response_code(500); die('保存失败：'.h($e->getMessage())); }
