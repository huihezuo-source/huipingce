<?php
require_once __DIR__ . '/../../includes/db.php';
require_login();

$name = trim($_POST['name'] ?? '');
if ($name === '') { http_response_code(400); die('品牌名不能为空'); }
$id = trim($_POST['id'] ?? '');
$now = now_ts();

$f = [
  'name'=>$name,
  'name_en'=>trim($_POST['name_en'] ?? ''),
  'code'=>trim($_POST['code'] ?? ''),
  'established'=>($_POST['established'] ?? '')!=='' ? (int)$_POST['established'] : null,
  'headquarters'=>trim($_POST['headquarters'] ?? ''),
  'website'=>trim($_POST['website'] ?? ''),
  'platform'=>trim($_POST['platform'] ?? 'MT4/MT5'),
  'min_dep'=>(int)($_POST['min_dep'] ?? 0),
  'leverage'=>trim($_POST['leverage'] ?? '1:500'),
  'spread'=>trim($_POST['spread'] ?? '浮动'),
  'btype'=>trim($_POST['btype'] ?? 'ECN'),
  'score'=>($_POST['score'] ?? '')!=='' ? round((float)$_POST['score'],1) : null,
  'summary'=>mb_substr(trim($_POST['summary'] ?? ''),0,300),
  'featured'=>!empty($_POST['featured'])?1:0,
  'verified'=>!empty($_POST['verified'])?1:0,
  'updated_at'=>$now,
];

try {
  if ($id) {
    $set = implode(',', array_map(fn($k)=>"`$k`=:$k", array_keys($f)));
    $f['id']=$id;
    db()->prepare("UPDATE brokers SET $set WHERE id=:id")->execute($f);
    write_log('broker_update',$name,$id);
  } else {
    $id = gen_id(); $f['id']=$id; $f['created_at']=$now;
    $cols=implode(',',array_map(fn($k)=>"`$k`",array_keys($f)));
    $ph=implode(',',array_map(fn($k)=>":$k",array_keys($f)));
    db()->prepare("INSERT INTO brokers ($cols) VALUES ($ph)")->execute($f);
    write_log('broker_create',$name,$id);
  }
  header('Location: /admin/broker_edit.php?id='.$id.'&saved=1');
} catch (Exception $e) { http_response_code(500); die('保存失败：'.h($e->getMessage())); }
