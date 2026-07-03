<?php
require_once __DIR__ . '/layout.php';
admin_head('users','会员管理');

$q=trim($_GET['q']??'');
$page=max(1,(int)($_GET['page']??1)); $per=30; $off=($page-1)*$per;
$where='1=1'; $p=[];
if($q!==''){ $where.=' AND (username LIKE ? OR nickname LIKE ?)'; $p[]="%$q%"; $p[]="%$q%"; }
$cnt=db()->prepare("SELECT COUNT(*) FROM site_users WHERE $where"); $cnt->execute($p);
$total=(int)$cnt->fetchColumn(); $pages=max(1,(int)ceil($total/$per));
$st=db()->prepare("SELECT u.*, (SELECT COUNT(*) FROM broker_reviews r WHERE r.user_id=u.id) rc,
  (SELECT COUNT(*) FROM complaints c WHERE c.user_id=u.id) cc
  FROM site_users u WHERE $where ORDER BY u.created_at DESC LIMIT $per OFFSET $off");
$st->execute($p); $rows=$st->fetchAll();
?>
<?php admin_topbar('会员管理', '<span class="muted">共 '.$total.' 名会员</span>'); ?>
<div class="card" style="padding:12px 16px;margin-bottom:14px">
  <form method="get" style="display:flex;gap:8px">
    <input class="inp" name="q" value="<?=h($q)?>" placeholder="搜索用户名/昵称" style="max-width:260px">
    <button class="btn btn-primary btn-sm" type="submit">搜索</button>
  </form>
</div>
<div class="card">
  <?php if(!$rows): ?><p class="muted">暂无会员</p><?php else: ?>
  <table>
    <thead><tr><th>用户名</th><th>昵称</th><th>测评</th><th>曝光</th><th>注册时间</th><th>状态</th><th>操作</th></tr></thead>
    <tbody>
    <?php foreach($rows as $u): ?>
      <tr>
        <td style="font-weight:700"><?=h($u['username'])?></td>
        <td><?=h($u['nickname']?:'—')?></td>
        <td class="muted"><?=(int)$u['rc']?></td>
        <td class="muted"><?=(int)$u['cc']?></td>
        <td class="muted"><?=date('Y-m-d',$u['created_at'])?></td>
        <td><?= $u['status']==='active'?'✅ 正常':'🚫 封禁' ?></td>
        <td>
          <?php if($u['status']==='active'): ?>
            <button class="btn btn-ghost btn-sm btn-danger" onclick="if(confirm('封禁该会员？'))ban('<?=h($u['id'])?>','ban')">封禁</button>
          <?php else: ?>
            <button class="btn btn-ghost btn-sm" onclick="ban('<?=h($u['id'])?>','unban')">解封</button>
          <?php endif; ?>
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
    <a class="btn btn-ghost btn-sm <?=$i==$page?'btn-primary':''?>" href="/admin/users.php?<?=$q?'q='.urlencode($q).'&':''?>page=<?=$i?>"><?=$i?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>
<script>
function ban(id,act){ fetch('/admin/api/user_ban.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id:id,action:act})}).then(r=>r.json()).then(d=>{ if(d.ok)location.reload(); else alert(d.error||'失败'); }); }
</script>
<?php admin_foot();
