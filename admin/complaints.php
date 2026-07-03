<?php
require_once __DIR__ . '/layout.php';
admin_head('complaints','曝光管理');

$detailId=$_GET['id']??'';
if($detailId){
  $st=db()->prepare("SELECT c.*, b.name bn FROM complaints c LEFT JOIN brokers b ON c.broker_id=b.id WHERE c.id=? LIMIT 1");
  $st->execute([$detailId]); $c=$st->fetch();
  if(!$c){ echo '<p>不存在</p>'; admin_foot(); exit; }
  admin_topbar('处理曝光','<a class="btn btn-ghost" href="/admin/complaints.php">← 返回列表</a>');
  ?>
  <div class="form-grid" style="grid-template-columns:1.4fr 1fr;align-items:start">
    <div class="card">
      <h2 style="font-size:18px;margin-bottom:6px"><?=h($c['title'])?></h2>
      <p class="muted" style="margin-bottom:12px"><?=h($c['type'])?> · <?=h($c['nickname'])?> · <?=date('Y-m-d H:i',$c['created_at'])?> · <?=h($c['bn']?:$c['broker_name']?:'未指定交易商')?></p>
      <div style="white-space:pre-wrap;line-height:1.8"><?=h($c['content'])?></div>
      <?php if($c['loss_amount']>0): ?><p style="margin-top:10px"><b>涉及金额：</b>$<?=number_format($c['loss_amount'])?></p><?php endif; ?>
      <?php if($c['evidence']): ?><p style="margin-top:10px"><b>证据：</b><br><?=nl2br(h($c['evidence']))?></p><?php endif; ?>
      <?php if($c['contact']): ?><p style="margin-top:10px"><b>联系方式（仅后台可见）：</b><?=h($c['contact'])?></p><?php endif; ?>
    </div>
    <form class="card" method="post" action="/admin/api/complaint_update.php">
      <input type="hidden" name="id" value="<?=h($c['id'])?>">
      <div class="form-row"><label>状态</label><select class="inp" name="status">
        <?php foreach(['pending'=>'待核实','processing'=>'处理中','resolved'=>'已解决','rejected'=>'未受理/驳回'] as $k=>$lbl): ?>
          <option value="<?=$k?>" <?=$c['status']===$k?'selected':''?>><?=$lbl?></option>
        <?php endforeach; ?>
      </select></div>
      <div class="form-row"><label>平台/交易商回应（公开）</label><textarea class="inp" name="admin_reply" rows="5"><?=h($c['admin_reply'])?></textarea></div>
      <div class="form-row"><label>已解决金额（USD）</label><input class="inp" type="number" step="0.01" name="resolved_amount" value="<?=$c['resolved_amount']!==null?h($c['resolved_amount']):''?>"></div>
      <button class="btn btn-primary" type="submit">💾 保存处理</button>
    </form>
  </div>
  <?php
  admin_foot(); exit;
}

// 列表
$status=$_GET['status']??'all';
$page=max(1,(int)($_GET['page']??1)); $per=25; $off=($page-1)*$per;
$where='1=1'; $p=[];
if(in_array($status,['pending','processing','resolved','rejected'],true)){ $where.=' AND c.status=?'; $p[]=$status; }
$cnt=db()->prepare("SELECT COUNT(*) FROM complaints c WHERE $where"); $cnt->execute($p);
$total=(int)$cnt->fetchColumn(); $pages=max(1,(int)ceil($total/$per));
$st=db()->prepare("SELECT c.*, b.name bn FROM complaints c LEFT JOIN brokers b ON c.broker_id=b.id
  WHERE $where ORDER BY (c.status='pending') DESC, c.created_at DESC LIMIT $per OFFSET $off");
$st->execute($p); $rows=$st->fetchAll();
$tabs=['all'=>'全部','pending'=>'待核实','processing'=>'处理中','resolved'=>'已解决','rejected'=>'已驳回'];
?>
<?php admin_topbar('曝光管理'); ?>
<div class="card" style="padding:12px 16px;margin-bottom:14px">
  <?php foreach($tabs as $k=>$vv): $on=$status===$k?'btn-primary':'btn-ghost'; ?>
    <a class="btn <?=$on?> btn-sm" href="/admin/complaints.php?status=<?=$k?>"><?=$vv?></a>
  <?php endforeach; ?>
</div>
<div class="card">
  <?php if(!$rows): ?><p class="muted">暂无曝光</p><?php else: ?>
  <table>
    <thead><tr><th>标题</th><th>类型</th><th>涉及交易商</th><th>金额</th><th>状态</th><th>时间</th><th></th></tr></thead>
    <tbody>
    <?php foreach($rows as $r): [$sl,$sc]=complaint_status_label($r['status']); ?>
      <tr>
        <td style="font-weight:700"><?= h(mb_strimwidth($r['title'],0,34,'…','UTF-8')) ?></td>
        <td><?= h($r['type']) ?></td>
        <td class="muted"><?= h($r['bn']?:$r['broker_name']?:'—') ?></td>
        <td class="muted"><?= $r['loss_amount']>0?'$'.number_format($r['loss_amount']):'—' ?></td>
        <td><?= $sl ?></td>
        <td class="muted"><?= date('m-d H:i',$r['created_at']) ?></td>
        <td><a class="btn btn-ghost btn-sm" href="/admin/complaints.php?id=<?=h($r['id'])?>">处理</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
<?php if($pages>1): ?>
<div class="pager" style="margin-top:14px">
  <?php for($i=max(1,$page-3);$i<=min($pages,$page+3);$i++): ?>
    <a class="btn btn-ghost btn-sm <?=$i==$page?'btn-primary':''?>" href="/admin/complaints.php?status=<?=$status?>&page=<?=$i?>"><?=$i?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>
<?php admin_foot();
