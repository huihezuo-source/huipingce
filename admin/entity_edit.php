<?php
require_once __DIR__ . '/layout.php';
admin_head('entities','实体编辑');
$id=(int)($_GET['id'] ?? 0); $e=null;
if($id){ $st=db()->prepare('SELECT * FROM reg_entities WHERE id=?'); $st->execute([$id]); $e=$st->fetch(); }
$v=function($k,$d='') use($e){ return $e?h($e[$k]??$d):$d; };
$regs=db()->query('SELECT id,name,flag FROM regulators ORDER BY sort_order')->fetchAll();
?>
<?php admin_topbar($id?'编辑实体':'新建实体','<a class="btn btn-ghost" href="/admin/reg_entities.php">← 返回</a>'); ?>
<form method="post" action="/admin/api/entity_save.php" class="card" style="max-width:760px">
  <input type="hidden" name="id" value="<?= $id ?>">
  <div class="form-grid">
    <div class="form-row"><label>实体公司名 *</label><input class="inp" name="name" value="<?= $v('name') ?>" required></div>
    <div class="form-row"><label>监管机构 *</label><select class="inp" name="regulator_id" required>
      <option value="">选择…</option>
      <?php foreach($regs as $rg): ?><option value="<?= h($rg['id']) ?>" <?= $e&&$e['regulator_id']===$rg['id']?'selected':'' ?>><?= h($rg['flag']) ?> <?= h($rg['name']) ?></option><?php endforeach; ?>
    </select></div>
  </div>
  <div class="form-grid">
    <div class="form-row"><label>牌照/注册号</label><input class="inp" name="license_no" value="<?= $v('license_no') ?>"></div>
    <div class="form-row"><label>状态</label><select class="inp" name="status">
      <?php foreach(['active'=>'有效','suspended'=>'暂停','revoked'=>'已吊销','expired'=>'已过期'] as $k=>$lbl): ?>
      <option value="<?= $k ?>" <?= $e&&$e['status']===$k?'selected':'' ?>><?= $lbl ?></option><?php endforeach; ?>
    </select></div>
  </div>
  <div class="form-row"><label>牌照类型/权限范围</label><input class="inp" name="license_type" value="<?= $v('license_type') ?>"></div>
  <div class="form-grid-3">
    <div class="form-row"><label>客户类型</label><input class="inp" name="client_type" value="<?= $v('client_type') ?>" placeholder="零售/专业/机构"></div>
    <div class="form-row"><label>国家</label><input class="inp" name="country" value="<?= $v('country') ?>"></div>
    <div class="form-row"><label>城市</label><input class="inp" name="city" value="<?= $v('city') ?>"></div>
  </div>
  <div class="form-grid-3">
    <div class="form-row"><label>注册日期</label><input class="inp" type="date" name="reg_date" value="<?= $e&&$e['reg_date']?h($e['reg_date']):'' ?>"></div>
    <div class="form-row"><label>官网</label><input class="inp" name="website" value="<?= $v('website') ?>"></div>
    <div class="form-row"><label>来源</label><input class="inp" name="source" value="<?= $e?$v('source'):'manual' ?>"></div>
  </div>
  <button class="btn btn-primary" type="submit">💾 保存</button>
</form>
<?php admin_foot();