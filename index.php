<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/footer.php';

// 统计
$cnt_broker = (int)db()->query('SELECT COUNT(*) FROM brokers')->fetchColumn();
$cnt_entity = (int)db()->query('SELECT COUNT(*) FROM reg_entities')->fetchColumn();
$cnt_review = (int)db()->query("SELECT COUNT(*) FROM reviews WHERE ".published_where())->fetchColumn();
$cnt_reg    = (int)db()->query('SELECT COUNT(*) FROM regulators')->fetchColumn();

// 最新评测（最多 3）
$latest = db()->query(
  "SELECT r.*, b.name AS broker_name FROM reviews r
   LEFT JOIN brokers b ON r.broker_id=b.id
   WHERE ".published_where()."
   ORDER BY COALESCE(r.publish_at,r.created_at) DESC LIMIT 3"
)->fetchAll();

// 精选经纪商（最多 6）
$brokers = db()->query(
  "SELECT * FROM brokers ORDER BY featured DESC, score DESC, sort_order ASC LIMIT 6"
)->fetchAll();

// 监管机构（按评级，最多 8）
$regs = db()->query("SELECT * FROM regulators ORDER BY sort_order ASC LIMIT 8")->fetchAll();

header_html('', [
  'desc' => setting('siteDesc'),
  'kw'   => setting('siteKeywords'),
]);
?>
<section class="hero">
  <div class="hero-in">
    <span class="pill"><b>NEW</b> 每日更新经纪商深度评测</span>
    <h1>看懂外汇经纪商<br>从一份<span class="hl">客观评测</span>开始</h1>
    <p>汇评测覆盖全球监管机构、受监管实体公司与经纪商品牌，用多维评分体系帮你把每一家经纪商看得清清楚楚。</p>
    <form class="hero-search" action="/brokers.php" method="get">
      <input type="text" name="q" placeholder="搜索经纪商，如 IC Markets、Pepperstone…" aria-label="搜索经纪商">
      <button type="submit" class="btn btn-primary">搜索</button>
    </form>
    <div class="hero-stats">
      <div class="hero-stat"><div class="v num" data-count="<?= $cnt_broker ?>" data-dec="0">0</div><div class="l">收录经纪商</div></div>
      <div class="hero-stat"><div class="v num" data-count="<?= $cnt_entity ?>" data-dec="0">0</div><div class="l">受监管实体</div></div>
      <div class="hero-stat"><div class="v num" data-count="<?= $cnt_reg ?>" data-dec="0">0</div><div class="l">监管机构</div></div>
      <div class="hero-stat"><div class="v num" data-count="<?= $cnt_review ?>" data-dec="0">0</div><div class="l">深度评测</div></div>
    </div>
  </div>
</section>

<!-- 最新评测 -->
<section class="sec">
  <div class="container">
    <div class="sec-head">
      <div><span class="eyebrow">Latest Reviews</span><h2>最新评测</h2></div>
      <a class="more" href="/reviews.php">全部评测</a>
    </div>
    <?php if ($latest): ?>
    <div class="grid grid-3">
      <?php foreach ($latest as $r): $tier=score_tier($r['overall_score']); ?>
      <a class="card review-card reveal" href="/review-detail.php?id=<?= h($r['id']) ?>">
        <?php if ($r['cover']): ?>
          <div class="rc-cover"><img src="<?= h($r['cover']) ?>" alt="<?= h($r['title']) ?>" loading="lazy">
            <?php if($r['overall_score']!==null): ?><div class="rc-score"><span class="sv <?= $tier ?>"><?= h($r['overall_score']) ?></span><span class="sl">总分</span></div><?php endif; ?>
          </div>
        <?php else: ?>
          <div class="rc-cover placeholder"><span><?= h(mb_substr($r['broker_name'] ?: $r['title'],0,2)) ?></span>
            <?php if($r['overall_score']!==null): ?><div class="rc-score"><span class="sv <?= $tier ?>"><?= h($r['overall_score']) ?></span><span class="sl">总分</span></div><?php endif; ?>
          </div>
        <?php endif; ?>
        <div class="rc-body">
          <?php if($r['tags']): ?><div class="rc-tags"><?php foreach(array_slice(explode(',',$r['tags']),0,2) as $t): ?><span class="tag"><?= h(trim($t)) ?></span><?php endforeach; ?></div><?php endif; ?>
          <div class="rc-title"><?= h($r['title']) ?></div>
          <div class="rc-verdict"><?= h($r['verdict'] ?: $r['summary']) ?></div>
          <div class="rc-foot"><span><?= h($r['author']) ?></span><span class="dot"></span><span><?= h(date('Y-m-d', $r['publish_at'] ?: $r['created_at'])) ?></span></div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
      <div class="empty"><div class="ico">📝</div><h3>评测即将上线</h3><p>评测团队正在打磨首批经纪商深度评测，敬请期待。</p></div>
    <?php endif; ?>
  </div>
</section>

<!-- 精选经纪商 -->
<section class="sec" style="background:var(--surface);border-top:1px solid var(--border);border-bottom:1px solid var(--border)">
  <div class="container">
    <div class="sec-head">
      <div><span class="eyebrow">Brokers</span><h2>精选经纪商</h2></div>
      <a class="more" href="/brokers.php">全部经纪商</a>
    </div>
    <?php if ($brokers): ?>
    <div class="grid grid-3">
      <?php foreach ($brokers as $b):
        $ents = broker_entities($b['id'], $b['name']);
        $regset = [];
        foreach ($ents as $e) if(!empty($e['reg_name'])) $regset[$e['reg_name']] = $e['reg_grade'];
      ?>
      <a class="card broker-card reveal" href="/broker-detail.php?id=<?= h($b['id']) ?>">
        <div class="bc-top">
          <div class="bc-logo"><?php if($b['logo']): ?><img src="<?= h($b['logo']) ?>" alt=""><?php else: ?><?= h(mb_substr($b['name'],0,1)) ?><?php endif; ?></div>
          <div><div class="bc-name"><?= h($b['name']) ?><?php if($b['name_en']): ?><div class="en"><?= h($b['name_en']) ?></div><?php endif; ?></div></div>
          <?php if($b['score']!==null): $t=score_tier($b['score']); ?>
          <div class="bc-meta"><div class="ring <?= $t ?>" data-p="<?= $b['score']*10 ?>" style="--sz:46px"><span class="ring-v <?= $t ?>"><?= h($b['score']) ?></span></div></div>
          <?php endif; ?>
        </div>
        <div class="bc-regs">
          <?php if($regset): foreach(array_slice($regset,0,4,true) as $rn=>$gr): ?><span class="badge <?= grade_class($gr) ?>"><?= h($rn) ?></span><?php endforeach; else: ?><span class="tag">监管核验中</span><?php endif; ?>
        </div>
        <div class="bc-stats">
          <div class="bc-stat"><div class="v"><?= h($b['leverage']) ?></div><div class="l">最高杠杆</div></div>
          <div class="bc-stat"><div class="v"><?= $b['min_dep']>0?'$'.h($b['min_dep']):'—' ?></div><div class="l">最低入金</div></div>
          <div class="bc-stat"><div class="v"><?= h($b['platform']) ?></div><div class="l">平台</div></div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
      <div class="empty"><div class="ico">🏦</div><h3>经纪商收录进行中</h3><p>正在从各监管机构采集经纪商与受监管实体数据。</p></div>
    <?php endif; ?>
  </div>
</section>

<!-- 监管机构 -->
<section class="sec">
  <div class="container">
    <div class="sec-head">
      <div><span class="eyebrow">Regulators</span><h2>全球监管机构</h2></div>
      <a class="more" href="/regulators.php">全部监管</a>
    </div>
    <div class="grid grid-4">
      <?php foreach ($regs as $rg): ?>
      <a class="card reg-card reveal" href="/regulator-detail.php?id=<?= h($rg['id']) ?>">
        <div class="reg-top">
          <span class="reg-flag"><?= h($rg['flag'] ?: '🏛️') ?></span>
          <div class="reg-name"><?= h($rg['name']) ?> <span class="badge <?= grade_class($rg['grade']) ?>"><?= h($rg['grade']) ?></span></div>
        </div>
        <div class="reg-foot">
          <span class="ec"><?= h($rg['country']) ?></span>
          <span class="ec"><b><?= (int)$rg['entity_count'] ?></b> 家实体</span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php footer_html();
