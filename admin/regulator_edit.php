<?php
require_once __DIR__ . '/layout.php';
admin_head('regulators','监管机构编辑');
$id=$_GET['id'] ?? ''; $r=null;
if($id){ $st=db()->prepare('SELECT * FROM regulators WHERE id=?'); $st->execute([$id]); $r=$st->fetch(); }
$v=function($k,$d='') use($r){ return $r?h($r[$k]??$d):$d; };
?>
<?php admin_topbar($id?'编辑监管机构':'新建监管机构','<a class="btn btn-ghost" href="/admin/regulators.php">← 返回</a>'); ?>
<form method="post" action="/admin/api/regulator_save.php" class="card" style="max-width:720px">
  <input type="hidden" name="id" value="<?= h($id) ?>">
  <div class="form-grid-3">
    <div class="form-row"><label>简称 *</label><input class="inp" name="name" value="<?= $v('name') ?>" required placeholder="FCA"></div>
    <div class="form-row"><label>国旗 emoji</label><input class="inp" name="flag" value="<?= $v('flag') ?>" placeholder="🇬🇧"></div>
    <div class="form-row"><label>评级</label>
      <select class="inp" name="grade"><?php foreach(['AAA','AA','A','B','C'] as $g): ?><option <?= $r&&$r['grade']===$g?'selected':'' ?>><?= $g ?></option><?php endforeach; ?></select></div>
  </div>
  <div class="form-row"><label>全称</label><input class="inp" name="full_name" value="<?= $v('full_name') ?>"></div>
  <div class="form-grid-3">
    <div class="form-row"><label>国家/地区</label><input class="inp" name="country" value="<?= $v('country') ?>"></div>
    <div class="form-row"><label>区域</label><input class="inp" name="region" value="<?= $v('region') ?>" placeholder="欧洲/亚太/美洲/离岸"></div>
    <div class="form-row"><label>信任度 0-100</label><input class="inp" type="number" name="trust_score" value="<?= $r?$v('trust_score'):'0' ?>"></div>
  </div>
  <div class="form-grid-3">
    <div class="form-row"><label>成立年份</label><input class="inp" type="number" name="established" value="<?= $v('established') ?>"></div>
    <div class="form-row"><label>监管类型</label><input class="inp" name="gov_type" value="<?= $v('gov_type') ?>" placeholder="政府监管"></div>
    <div class="form-row"><label>排序</label><input class="inp" type="number" name="sort_order" value="<?= $r?$v('sort_order'):'0' ?>"></div>
  </div>
  <div class="form-row"><label>官网</label><input class="inp" name="website" value="<?= $v('website') ?>"></div>
  <div class="form-row"><label>牌照查询入口 URL</label><input class="inp" name="query_url" value="<?= $v('query_url') ?>"></div>
  <div class="form-row"><label>简介</label><textarea class="inp" name="description" rows="4"><?= $v('description') ?></textarea></div>
  <button class="btn btn-primary" type="submit">💾 保存</button>
</form>
<?php admin_foot();