<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/footer.php';

$region = trim($_GET['region'] ?? '');
$regions = ['欧洲','亚太','美洲','非洲','离岸'];

$where='1=1'; $p=[];
if ($region!=='' && in_array($region,$regions,true)) { $where.=' AND region=?'; $p[]=$region; }
$st = db()->prepare("SELECT * FROM regulators WHERE $where ORDER BY trust_score DESC, sort_order ASC");
$st->execute($p);
$regs = $st->fetchAll();

header_html('regulators', [
    'title'=>'全球外汇监管机构数据库',
    'desc'=>'FCA、ASIC、SFC、NFA 等全球外汇监管机构信任评级、牌照查询入口与受监管实体名录。'
]);
?>
<div class="crumb"><a href="/">首页</a> › <b>监管机构</b></div>

<div class="section-hd"><h2>全球监管机构</h2><span class="muted" style="font-size:13px">共 <?=count($regs)?> 家</span></div>

<div class="card filters" style="margin-bottom:18px">
  <div class="fgroup">
    <span class="flabel">地区</span>
    <a class="chip<?=$region===''?' on':''?>" href="/regulators.php">全部</a>
    <?php foreach($regions as $rg): ?>
      <a class="chip<?=$region===$rg?' on':''?>" href="/regulators.php?region=<?=urlencode($rg)?>"><?=$rg?></a>
    <?php endforeach; ?>
  </div>
</div>

<?php if(!$regs): ?>
  <div class="card empty"><p>该地区暂无监管机构</p></div>
<?php else: ?>
<div class="reg-grid">
  <?php foreach($regs as $r): ?>
    <a class="card reg-card reveal" href="/regulator-detail.php?id=<?=h($r['id'])?>">
      <div class="reg-card-hd">
        <span class="reg-flag"><?=h($r['flag'])?></span>
        <div style="flex:1">
          <div class="rname"><?=h($r['name'])?> <span class="grade <?=grade_class($r['grade'])?>"><?=h($r['grade'])?></span></div>
          <div class="rfull"><?=h($r['full_name'])?></div>
        </div>
      </div>
      <div class="muted" style="font-size:13px;margin-top:10px"><?=h($r['country'])?> · <?=h($r['gov_type']?:'监管机构')?><?=$r['established']?' · '.h($r['established']).'年设立':''?></div>
      <div class="reg-card-meta">
        <span class="reg-trust">信任评分 <b><?=(int)$r['trust_score']?></b></span>
        <span class="badge badge-gray"><?=grade_label($r['grade'])?></span>
      </div>
    </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php footer_html(); ?>
