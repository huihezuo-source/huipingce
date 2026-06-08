<?php
/**
 * 采集脚本共享库
 * - 读取配置（API key / 入库地址 / cron_key）
 * - HTTP GET 封装
 * - 批量推送实体到 entities_bulk_insert.php
 */

// 配置：优先 scripts/secret.php（不进 git），否则环境变量
$__cfg = [
  'fca_email'   => getenv('FCA_EMAIL')   ?: '',
  'fca_key'     => getenv('FCA_KEY')     ?: '',
  'ingest_url'  => getenv('HPC_INGEST')  ?: 'http://127.0.0.1:8800/admin/api/entities_bulk_insert.php',
  'cron_key'    => getenv('HPC_CRON_KEY')?: 'huipingce2026',
];
if (is_file(__DIR__ . '/secret.php')) {
  $__cfg = array_merge($__cfg, (require __DIR__ . '/secret.php'));
}
function cfg($k){ global $__cfg; return $__cfg[$k] ?? ''; }

function http_get_json($url, $headers = []) {
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTPHEADER => array_merge(['Accept: application/json'], $headers),
    CURLOPT_USERAGENT => 'HuipingceCollector/1.0',
  ]);
  $body = curl_exec($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $err  = curl_error($ch);
  curl_close($ch);
  if ($body === false) return ['_http'=>0, '_err'=>$err];
  $j = json_decode($body, true);
  if (!is_array($j)) return ['_http'=>$code, '_raw'=>$body];
  $j['_http'] = $code;
  return $j;
}

/** 批量推送实体；$entities = 实体数组 */
function ingest_entities(array $entities, $chunk = 200) {
  if (!$entities) { echo "[ingest] 无数据\n"; return; }
  $total_ok = 0; $total_skip = 0;
  foreach (array_chunk($entities, $chunk) as $i => $batch) {
    $payload = json_encode(['cron_key'=>cfg('cron_key'), 'entities'=>$batch], JSON_UNESCAPED_UNICODE);
    $ch = curl_init(cfg('ingest_url'));
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => $payload,
      CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
      CURLOPT_TIMEOUT => 60,
    ]);
    $resp = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    $r = json_decode($resp, true);
    if ($code === 200 && ($r['ok'] ?? false)) {
      $total_ok += $r['inserted']; $total_skip += $r['skipped'];
      echo "[ingest] batch ".($i+1)."：入库 {$r['inserted']}，跳过 {$r['skipped']}\n";
      if (!empty($r['errors'])) foreach (array_slice($r['errors'],0,3) as $e) echo "         ⚠ $e\n";
    } else {
      echo "[ingest] batch ".($i+1)." 失败 HTTP $code：".substr($resp,0,200)."\n";
    }
  }
  echo "[ingest] 完成：共入库 $total_ok，跳过 $total_skip\n";
}
