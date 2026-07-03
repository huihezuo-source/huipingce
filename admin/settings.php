<?php
require_once __DIR__ . '/layout.php';
admin_head('settings','站点设置');
$cfg = settings_all();
$f=function($k,$d='') use($cfg){ return h($cfg[$k] ?? $d); };
?>
<?php admin_topbar('站点设置'); ?>
<?php if(isset($_GET['saved'])): ?><div class="flash flash-ok">✅ 设置已保存</div><?php endif; ?>
<form method="post" action="/admin/api/settings_save.php" class="card" style="max-width:720px">
  <div class="form-grid">
    <div class="form-row"><label>站点名</label><input class="inp" name="siteName" value="<?= $f('siteName','汇评测') ?>"></div>
    <div class="form-row"><label>英文名</label><input class="inp" name="siteNameEn" value="<?= $f('siteNameEn','Huipingce') ?>"></div>
  </div>
  <div class="form-row"><label>口号 Slogan</label><input class="inp" name="siteSlogan" value="<?= $f('siteSlogan') ?>"></div>
  <div class="form-row"><label>站点描述 SEO desc</label><textarea class="inp" name="siteDesc" rows="3"><?= $f('siteDesc') ?></textarea></div>
  <div class="form-row"><label>关键词 SEO keywords</label><input class="inp" name="siteKeywords" value="<?= $f('siteKeywords') ?>"></div>
  <div class="form-grid">
    <div class="form-row"><label>站点 URL</label><input class="inp" name="siteUrl" value="<?= $f('siteUrl','https://www.huipingce.com') ?>"></div>
    <div class="form-row"><label>客服时间</label><input class="inp" name="serviceTime" value="<?= $f('serviceTime') ?>"></div>
  </div>
  <div class="form-row"><label>ICP 备案号</label><input class="inp" name="icp" value="<?= $f('icp') ?>"></div>
  <div class="form-row"><label>用户测评审核</label>
    <label style="display:flex;align-items:center;gap:8px;font-weight:600">
      <input type="checkbox" name="reviewModeration" value="1" <?= ($cfg['reviewModeration'] ?? '0')==='1'?'checked':'' ?>>
      开启后：带文字的测评需后台审核通过才展示（纯打分仍即时生效）
    </label>
  </div>
  <button class="btn btn-primary" type="submit">💾 保存设置</button>
</form>
<?php admin_foot();