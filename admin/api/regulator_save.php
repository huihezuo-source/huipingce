<?php
require_once __DIR__ . '/../../includes/db.php';
require_login();
$name = trim($_POST['name'] ?? '');
if ($name==='') { http_response_code(400); die('简称不能为空'); }
$id = trim($_POST['id'] ?? '');
$now = now_ts();
$grade = in_array($_POST['grade'] ?? 'A',['AAA','AA','A','B','C'],true)?$_POST['grade']:'A';
$f = [
  'name'=>$name,'full_name'=>trim($_POST['full_name'] ?? ''),
  'country'=>trim($_POST['country'] ?? ''),'flag'=>trim($_POST['flag'] ?? ''),
  'region'=>trim($_POST['region'] ?? ''),'grade'=>$grade,
  'trust_score'=>max(0,min(100,(int)($_POST['trust_score'] ?? 0))),
  'established'=>($_POST['established'] ?? '')!=='' ? (int)$_POST['established'] : null,
  'gov_type'=>trim($_POST['gov_type'] ?? ''),'website'=>trim($_POST['website'] ?? ''),
  'query_url'=>trim($_POST['query_url'] ?? ''),'description'=>trim($_POST['description'] ?? ''),
  'sort_order'=>(int)($_POST['sort_order'] ?? 0),'updated_at'=>$now,
];
try {
  if ($id) {
    $set=implode(',',array_map(fn($k)=>"`$k`=:$k",array_keys($f))); $f['id']=$id;
    db()->prepare("UPDATE regulators SET $set WHERE id=:id")->execute($f);
    write_log('regulator_update',$name,$id);
  } else {
    $id='reg_'.strtolower(preg_replace('/[^a-z0-9]+/i','_',$name)).'_'.substr(gen_id(),0,4);
    $f['id']=$id; $f['created_at']=$now;
    $cols=implode(',',array_map(fn($k)=>"`$k`",array_keys($f)));
    $ph=implode(',',array_map(fn($k)=>":$k",array_keys($f)));
    db()->prepare("INSERT INTO regulators ($cols) VALUES ($ph)")->execute($f);
    write_log('regulator_create',$name,$id);
  }
  header('Location: /admin/regulators.php?saved=1');
} catch (Exception $e){ http_response_code(500); die('保存失败：'.h($e->getMessage())); }
