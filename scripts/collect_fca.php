<?php
/**
 * FCA（英国）受监管实体采集器
 * 数据源：FCA Register 公开 API  https://register.fca.org.uk/services/V0.1/
 * 需免费注册取 X-Auth-Email / X-Auth-Key：https://register.fca.org.uk/Developer/s/
 *
 * 用法：
 *   php scripts/collect_fca.php names scripts/fca_brokers.txt   # 按品牌名清单搜索
 *   php scripts/collect_fca.php frn 122169 684312 ...           # 按 FRN 直接抓
 *   php scripts/collect_fca.php names scripts/fca_brokers.txt --dry  # 只打印不入库
 *
 * 入库目标：reg_entities（regulator_id = reg_fca），去重 UNIQUE(regulator_id, license_no=FRN)
 */
require __DIR__ . '/lib.php';

$API = 'https://register.fca.org.uk/services/V0.1';
$mode = $argv[1] ?? '';
$dry  = in_array('--dry', $argv, true);

if (!cfg('fca_email') || !cfg('fca_key')) {
  fwrite(STDERR, "❌ 缺少 FCA API 凭证。请在 scripts/secret.php 配置 fca_email / fca_key，\n");
  fwrite(STDERR, "   或设置环境变量 FCA_EMAIL / FCA_KEY。注册地址：https://register.fca.org.uk/Developer/s/\n");
  exit(1);
}
$H = ['X-Auth-Email: '.cfg('fca_email'), 'X-Auth-Key: '.cfg('fca_key')];

/** 抓单个 FRN 的实体记录 */
function fetch_firm($frn, $API, $H) {
  $d = http_get_json("$API/Firm/$frn", $H);
  if (($d['_http'] ?? 0) !== 200 || empty($d['Data'][0])) return null;
  $f = $d['Data'][0];
  // 权限（牌照类型）
  $perm = http_get_json("$API/Firm/$frn/Permissions", $H);
  $ptypes = [];
  if (!empty($perm['Data'])) {
    foreach ($perm['Data'] as $blk) {
      foreach ((array)$blk as $name=>$v) { $ptypes[] = $name; if (count($ptypes)>=4) break 2; }
    }
  }
  $status = stripos($f['Status'] ?? '', 'Authorised') !== false ? 'active'
          : (stripos($f['Status'] ?? '', 'No longer') !== false ? 'revoked' : 'active');
  return [
    'name'         => $f['Organisation Name'] ?? ('FRN '.$frn),
    'regulator_id' => 'reg_fca',
    'license_no'   => (string)$frn,
    'license_type' => implode(' / ', array_slice(array_unique($ptypes),0,4)),
    'client_type'  => '',
    'status'       => $status,
    'country'      => '英国',
    'source'       => 'fca-api',
    'note'         => trim($f['Status'] ?? ''),
  ];
}

$entities = [];

if ($mode === 'frn') {
  $frns = array_slice($argv, 2);
  $frns = array_filter($frns, fn($x)=>ctype_digit($x));
  foreach ($frns as $frn) {
    echo "→ FRN $frn ... ";
    $e = fetch_firm($frn, $API, $H);
    if ($e) { $entities[] = $e; echo $e['name']."\n"; } else echo "未找到\n";
    usleep(200000);
  }
} elseif ($mode === 'names') {
  $file = $argv[2] ?? '';
  if (!is_file($file)) { fwrite(STDERR,"❌ 找不到清单文件：$file\n"); exit(1); }
  $names = array_filter(array_map('trim', file($file)));
  foreach ($names as $q) {
    if ($q === '' || $q[0] === '#') continue;
    echo "🔎 搜索「$q」... ";
    $s = http_get_json("$API/Search?q=".urlencode($q)."&type=firm", $H);
    if (empty($s['Data'])) { echo "无结果\n"; usleep(300000); continue; }
    $hit = 0;
    foreach (array_slice($s['Data'],0,3) as $row) {
      $frn = $row['Reference Number'] ?? '';
      if (!ctype_digit((string)$frn)) continue;
      $e = fetch_firm($frn, $API, $H);
      if ($e) { $entities[] = $e; $hit++; }
      usleep(200000);
    }
    echo "命中 $hit\n";
    usleep(300000);
  }
} else {
  fwrite(STDERR, "用法：php scripts/collect_fca.php names <清单文件> [--dry]\n");
  fwrite(STDERR, "      php scripts/collect_fca.php frn <FRN...> [--dry]\n");
  exit(1);
}

echo "\n采集到 ".count($entities)." 个 FCA 实体。\n";
if ($dry) {
  foreach ($entities as $e) echo "  · {$e['name']}  FRN {$e['license_no']}  [{$e['license_type']}]\n";
  echo "(--dry 模式，未入库)\n";
} else {
  ingest_entities($entities);
}
