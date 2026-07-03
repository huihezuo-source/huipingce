<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/footer.php';

$cats = article_cats();
$cat  = trim($_GET['cat'] ?? '');
$page = max(1,(int)($_GET['page']??1)); $per=12; $off=($page-1)*$per;

$where = published_where('a'); $p=[];
$catRow = null;
if ($cat!==''){
    foreach($cats as $c){ if($c['slug']===$cat){ $catRow=$c; break; } }
    if($catRow){ $where.=' AND a.cat_id=?'; $p[]=$catRow['id']; }
}
$cnt=db()->prepare("SELECT COUNT(*) FROM articles a WHERE $where"); $cnt->execute($p);
$total=(int)$cnt->fetchColumn(); $pages=max(1,(int)ceil($total/$per));
$page=min($page,$pages); $off=($page-1)*$per;

$st=db()->prepare("SELECT a.*, c.name cat_name, c.slug cat_slug, b.name bname
    FROM articles a LEFT JOIN article_cats c ON a.cat_id=c.id LEFT JOIN brokers b ON a.broker_id=b.id
    WHERE $where ORDER BY COALESCE(a.publish_at,a.created_at) DESC LIMIT $per OFFSET $off");
$st->execute($p); $arts=$st->fetchAll();

header_html('articles', [
    'title'=>($catRow?$catRow['name'].' · ':'').'外汇资讯',
    'desc'=>'外汇行业动态、交易商快讯、监管公告与曝光维权资讯。'
]);
?>
<div class="crumb"><a href="/">首页</a> › <b>资讯</b><?=$catRow?' › '.h($catRow['name']):''?></div>

<div class="card filters" style="margin-bottom:18px">
  <div class="fgroup">
    <span class="flabel">分类</span>
    <a class="chip<?=$cat===''?' on':''?>" href="/articles.php">全部</a>
    <?php foreach($cats as $c): ?>
      <a class="chip<?=$cat===$c['slug']?' on':''?>" href="/articles.php?cat=<?=h($c['slug'])?>"><?=h($c['name'])?></a>
    <?php endforeach; ?>
  </div>
</div>

<div class="card" style="padding:6px 22px">
  <?php if(!$arts): ?>
    <div class="empty"><div class="ico">📰</div><p>该分类暂无资讯</p></div>
  <?php else: foreach($arts as $a): ?>
    <div class="art-item">
      <?php if(trim((string)$a['cover'])!==''): ?><a href="/article-detail.php?id=<?=h($a['id'])?>"><img class="art-cover" src="<?=h($a['cover'])?>" alt=""></a><?php endif; ?>
      <div class="art-main">
        <h3><a href="/article-detail.php?id=<?=h($a['id'])?>"><?=h($a['title'])?></a></h3>
        <?php if($a['summary']): ?><p class="art-sum"><?=h($a['summary'])?></p><?php endif; ?>
        <div class="art-meta">
          <?php if($a['cat_name']): ?><span class="badge badge-green"><?=h($a['cat_name'])?></span><?php endif; ?>
          <?php if($a['bname']): ?><a href="/broker-detail.php?id=<?=h($a['broker_id'])?>" class="badge badge-blue"><?=h($a['bname'])?></a><?php endif; ?>
          <span><?=h($a['author'])?></span>
          <span><?=time_ago($a['publish_at']?:$a['created_at'])?></span>
          <span><?=num_short($a['views'])?> 阅读</span>
        </div>
      </div>
    </div>
  <?php endforeach; endif; ?>
</div>

<?php if($pages>1): $qs=function($pp)use($cat){return '/articles.php?'.($cat?'cat='.$cat.'&':'').'page='.$pp;}; ?>
<div class="pager">
  <?php for($i=max(1,$page-3);$i<=min($pages,$page+3);$i++): ?>
    <?php if($i==$page): ?><span class="cur"><?=$i?></span><?php else: ?><a href="<?=$qs($i)?>"><?=$i?></a><?php endif; ?>
  <?php endfor; ?>
</div>
<?php endif; ?>

<?php footer_html(); ?>
