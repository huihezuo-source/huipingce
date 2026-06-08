<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/footer.php';

$tag  = trim($_GET['tag'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));

$cond = published_where(); $args = [];
if ($tag !== '') { $cond .= ' AND tags LIKE ?'; $args[]="%$tag%"; }

$total = (int)(function() use($cond,$args){ $s=db()->prepare("SELECT COUNT(*) FROM reviews WHERE $cond"); $s->execute($args); return $s->fetchColumn(); })();
$pg = admin_paginate($page, $total, 9);
$st = db()->prepare(
  "SELECT r.*, b.name AS broker_name FROM reviews r LEFT JOIN brokers b ON r.broker_id=b.id
   WHERE $cond ORDER BY COALESCE(r.publish_at,r.created_at) DESC LIMIT {$pg['per']} OFFSET {$pg['offset']}"
);
$st->execute($args);
$reviews = $st->fetchAll();

// 标签云
$tagrows = db()->query("SELECT tags FROM reviews WHERE ".published_where()." AND tags<>''")->fetchAll();
$tagset = [];
foreach($tagrows as $tr) foreach(explode(',',$tr['tags']) as $t){ $t=trim($t); if($t) $tagset[$t]=($tagset[$t]??0)+1; }
arsort($tagset);

header_html('reviews', [
  'title'=>'外汇经纪商评测',
  'desc' =>'汇评测每日更新外汇经纪商深度评测，多维评分覆盖监管安全、交易成本、出入金、平台体验等维度，帮你客观判断每一家经纪商。',
  'kw'   =>'外汇经纪商评测,经纪商深度评测,外汇券商评分,经纪商对比',
]);
?>
<section class="sec-sm">
  <div class="container">
    <div class="crumb"><a href="/">首页</a><span class="sep">/</span><span>评测</span></div>
    <div class="sec-head"><div><span class="eyebrow">Reviews</span><h2>经纪商评测</h2>
      <p style="color:var(--ink-2);margin-top:6px">每日更新 · 多维评分 · 共 <b><?= $total ?></b> 篇深度评测</p></div></div>

    <?php if($tagset): ?>
    <div class="chips" style="margin-bottom:24px">
      <a class="chip<?= $tag===''?' on':'' ?>" href="/reviews.php">全部</a>
      <?php foreach(array_slice($tagset,0,8,true) as $t=>$n): ?>
      <a class="chip<?= $tag===$t?' on':'' ?>" href="/reviews.php?tag=<?= urlencode($t) ?>"><?= h($t) ?></a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if($reviews): ?>
    <div class="grid grid-3">
      <?php foreach($reviews as $r): $tier=score_tier($r['overall_score']); ?>
      <a class="card review-card reveal" href="/review-detail.php?id=<?= h($r['id']) ?>">
        <?php if($r['cover']): ?>
          <div class="rc-cover"><img src="<?= h($r['cover']) ?>" alt="<?= h($r['title']) ?>" loading="lazy">
        <?php else: ?>
          <div class="rc-cover placeholder"><span><?= h(mb_substr($r['broker_name'] ?: $r['title'],0,2)) ?></span>
        <?php endif; ?>
            <?php if($r['overall_score']!==null): ?><div class="rc-score"><span class="sv <?= $tier ?>"><?= h($r['overall_score']) ?></span><span class="sl">总分</span></div><?php endif; ?>
          </div>
        <div class="rc-body">
          <?php if($r['tags']): ?><div class="rc-tags"><?php foreach(array_slice(explode(',',$r['tags']),0,2) as $t): ?><span class="tag"><?= h(trim($t)) ?></span><?php endforeach; ?></div><?php endif; ?>
          <div class="rc-title"><?= h($r['title']) ?></div>
          <div class="rc-verdict"><?= h($r['verdict'] ?: $r['summary']) ?></div>
          <div class="rc-foot"><span><?= h($r['author']) ?></span><span class="dot"></span><span><?= (int)$r['read_time'] ?> 分钟读</span><span class="dot"></span><span><?= h(date('Y-m-d', $r['publish_at'] ?: $r['created_at'])) ?></span></div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?= admin_pager_html($pg, '/reviews.php?'.($tag?'tag='.urlencode($tag).'&':'')) ?>
    <?php else: ?>
      <div class="empty"><div class="ico">📝</div><h3>评测即将上线</h3><p>评测团队正在打磨首批经纪商深度评测，敬请期待。</p></div>
    <?php endif; ?>
  </div>
</section>
<?php footer_html();