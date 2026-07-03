<?php
require_once __DIR__ . '/../../includes/db.php';
require_login();

$title=trim($_POST['title']??'');
if($title==='') { http_response_code(400); die('标题不能为空'); }
$id=trim($_POST['id']??''); $now=now_ts();

$pub = null;
if(($_POST['publish_at']??'')!=='') $pub = strtotime($_POST['publish_at']);
if($pub===false) $pub=null;
$status=$_POST['status']??'published';
if($status==='published' && !$pub) $pub=$now;

$f=[
  'cat_id'=>($_POST['cat_id']??'')!=='' ? (int)$_POST['cat_id'] : null,
  'broker_id'=>($_POST['broker_id']??'')!=='' ? trim($_POST['broker_id']) : null,
  'title'=>$title,
  'slug'=>slugify($title),
  'cover'=>trim($_POST['cover']??''),
  'summary'=>trim($_POST['summary']??''),
  'content'=>$_POST['content']??'',
  'source'=>trim($_POST['source']??''),
  'author'=>trim($_POST['author']??'汇评测') ?: '汇评测',
  'tags'=>trim($_POST['tags']??''),
  'status'=>in_array($status,['published','draft','scheduled'],true)?$status:'draft',
  'publish_at'=>$pub,
  'updated_at'=>$now,
];
try {
  if($id){
    $set=implode(',',array_map(fn($k)=>"`$k`=:$k",array_keys($f)));
    $f['id']=$id;
    db()->prepare("UPDATE articles SET $set WHERE id=:id")->execute($f);
    write_log('article_update',$title,$id);
  } else {
    $id='art_'.gen_id(); $f['id']=$id; $f['created_at']=$now;
    $cols=implode(',',array_map(fn($k)=>"`$k`",array_keys($f)));
    $ph=implode(',',array_map(fn($k)=>":$k",array_keys($f)));
    db()->prepare("INSERT INTO articles ($cols) VALUES ($ph)")->execute($f);
    write_log('article_create',$title,$id);
  }
  header('Location: /admin/articles.php');
} catch(Exception $e){ http_response_code(500); die('保存失败：'.h($e->getMessage())); }
