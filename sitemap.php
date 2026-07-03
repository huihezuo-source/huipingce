<?php
require_once __DIR__ . '/includes/db.php';
header('Content-Type: application/xml; charset=utf-8');
$base = rtrim(setting('siteUrl','https://www.huipingce.com'), '/');
echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

function u($loc,$lastmod=null,$freq='weekly',$pri='0.6'){
  echo "<url><loc>".htmlspecialchars($loc)."</loc>";
  if($lastmod) echo "<lastmod>".date('Y-m-d',$lastmod)."</lastmod>";
  echo "<changefreq>$freq</changefreq><priority>$pri</priority></url>\n";
}

// 静态页
u($base.'/', time(), 'daily', '1.0');
u($base.'/regulators.php', time(), 'weekly', '0.8');
u($base.'/brokers.php', time(), 'daily', '0.9');
u($base.'/articles.php', time(), 'daily', '0.8');
u($base.'/exposure.php', time(), 'daily', '0.7');

// 监管机构
foreach (db()->query('SELECT id,updated_at FROM regulators')->fetchAll() as $r)
  u($base.'/regulator-detail.php?id='.$r['id'], $r['updated_at'] ?: time(), 'weekly', '0.7');

// 交易商
foreach (db()->query('SELECT id,updated_at FROM brokers')->fetchAll() as $b)
  u($base.'/broker-detail.php?id='.$b['id'], $b['updated_at'] ?: time(), 'weekly', '0.8');

// 资讯
foreach (db()->query("SELECT id,COALESCE(publish_at,created_at) AS t FROM articles WHERE ".published_where())->fetchAll() as $a)
  u($base.'/article-detail.php?id='.$a['id'], $a['t'], 'monthly', '0.6');

// 曝光
foreach (db()->query("SELECT id,created_at FROM complaints WHERE status<>'rejected'")->fetchAll() as $c)
  u($base.'/exposure-detail.php?id='.$c['id'], $c['created_at'], 'monthly', '0.5');

echo '</urlset>';
