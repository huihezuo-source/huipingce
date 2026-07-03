<?php
require_once __DIR__ . '/layout.php';
admin_head('regulators','监管机构');
$rows = db()->query("SELECT * FROM regulators ORDER BY FIELD(grade,'AAA','AA','A','B','C'), sort_order")->fetchAll();
$gc=['AAA'=>'g-aaa','AA'=>'g-aa','A'=>'g-a','B'=>'g-b','C'=>'g-c'];
?>
<?php admin_topbar('监管机构','<a class="btn btn-primary" href="/admin/regulator_edit.php">+ 新建监管机构</a>'); ?>
<?php if(isset($_GET['saved'])): ?><div class="flash flash-ok">✅ 已保存</div><?php endif; ?>
<table>
  <thead><tr><th>机构</th><th>地区</th><th>评级</th><th>信任度</th><th>实体数</th><th></th></tr></thead>
  <tbody>
  <?php foreach($rows as $r): ?>
    <tr>
      <td style="font-weight:700"><?= h($r['flag']) ?> <?= h($r['name']) ?><div class="muted"><?= h($r['full_name']) ?></div></td>
      <td class="muted"><?= h($r['country']) ?> · <?= h($r['region']) ?></td>
      <td><span class="badge <?= $gc[$r['grade']]??'g-a' ?>"><?= h($r['grade']) ?></span></td>
      <td class="muted"><?= (int)$r['trust_score'] ?></td>
      <td class="muted"><?= (int)$r['entity_count'] ?></td>
      <td style="white-space:nowrap">
        <a class="btn btn-ghost btn-sm" href="/admin/regulator_edit.php?id=<?= h($r['id']) ?>">编辑</a>
        <button class="btn btn-ghost btn-sm btn-danger" onclick="admDelete('/admin/api/regulator_delete.php','<?= h($r['id']) ?>','<?= h(addslashes($r['name'])) ?>')">删除</button>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php admin_foot();