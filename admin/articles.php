<?php
require_once __DIR__ . '/layout.php';
admin_head('articles','资讯管理');

$page=max(1,(int)($_GET['page']??1)); $per=25; $off=($page-1)*$per;
$total=(int)db()->query('SELECT COUNT(*) FROM articles')->fetchColumn();
$pages=max(1,(int)ceil($total/$per));
$rows=db()->query("SELECT a.*, c.name cat_name, b.name bn FROM articles a
  LEFT JOIN article_cats c ON a.cat_id=c.id LEFT JOIN brokers b ON a.broker_id=b.id
  ORDER BY a.created_at DESC LIMIT $per OFFSET $off")->fetchAll();
?>
<?php admin_topbar('资讯管理','<a class="btn btn-primary" href="/admin/article_edit.php">+ 新建资讯</a>'); ?>
<div class="card">
  <?php if(!$rows): ?><p class="muted">还没有资讯，<a href="/admin/article_edit.php" style="color:var(--brand);font-weight:700">新建一篇</a></p><?php else: ?>
  <table>
    <thead><tr><th>标题</th><th>分类</th><th>关联交易商</th><th>阅读</th><th>状态</th><th>操作</th></tr></thead>
    <tbody>
    <?php foreach($rows as $r):
      $blbl=['published'=>'已发布','draft'=>'草稿','scheduled'=>'定时']; ?>
      <tr>
        <td style="font-weight:700"><?= h(mb_strimwidth($r['title'],0,40,'…','UTF-8')) ?></td>
        <td><?= h($r['cat_name'] ?: '—') ?></td>
        <td class="muted"><?= h($r['bn'] ?: '—') ?></td>
        <td class="muted"><?= (int)$r['views'] ?></td>
        <td><?= $blbl[$r['status']] ?? $r['status'] ?></td>
        <td style="white-space:nowrap">
          <a class="btn btn-ghost btn-sm" href="/admin/article_edit.php?id=<?=h($r['id'])?>">编辑</a>
          <button class="btn btn-ghost btn-sm btn-danger" onclick="if(confirm('删除？'))artDel('<?=h($r['id'])?>')">删除</button>
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
    <a class="btn btn-ghost btn-sm <?=$i==$page?'btn-primary':''?>" href="/admin/articles.php?page=<?=$i?>"><?=$i?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>
<script>
function artDel(id){ fetch('/admin/api/article_delete.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id:id})}).then(r=>r.json()).then(d=>{ if(d.ok)location.reload(); else alert(d.error||'失败'); }); }
</script>
<?php admin_foot();
