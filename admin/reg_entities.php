<?php
require_once __DIR__ . '/layout.php';
admin_head('entities','受监管实体');

$q = trim($_GET['q'] ?? '');
$reg = trim($_GET['reg'] ?? '');
$page = max(1,(int)($_GET['page'] ?? 1));
$cond='1=1'; $args=[];
if($q!==''){ $cond.=' AND (e.name LIKE ? OR e.license_no LIKE ?)'; $args[]="%$q%"; $args[]="%$q%"; }
if($reg!==''){ $cond.=' AND e.regulator_id=?'; $args[]=$reg; }
$total=(int)(function()use($cond,$args){$s=db()->prepare("SELECT COUNT(*) FROM reg_entities e WHERE $cond");$s->execute($args);return $s->fetchColumn();})();
$pg=admin_paginate($page,$total,25);
$st=db()->prepare("SELECT e.*,r.name AS reg_name,r.flag AS reg_flag FROM reg_entities e LEFT JOIN regulators r ON e.regulator_id=r.id WHERE $cond ORDER BY e.id DESC LIMIT {$pg['per']} OFFSET {$pg['offset']}");
$st->execute($args); $rows=$st->fetchAll();
$regs=db()->query('SELECT id,name,flag FROM regulators ORDER BY sort_order')->fetchAll();
?>
<?php admin_topbar('受监管实体 ('.$total.')','<a class="btn btn-primary" href="/admin/entity_edit.php">+ 新建实体</a>'); ?>
<?php if(isset($_GET['saved'])): ?><div class="flash flash-ok">✅ 已保存</div><?php endif; ?>
<form class="toolbar" method="get">
  <input class="search" name="q" value="<?= h($q) ?>" placeholder="搜索实体名 / 牌照号…">
  <select class="inp" name="reg" style="width:auto"><option value="">全部监管</option>
    <?php foreach($regs as $rg): ?><option value="<?= h($rg['id']) ?>" <?= $reg===$rg['id']?'selected':'' ?>><?= h($rg['flag']) ?> <?= h($rg['name']) ?></option><?php endforeach; ?>
  </select>
  <button class="btn btn-ghost btn-sm">筛选</button>
</form>
<?php if($rows): ?>
<table>
  <thead><tr><th>实体名称</th><th>监管</th><th>牌照号</th><th>状态</th><th></th></tr></thead>
  <tbody>
  <?php $sl=['active'=>['有效','b-pub'],'suspended'=>['暂停','b-sch'],'revoked'=>['吊销','g-c'],'expired'=>['过期','g-c']]; foreach($rows as $e): ?>
    <tr>
      <td style="font-weight:700"><?= h($e['name']) ?><?php if($e['license_type']): ?><div class="muted"><?= h(mb_substr($e['license_type'],0,40)) ?></div><?php endif; ?></td>
      <td><?= h($e['reg_flag']) ?> <?= h($e['reg_name']) ?></td>
      <td class="muted"><?= h($e['license_no']?:'—') ?></td>
      <td><span class="badge <?= $sl[$e['status']][1]??'b-draft' ?>"><?= $sl[$e['status']][0]??$e['status'] ?></span></td>
      <td style="white-space:nowrap">
        <a class="btn btn-ghost btn-sm" href="/admin/entity_edit.php?id=<?= (int)$e['id'] ?>">编辑</a>
        <button class="btn btn-ghost btn-sm btn-danger" onclick="admDelete('/admin/api/entity_delete.php','<?= (int)$e['id'] ?>','<?= h(addslashes($e['name'])) ?>')">删除</button>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?= admin_pager_html($pg,'/admin/reg_entities.php?'.($q?'q='.urlencode($q).'&':'').($reg?'reg='.urlencode($reg).'&':'')) ?>
<?php else: ?>
  <div class="card" style="text-align:center;padding:48px"><p class="muted">暂无实体。可由采集脚本批量入库，或手动新建。</p></div>
<?php endif; ?>
<?php admin_foot();