<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/footer.php';

$id=$_GET['id']??'';
$st=db()->prepare("SELECT a.*, c.name cat_name, c.slug cat_slug, b.name bname
    FROM articles a LEFT JOIN article_cats c ON a.cat_id=c.id LEFT JOIN brokers b ON a.broker_id=b.id
    WHERE a.id=? AND ".published_where('a')." LIMIT 1");
$st->execute([$id]); $a=$st->fetch();
if(!$a){ http_response_code(404); header_html('articles'); echo '<div class="empty"><div class="ico">🚫</div><p>文章不存在或未发布</p><a class="btn" href="/articles.php">返回资讯</a></div>'; footer_html(); exit; }

db()->prepare('UPDATE articles SET views=views+1 WHERE id=?')->execute([$id]);

// 相关（同分类）
$rel=db()->prepare("SELECT id,title FROM articles WHERE cat_id<=>? AND id<>? AND ".published_where()." ORDER BY COALESCE(publish_at,created_at) DESC LIMIT 6");
$rel->execute([$a['cat_id'],$id]); $rel=$rel->fetchAll();

header_html('articles', [
    'title'=>$a['title'],
    'desc'=>$a['summary']?:mb_strimwidth(strip_tags($a['content']),0,120,'…','UTF-8'),
    'og'=>['type'=>'article','image'=>$a['cover']],
]);
?>
<div class="crumb"><a href="/">首页</a> › <a href="/articles.php">资讯</a><?=$a['cat_slug']?' › <a href="/articles.php?cat='.h($a['cat_slug']).'">'.h($a['cat_name']).'</a>':''?> › <b>详情</b></div>

<div class="layout">
  <article class="card panel">
    <h1 style="font-size:26px;line-height:1.4"><?=h($a['title'])?></h1>
    <div class="art-meta" style="margin:14px 0 20px;padding-bottom:16px;border-bottom:1px solid var(--line)">
      <?php if($a['cat_name']): ?><span class="badge badge-green"><?=h($a['cat_name'])?></span><?php endif; ?>
      <span><?=h($a['author'])?></span>
      <?php if($a['source']): ?><span>来源 <?=h($a['source'])?></span><?php endif; ?>
      <span><?=date('Y-m-d',$a['publish_at']?:$a['created_at'])?></span>
      <span><?=num_short($a['views']+1)?> 阅读</span>
    </div>
    <?php if($a['bname']): ?>
    <div class="rate-box" style="margin-bottom:18px">
      <span>本文关联交易商：<b><?=h($a['bname'])?></b></span>
      <a class="btn" href="/broker-detail.php?id=<?=h($a['broker_id'])?>">查看监管与评分 ›</a>
    </div>
    <?php endif; ?>
    <div class="art-body"><?=$a['content']?></div>
    <?php if($a['tags']): ?><div style="margin-top:24px"><?php foreach(explode(',',$a['tags']) as $t){ if(trim($t)) echo '<span class="tag">'.h(trim($t)).'</span>'; } ?></div><?php endif; ?>
  </article>

  <aside>
    <?php if($rel): ?>
    <div class="card side-card">
      <h3>相关资讯</h3>
      <div class="side-list">
        <?php foreach($rel as $x): ?>
          <a href="/article-detail.php?id=<?=h($x['id'])?>"><?=h(mb_strimwidth($x['title'],0,42,'…','UTF-8'))?></a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
    <div class="card side-card">
      <h3>快捷入口</h3>
      <div class="side-list">
        <a href="/brokers.php">🏆 交易商口碑榜</a>
        <a href="/regulators.php">🛡️ 监管机构查询</a>
        <a href="/exposure.php">⚠️ 曝光维权台</a>
      </div>
    </div>
  </aside>
</div>

<?php footer_html(); ?>
