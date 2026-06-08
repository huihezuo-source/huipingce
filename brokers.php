<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/footer.php';

$q    = trim($_GET['q'] ?? '');
$sort = $_GET['sort'] ?? 'score';
$page = max(1, (int)($_GET['page'] ?? 1));

$cond = '1=1'; $args = [];
if ($q !== '') { $cond .= ' AND (name LIKE ? OR name_en LIKE ?)'; $args[]="%$q%"; $args[]="%$q%"; }

$order = match($sort){
  'newest' => 'created_at DESC',
  'mindep' => 'min_dep ASC, score DESC',
  default  => 'score DESC, featured DESC, sort_order ASC',
};

$total = (int)(function() use($cond,$args){ $s=db()->prepare("SELECT COUNT(*) FROM brokers WHERE $cond"); $s->execute($args); return $s->fetchColumn(); })();
$pg = admin_paginate($page, $total, 12);
$st = db()->prepare("SELECT * FROM brokers WHERE $cond ORDER BY (score IS NULL), $order LIMIT {$pg['per']} OFFSET {$pg['offset']}");
$st->execute($args);
$brokers = $st->fetchAll();

header_html('brokers', [
  'title'=>'外汇经纪商列表 · 监管核验与评分',
  'desc' =>'汇评测收录外汇经纪商品牌，展示其关联的各监管机构受监管实体、监管评级与综合评分，帮你快速核验与对比。',
  'kw'   =>'外汇经纪商,经纪商对比,经纪商评分,外汇券商,监管核验',
]);
?>
<section class="sec-sm">
  <div class="container">
    <div class="crumb"><a href="/">首页</a><span class="sep">/</span><span>外汇经纪商</span></div>
    <div class="sec-head"><div><span class="eyebrow">Brokers</span><h2>外汇经纪商</h2>
      <p style="color:var(--ink-2);margin-top:6px">收录 <b><?= $total ?></b> 家经纪商品牌，每家关联其受监管实体与评分</p></div></div>
  </div>

  <div class="filter-bar">
    <div class="container filter-in">
      <form class="search-box" method="get" style="flex:1">
        <span>🔍</span><input type="text" name="q" value="<?= h($q) ?>" placeholder="搜索经纪商品牌…">
        <?php if($sort): ?><input type="hidden" name="sort" value="<?= h($sort) ?>"><?php endif; ?>
      </form>
      <div class="chips">
        <?php
        $sorts = ['score'=>'综合评分','newest'=>'最新收录','mindep'=>'低门槛'];
        foreach($sorts as $k=>$lbl): $u='/brokers.php?sort='.$k.($q?'&q='.urlencode($q):''); ?>
        <a class="chip<?= $sort===$k?' on':'' ?>" href="<?= $u ?>"><?= $lbl ?></a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="container">
    <?php if($brokers): ?>
    <div class="grid grid-3">
      <?php foreach($brokers as $b):
        $ents = broker_entities($b['id'], $b['name']);
        $regset=[]; foreach($ents as $e) if(!empty($e['reg_name'])) $regset[$e['reg_name']]=$e['reg_grade'];
      ?>
      <a class="card broker-card reveal" href="/broker-detail.php?id=<?= h($b['id']) ?>">
        <div class="bc-top">
          <div class="bc-logo"><?php if($b['logo']): ?><img src="<?= h($b['logo']) ?>" alt=""><?php else: ?><?= h(mb_substr($b['name'],0,1)) ?><?php endif; ?></div>
          <div style="flex:1"><div class="bc-name"><?= h($b['name']) ?><?php if($b['name_en'] && $b['name_en']!==$b['name']): ?><div class="en"><?= h($b['name_en']) ?></div><?php endif; ?></div></div>
          <?php if($b['score']!==null): $t=score_tier($b['score']); ?>
          <div class="ring <?= $t ?>" data-p="<?= $b['score']*10 ?>" style="--sz:48px"><span class="ring-v <?= $t ?>"><?= h($b['score']) ?></span></div>
          <?php endif; ?>
        </div>
        <?php if($b['summary']): ?><p style="font-size:13.5px;color:var(--ink-2);margin-bottom:12px;line-height:1.55;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden"><?= h($b['summary']) ?></p><?php endif; ?>
        <div class="bc-regs">
          <?php if($regset): foreach(array_slice($regset,0,5,true) as $rn=>$gr): ?><span class="badge <?= grade_class($gr) ?>"><?= h($rn) ?></span><?php endforeach; else: ?><span class="tag">监管核验中</span><?php endif; ?>
        </div>
        <div class="bc-stats">
          <div class="bc-stat"><div class="v"><?= h($b['leverage']) ?></div><div class="l">最高杠杆</div></div>
          <div class="bc-stat"><div class="v"><?= $b['min_dep']>0?'$'.h($b['min_dep']):'—' ?></div><div class="l">最低入金</div></div>
          <div class="bc-stat"><div class="v"><?= $b['established']?h($b['established']):'—' ?></div><div class="l">成立</div></div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?= admin_pager_html($pg, '/brokers.php?sort='.h($sort).($q?'&q='.urlencode($q):'').'&') ?>
    <?php else: ?>
      <div class="empty"><div class="ico">🏦</div><h3><?= $q?'未匹配到经纪商':'经纪商收录进行中' ?></h3><p>正在从各监管机构采集经纪商与受监管实体数据。</p></div>
    <?php endif; ?>
  </div>
</section>
<?php footer_html();