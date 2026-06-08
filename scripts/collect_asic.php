<?php
/**
 * ASIC（澳大利亚）受监管实体采集器
 * 数据源：ASIC AFS Licensees 公开数据集（CSV）
 *   下载页：https://data.gov.au/dataset/ → 搜 "ASIC AFS Licensees"
 *   或 ASIC Connect 专业注册：https://connectonline.asic.gov.au
 *
 * 用法：
 *   1) 先把 AFS Licensees CSV 下到本地，如 scripts/_tmp/asic_afsl.csv
 *   2) php scripts/collect_asic.php scripts/_tmp/asic_afsl.csv [--dry]
 *
 * CSV 需含列（列名大小写不敏感，自动匹配）：
 *   licensee name / AFS licence number / status / address(state) 等
 * 仅抽取经营范围含 derivatives / foreign exchange 的持牌人（外汇相关）。
 */
require __DIR__ . '/lib.php';

$file = $argv[1] ?? '';
$dry  = in_array('--dry', $argv, true);
if (!is_file($file)) {
  fwrite(STDERR, "用法：php scripts/collect_asic.php <AFS_CSV 文件> [--dry]\n");
  fwrite(STDERR, "下载 ASIC AFS Licensees CSV：https://data.gov.au（搜 ASIC AFS Licensees）\n");
  exit(1);
}

$fh = fopen($file, 'r');
$header = fgetcsv($fh);
if (!$header) { fwrite(STDERR,"❌ CSV 为空\n"); exit(1); }
// 列名归一化
$idx = [];
foreach ($header as $i=>$col) $idx[strtolower(trim($col))] = $i;
function col($row,$idx,$names){ foreach((array)$names as $n){ $n=strtolower($n); if(isset($idx[$n])) return trim($row[$idx[$n]] ?? ''); } return ''; }

$entities = []; $scanned = 0;
while (($row = fgetcsv($fh)) !== false) {
  $scanned++;
  $name = col($row,$idx,['licensee name','organisation name','name']);
  $afsl = col($row,$idx,['afs licence number','licence number','afsl']);
  if ($name === '' || $afsl === '') continue;
  // 外汇相关过滤：经营范围/授权字段含关键词
  $auth = strtolower(col($row,$idx,['authorisations','authorisation','licence authorisation conditions','products']));
  $blob = strtolower($name.' '.$auth);
  if ($auth !== '' && strpos($auth,'derivativ')===false && strpos($auth,'foreign exchange')===false) continue;
  $status = stripos(col($row,$idx,['status','licence status']),'cancel')!==false ? 'revoked' : 'active';
  $entities[] = [
    'name'=>$name, 'regulator_id'=>'reg_asic',
    'license_no'=>$afsl, 'license_type'=>'AFS Licence',
    'status'=>$status, 'country'=>'澳大利亚',
    'city'=>col($row,$idx,['state','address state','suburb']),
    'source'=>'asic-csv',
  ];
}
fclose($fh);

echo "扫描 $scanned 行，外汇相关实体 ".count($entities)." 个。\n";
if ($dry) {
  foreach (array_slice($entities,0,30) as $e) echo "  · {$e['name']}  AFSL {$e['license_no']}\n";
  echo (count($entities)>30?"  ...\n":'')."(--dry 模式，未入库)\n";
} else {
  ingest_entities($entities);
}
