<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/footer.php';

$q    = trim($_GET['q'] ?? '');
$sort = $_GET['sort'] ?? 'rating';
$page = max(1, (int)($_GET['page'] ?? 1));
$per  = 20;

$where = '1=1'; $params = [];
if ($q !== '') { $where .= ' AND (name LIKE ? OR name_en LIKE ?)'; $params[]="%$q%"; $params[]="%$q%"; }

$order = match($sort){
    'newest'  => 'created_at DESC',
    'name'    => 'name ASC',
    'reviews' => 'user_rating_count DESC, user_rating_avg DESC',
    default   => '(user_rating_count>0) DESC, user_rating_avg DESC, user_rating_count DESC',
};

$cst = db()->prepare("SELECT COUNT(*) FROM brokers WHERE $where");
$cst->execute($params);
$total = (int)$cst->fetchColumn();
$pages = max(1, (int)ceil($total/$per));
$page  = min($page, $pages);
$off   = ($page-1)*$per;

$st = db()->prepare("SELECT * FROM brokers WHERE $where ORDER BY $order LIMIT $per OFFSET $off");
$st->execute($params);
$brokers = $st->fetchAll();

$sorts = ['rating'=>'口碑评分','reviews'=>'评价最多','newest'=>'最新收录','name'=>'名称'];

header_html('brokers', [
    'title'=>'外汇交易商 · 监管与用户口碑',
    'desc'=>'汇评测收录的外汇交易商列表，含监管牌照、受监管实体与用户真实打分测评。'
]);
?>
<div class="crumb"><a href="/">首页</a> › <b>交易商</b></div>

<div class="card filters">
  <div class="fgroup">
    <span class="flabel">排序</span>
    <?php foreach ($sorts as $k=>$v): $on=$sort===$k?' on':''; ?>
      <a class="chip<?=$on?>" href="/brokers.php?sort=<?=$k?><?=$q?'&q='.urlencode($q):''?>"><?=$v?></a>
    <?php endforeach; ?>
  </div>
  <form action="/brokers.php" method="get" style="margin-left:auto;display:flex;gap:8px">
    <input type="hidden" name="sort" value="<?=h($sort)?>">
    <input type="text" name="q" value="<?=h($q)?>" placeholder="搜索交易商" style="border:1px solid var(--line-d);border-radius:8px;padding:6px 12px;font:inherit;outline:none">
    <button class="btn" type="submit">搜索</button>
  </form>
</div>

<div class="card">
  <?php if (!$brokers): ?>
    <div class="empty"><div class="ico">🔍</div><p>没有找到匹配的交易商</p></div>
  <?php else: foreach ($brokers as $b):
    $rs = broker_reg_summary($b['id'], $b['name']);
    $tier = score_tier($b['user_rating_avg']*2); ?>
    <div class="broker-row">
      <a href="/broker-detail.php?id=<?=h($b['id'])?>"><?=logo_html($b['logo'],$b['name'],'broker-logo')?></a>
      <div class="broker-info">
        <div class="bname">
          <a href="/broker-detail.php?id=<?=h($b['id'])?>" style="color:var(--ink)"><?=h($b['name'])?></a>
          <?php if($b['name_en']): ?><span class="muted" style="font-size:13px;font-weight:400"><?=h($b['name_en'])?></span><?php endif; ?>
          <?php if($b['verified']): ?><span class="badge badge-green">✓ 已核验</span><?php endif; ?>
        </div>
        <div class="bmeta">
          <?php if($b['country']): ?><span>📍 <?=h($b['country'])?></span><?php endif; ?>
          <?php if($b['established']): ?><span>成立 <?=h($b['established'])?> 年</span><?php endif; ?>
          <?php if($b['platform']): ?><span><?=h($b['platform'])?></span><?php endif; ?>
          <?php if($b['leverage']): ?><span>杠杆 <?=h($b['leverage'])?></span><?php endif; ?>
          <?php if((int)$b['complaint_count']>0): ?><span class="badge badge-red"><?=$b['complaint_count']?> 条曝光</span><?php endif; ?>
        </div>
        <div class="broker-regs">
          <?php if($rs['count']): foreach (array_slice($rs['entities'],0,4) as $e): ?>
            <span class="pill <?=entity_status_label($e['status'])[1]?>">
              <span class="grade <?=grade_class($e['reg_grade'])?>" style="height:16px;min-width:26px;font-size:10px"><?=h($e['reg_name'])?></span>
              <?=entity_status_label($e['status'])[0]?>
            </span>
          <?php endforeach; if($rs['count']>4): ?><span class="muted" style="font-size:12px">+<?=$rs['count']-4?></span><?php endif;
          else: ?><span class="badge badge-gray">暂无监管信息</span><?php endif; ?>
        </div>
      </div>
      <div class="broker-score">
        <?php if((int)$b['user_rating_count']>0): ?>
          <span class="rating-num <?=$tier?>"><?=number_format($b['user_rating_avg']*2,1)?></span>
          <?=stars_html($b['user_rating_avg'])?>
          <div class="cnt"><?=$b['user_rating_count']?> 人评价</div>
        <?php else: ?>
          <div class="noscore">暂无评分<br><span style="font-size:11px">来做第一个</span></div>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; endif; ?>
</div>

<?php
if ($pages > 1):
  $qs = function($p) use ($sort,$q){ return '/brokers.php?sort='.$sort.($q?'&q='.urlencode($q):'').'&page='.$p; };
?>
<div class="pager">
  <a class="<?=$page<=1?'disabled':''?>" href="<?=$qs(max(1,$page-1))?>">‹</a>
  <?php for($i=max(1,$page-3);$i<=min($pages,$page+3);$i++): ?>
    <?php if($i==$page): ?><span class="cur"><?=$i?></span><?php else: ?><a href="<?=$qs($i)?>"><?=$i?></a><?php endif; ?>
  <?php endfor; ?>
  <a class="<?=$page>=$pages?'disabled':''?>" href="<?=$qs(min($pages,$page+1))?>">›</a>
</div>
<?php endif; ?>

<?php footer_html(); ?>
