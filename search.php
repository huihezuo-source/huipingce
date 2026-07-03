<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/footer.php';

$q = trim($_GET['q'] ?? '');
$brokers=$regs=$arts=[];
if ($q!=='') {
    $like="%$q%";
    $s=db()->prepare("SELECT id,name,name_en,logo,country,user_rating_avg,user_rating_count FROM brokers WHERE name LIKE ? OR name_en LIKE ? ORDER BY user_rating_count DESC LIMIT 20");
    $s->execute([$like,$like]); $brokers=$s->fetchAll();
    $s=db()->prepare("SELECT * FROM regulators WHERE name LIKE ? OR full_name LIKE ? OR country LIKE ? LIMIT 10");
    $s->execute([$like,$like,$like]); $regs=$s->fetchAll();
    $s=db()->prepare("SELECT id,title FROM articles WHERE title LIKE ? AND ".published_where()." ORDER BY COALESCE(publish_at,created_at) DESC LIMIT 10");
    $s->execute([$like]); $arts=$s->fetchAll();
}

header_html('', ['title'=>$q?'搜索“'.$q.'”':'搜索']);
?>
<div class="crumb"><a href="/">首页</a> › <b>搜索</b></div>
<h2 style="margin-bottom:16px">搜索<?=$q?'：“'.h($q).'”':''?></h2>

<?php if($q===''): ?>
  <div class="card empty"><div class="ico">🔍</div><p>输入交易商名称或监管机构开始搜索</p></div>
<?php else: ?>

<?php if($brokers): ?>
<div class="card" style="margin-bottom:18px">
  <div class="panel-hd" style="padding:16px 18px 12px;margin:0">交易商 <span class="cnt"><?=count($brokers)?></span></div>
  <?php foreach($brokers as $b): $tier=score_tier($b['user_rating_avg']*2); ?>
    <a class="rank-item" href="/broker-detail.php?id=<?=h($b['id'])?>">
      <?=logo_html($b['logo'],$b['name'],'rank-logo')?>
      <div class="rank-main"><div class="rank-name"><?=h($b['name'])?> <span class="muted" style="font-weight:400;font-size:12px"><?=h($b['name_en'])?></span></div><div class="rank-meta"><?=h($b['country']?:'—')?></div></div>
      <div class="rank-score"><?php if($b['user_rating_count']>0): ?><span class="rating-num <?=$tier?>" style="font-size:20px"><?=number_format($b['user_rating_avg']*2,1)?></span><?php else: ?><span class="muted" style="font-size:12px">暂无评分</span><?php endif; ?></div>
    </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if($regs): ?>
<div class="card" style="margin-bottom:18px">
  <div class="panel-hd" style="padding:16px 18px 12px;margin:0">监管机构 <span class="cnt"><?=count($regs)?></span></div>
  <div class="reg-grid" style="padding:16px">
    <?php foreach($regs as $r): ?>
      <a class="card reg-card" href="/regulator-detail.php?id=<?=h($r['id'])?>">
        <div class="reg-card-hd"><span class="reg-flag"><?=h($r['flag'])?></span><div><div class="rname"><?=h($r['name'])?> <span class="grade <?=grade_class($r['grade'])?>"><?=h($r['grade'])?></span></div><div class="rfull"><?=h($r['full_name'])?></div></div></div>
      </a>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?php if($arts): ?>
<div class="card" style="margin-bottom:18px">
  <div class="panel-hd" style="padding:16px 18px 12px;margin:0">资讯 <span class="cnt"><?=count($arts)?></span></div>
  <div class="side-list" style="padding:8px 18px 16px">
    <?php foreach($arts as $a): ?><a href="/article-detail.php?id=<?=h($a['id'])?>"><?=h($a['title'])?></a><?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?php if(!$brokers && !$regs && !$arts): ?>
  <div class="card empty"><div class="ico">🤷</div><p>没有找到与“<?=h($q)?>”相关的内容</p></div>
<?php endif; ?>

<?php endif; ?>
<?php footer_html(); ?>
