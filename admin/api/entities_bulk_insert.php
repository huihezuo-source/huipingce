<?php
/**
 * 受监管实体批量入库 — 给采集脚本(scripts/collect_*.php)调用
 * 鉴权：cron_key（不需登录）
 * 入参 JSON：{ "cron_key":"...", "entities":[ {name,regulator_id,license_no,...}, ... ] }
 * 去重：UNIQUE(regulator_id, license_no)，命中则更新
 */
require_once __DIR__ . '/../../includes/db.php';

$d = json_in();
if (($d['cron_key'] ?? '') !== CRON_KEY) json_out(['ok'=>false,'error'=>'forbidden'],403);

$items = $d['entities'] ?? [];
if (!is_array($items) || !$items) json_out(['ok'=>false,'error'=>'entities 为空'],400);

// 合法监管 id 集合
$valid_regs = array_column(db()->query('SELECT id FROM regulators')->fetchAll(), 'id');
$valid_regs = array_flip($valid_regs);

$cols = ['name','name_local','regulator_id','license_no','license_type','client_type','status','reg_date','country','city','website','phone','email','source','note'];
$ins = db()->prepare(
  "INSERT INTO reg_entities (".implode(',',$cols).") VALUES (".implode(',',array_fill(0,count($cols),'?')).")
   ON DUPLICATE KEY UPDATE
     name=VALUES(name), name_local=VALUES(name_local), license_type=VALUES(license_type),
     client_type=VALUES(client_type), status=VALUES(status), reg_date=VALUES(reg_date),
     country=VALUES(country), city=VALUES(city), website=VALUES(website),
     phone=VALUES(phone), email=VALUES(email), source=VALUES(source), note=VALUES(note)"
);

$ok=0; $skip=0; $errors=[];
foreach ($items as $i=>$e) {
  $name = trim($e['name'] ?? '');
  $reg  = trim($e['regulator_id'] ?? '');
  if ($name==='' || !isset($valid_regs[$reg])) { $skip++; $errors[]="#$i 缺 name 或非法 regulator_id"; continue; }
  $status = in_array($e['status'] ?? 'active',['active','suspended','revoked','expired'],true)?$e['status']:'active';
  $reg_date = !empty($e['reg_date']) ? date('Y-m-d', strtotime($e['reg_date'])) : null;
  try {
    $ins->execute([
      mb_substr($name,0,220), mb_substr($e['name_local'] ?? '',0,220), $reg,
      mb_substr($e['license_no'] ?? '',0,120), mb_substr($e['license_type'] ?? '',0,300),
      mb_substr($e['client_type'] ?? '',0,80), $status, $reg_date,
      mb_substr($e['country'] ?? '',0,80), mb_substr($e['city'] ?? '',0,80),
      mb_substr($e['website'] ?? '',0,220), mb_substr($e['phone'] ?? '',0,60),
      mb_substr($e['email'] ?? '',0,200), mb_substr($e['source'] ?? '',0,40),
      mb_substr($e['note'] ?? '',0,500),
    ]);
    $ok++;
  } catch (Exception $ex) { $skip++; $errors[]="#$i ".$ex->getMessage(); }
}

// 刷新各监管实体计数缓存
db()->query("UPDATE regulators r SET entity_count=(SELECT COUNT(*) FROM reg_entities e WHERE e.regulator_id=r.id)");

json_out(['ok'=>true,'inserted'=>$ok,'skipped'=>$skip,'errors'=>array_slice($errors,0,20)]);
