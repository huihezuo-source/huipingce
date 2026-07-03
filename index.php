<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/footer.php';

// 口碑榜（有用户评分的交易商，≥1 人）
$rank = db()->query(
    "SELECT id,name,name_en,logo,user_rating_avg,user_rating_count,country,established
     FROM brokers WHERE user_rating_count>0
     ORDER BY user_rating_avg DESC, user_rating_count DESC LIMIT 12"
)->fetchAll();

// 若口碑榜不足，补最新收录
$newest = db()->query("SELECT id,name,name_en,logo,country,established,verified,user_rating_avg,user_rating_count
     FROM brokers ORDER BY created_at DESC LIMIT 8")->fetchAll();

// 监管机构（按信任分）
$regs = db()->query("SELECT * FROM regulators ORDER BY trust_score DESC, sort_order ASC LIMIT 6")->fetchAll();

// 最新资讯
$arts = db()->query("SELECT a.*, c.name cat_name FROM articles a LEFT JOIN article_cats c ON a.cat_id=c.id
     WHERE ".published_where('a')." ORDER BY COALESCE(a.publish_at,a.created_at) DESC LIMIT 6")->fetchAll();

// 最新曝光
$exps = db()->query("SELECT c.*, b.name bname FROM complaints c LEFT JOIN brokers b ON c.broker_id=b.id
     WHERE c.status<>'rejected' ORDER BY c.created_at DESC LIMIT 6")->fetchAll();

// 统计
$stat_broker = (int)db()->query("SELECT COUNT(*) FROM brokers")->fetchColumn();
$stat_reg    = (int)db()->query("SELECT COUNT(*) FROM regulators")->fetchColumn();
$stat_ent    = (int)db()->query("SELECT COUNT(*) FROM reg_entities")->fetchColumn();
$stat_review = (int)db()->query("SELECT COUNT(*) FROM broker_reviews WHERE status='approved'")->fetchColumn();

header_html('', ['canonical'=>'/']);
?>
<section class="hero reveal">
  <h1>外汇经纪商 · 监管信息与用户口碑</h1>
  <p>聚合全球监管机构与受监管实体，交易者自由打分、写测评、曝光维权 —— 帮你看清每一家交易商。</p>
  <form class="hero-search" action="/search.php" method="get">
    <input type="text" name="q" placeholder="输入交易商名称 / 监管机构，如 FCA、IC Markets" autocomplete="off">
    <button type="submit">搜索</button>
  </form>
  <div class="hero-stats">
    <div class="hero-stat"><b><?=number_format($stat_broker)?></b><span>收录交易商</span></div>
    <div class="hero-stat"><b><?=$stat_reg?></b><span>监管机构</span></div>
    <div class="hero-stat"><b><?=number_format($stat_ent)?></b><span>受监管实体</span></div>
    <div class="hero-stat"><b><?=number_format($stat_review)?></b><span>用户测评</span></div>
  </div>
</section>

<div class="home-cols">
  <div class="home-main">
    <!-- 口碑榜 -->
    <div class="card reveal" style="margin-bottom:24px">
      <div class="section-hd" style="padding:18px 20px 0;margin:0">
        <h2>🏆 交易商口碑榜</h2>
        <a class="more" href="/brokers.php?sort=rating">查看完整榜单 ›</a>
      </div>
      <div class="rank-list">
        <?php if (!$rank): ?>
          <div class="empty"><p>暂无用户评分，快去<a href="/brokers.php">给交易商打分</a>吧</p></div>
        <?php else: foreach ($rank as $i=>$b):
          $tier = score_tier($b['user_rating_avg']*2); ?>
          <a class="rank-item" href="/broker-detail.php?id=<?=h($b['id'])?>">
            <span class="rank-no"><?=$i+1?></span>
            <?=logo_html($b['logo'],$b['name'],'rank-logo')?>
            <div class="rank-main">
              <div class="rank-name"><?=h($b['name'])?></div>
              <div class="rank-meta"><?=h($b['country']?:'—')?><?=$b['established']?' · 成立'.h($b['established']).'年':''?></div>
            </div>
            <div class="rank-score">
              <span class="rating-num <?=$tier?>"><?=number_format($b['user_rating_avg']*2,1)?></span>
              <small><?=$b['user_rating_count']?>人评</small>
            </div>
          </a>
        <?php endforeach; endif; ?>
      </div>
    </div>

    <!-- 最新资讯 -->
    <div class="card reveal" style="padding:18px 20px">
      <div class="section-hd"><h2>📰 行业资讯</h2><a class="more" href="/articles.php">更多 ›</a></div>
      <?php if (!$arts): ?>
        <div class="empty"><p>暂无资讯</p></div>
      <?php else: foreach ($arts as $a): ?>
        <div class="art-item" style="padding:12px 0">
          <?php if(trim((string)$a['cover'])!==''): ?><img class="art-cover" style="width:120px;height:78px" src="<?=h($a['cover'])?>" alt=""><?php endif; ?>
          <div class="art-main">
            <h3><a href="/article-detail.php?id=<?=h($a['id'])?>"><?=h($a['title'])?></a></h3>
            <div class="art-meta">
              <?php if($a['cat_name']): ?><span class="badge badge-green"><?=h($a['cat_name'])?></span><?php endif; ?>
              <span><?=time_ago($a['publish_at']?:$a['created_at'])?></span>
              <span><?=num_short($a['views'])?> 阅读</span>
            </div>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>

  <!-- 侧栏 -->
  <aside class="home-side">
    <div class="card side-card reveal">
      <h3>🛡️ 权威监管机构</h3>
      <div class="side-list">
        <?php foreach ($regs as $r): ?>
          <a href="/regulator-detail.php?id=<?=h($r['id'])?>">
            <span class="reg-flag" style="font-size:18px"><?=h($r['flag'])?></span>
            <span style="flex:1"><b><?=h($r['name'])?></b> <span class="muted" style="font-size:12px"><?=h($r['country'])?></span></span>
            <span class="grade <?=grade_class($r['grade'])?>"><?=h($r['grade'])?></span>
          </a>
        <?php endforeach; ?>
      </div>
      <a class="btn btn-block" style="margin-top:12px" href="/regulators.php">全部监管机构</a>
    </div>

    <div class="card side-card reveal">
      <h3>⚠️ 最新曝光</h3>
      <div class="side-list">
        <?php if(!$exps): ?><p class="muted" style="font-size:13px">暂无曝光</p>
        <?php else: foreach ($exps as $c): [$sl,$sc]=complaint_status_label($c['status']); ?>
          <a href="/exposure-detail.php?id=<?=h($c['id'])?>" style="align-items:flex-start">
            <span class="badge badge-red" style="flex-shrink:0"><?=h($c['type'])?></span>
            <span style="flex:1; line-height:1.4"><?=h(mb_strimwidth($c['title'],0,34,'…','UTF-8'))?>
              <span class="pill <?=$sc?>" style="font-size:11px"><?=$sl?></span></span>
          </a>
        <?php endforeach; endif; ?>
      </div>
      <a class="btn btn-block" style="margin-top:12px" href="/exposure.php">查看曝光台</a>
    </div>
  </aside>
</div>

<?php footer_html(); ?>
