<?php
require_once __DIR__ . '/layout.php';
admin_head('reviews','用户测评审核');

$status = $_GET['status'] ?? 'all';
$page = max(1,(int)($_GET['page']??1)); $per=30; $off=($page-1)*$per;

$where='1=1'; $p=[];
if (in_array($status,['approved','pending','rejected'],true)) { $where.=' AND r.status=?'; $p[]=$status; }

$cnt=db()->prepare("SELECT COUNT(*) FROM broker_reviews r WHERE $where"); $cnt->execute($p);
$total=(int)$cnt->fetchColumn(); $pages=max(1,(int)ceil($total/$per));

$st=db()->prepare("SELECT r.*, b.name bn, u.nickname, u.username FROM broker_reviews r
  LEFT JOIN brokers b ON r.broker_id=b.id LEFT JOIN site_users u ON r.user_id=u.id
  WHERE $where ORDER BY (r.status='pending') DESC, r.created_at DESC LIMIT $per OFFSET $off");
$st->execute($p); $rows=$st->fetchAll();

$tabs=['all'=>'全部','pending'=>'待审','approved'=>'已通过','rejected'=>'已拒绝'];
?>
<?php admin_topbar('用户测评审核'); ?>
<div class="card" style="padding:12px 16px;margin-bottom:14px">
  <?php foreach($tabs as $k=>$v): $on=$status===$k?'btn-primary':'btn-ghost'; ?>
    <a class="btn <?=$on?> btn-sm" href="/admin/reviews.php?status=<?=$k?>"><?=$v?></a>
  <?php endforeach; ?>
</div>
<div class="card">
  <?php if(!$rows): ?><p class="muted">暂无测评</p><?php else: ?>
  <table>
    <thead><tr><th>会员</th><th>交易商</th><th>评分</th><th>内容</th><th>有用</th><th>状态</th><th>操作</th></tr></thead>
    <tbody>
    <?php foreach($rows as $r): ?>
      <tr id="rv_<?=h($r['id'])?>">
        <td><?= h($r['nickname'] ?: $r['username'] ?: '—') ?></td>
        <td class="muted"><?= h($r['bn'] ?: '（已删）') ?></td>
        <td style="font-weight:800;white-space:nowrap"><?= str_repeat('★',(int)$r['stars']) ?></td>
        <td style="max-width:340px"><?= h($r['content']?:'（仅打分）') ?></td>
        <td class="muted"><?= (int)$r['useful_count'] ?></td>
        <td><?= $r['status']==='approved'?'✅ 通过':($r['status']==='pending'?'⏳ 待审':'❌ 拒绝') ?></td>
        <td style="white-space:nowrap">
          <?php if($r['status']!=='approved'): ?><button class="btn btn-ghost btn-sm" onclick="rvMod('<?=h($r['id'])?>','approve')">通过</button><?php endif; ?>
          <?php if($r['status']!=='rejected'): ?><button class="btn btn-ghost btn-sm btn-danger" onclick="rvMod('<?=h($r['id'])?>','reject')">拒绝</button><?php endif; ?>
          <button class="btn btn-ghost btn-sm btn-danger" onclick="if(confirm('删除该测评？'))rvMod('<?=h($r['id'])?>','delete')">删除</button>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
<?php if($pages>1): ?>
<div class="pager" style="margin-top:14px">
  <?php for($i=max(1,$page-3);$i<=min($pages,$page+3);$i++): ?>
    <a class="btn btn-ghost btn-sm <?=$i==$page?'btn-primary':''?>" href="/admin/reviews.php?status=<?=$status?>&page=<?=$i?>"><?=$i?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>
<script>
function rvMod(id,act){
  fetch('/admin/api/review_mod.php',{method:'POST',headers:{'Content-Type':'application/json'},
    body:JSON.stringify({id:id,action:act})}).then(r=>r.json()).then(function(d){
    if(d.ok) location.reload(); else alert(d.error||'失败');
  });
}
</script>
<?php admin_foot();
