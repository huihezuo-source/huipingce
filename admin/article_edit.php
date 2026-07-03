<?php
require_once __DIR__ . '/layout.php';
admin_head('articles','资讯编辑');

$id=$_GET['id']??''; $a=null;
if($id){ $st=db()->prepare('SELECT * FROM articles WHERE id=?'); $st->execute([$id]); $a=$st->fetch(); }
$v=function($k,$d='')use($a){ return $a?h($a[$k]??$d):$d; };
$cats=article_cats();
$brokers=db()->query('SELECT id,name FROM brokers ORDER BY name')->fetchAll();
?>
<?php admin_topbar($id?'编辑资讯':'新建资讯','<a class="btn btn-ghost" href="/admin/articles.php">← 返回</a>'); ?>
<form method="post" action="/admin/api/article_save.php" class="form-grid" style="grid-template-columns:1.5fr 1fr;align-items:start">
  <input type="hidden" name="id" value="<?=h($id)?>">
  <div class="card">
    <div class="form-row"><label>标题 *</label><input class="inp" name="title" value="<?=$v('title')?>" required></div>
    <div class="form-row"><label>摘要</label><textarea class="inp" name="summary" rows="2"><?=$v('summary')?></textarea></div>
    <div class="form-row"><label>正文（HTML）</label><textarea class="inp" name="content" rows="18" style="font-family:monospace"><?=$a?h($a['content']):''?></textarea></div>
  </div>
  <div class="card">
    <div class="form-row"><label>分类</label><select class="inp" name="cat_id">
      <option value="">— 未分类 —</option>
      <?php foreach($cats as $c): ?><option value="<?=$c['id']?>" <?=$a&&$a['cat_id']==$c['id']?'selected':''?>><?=h($c['name'])?></option><?php endforeach; ?>
    </select></div>
    <div class="form-row"><label>关联交易商</label><select class="inp" name="broker_id">
      <option value="">— 无 —</option>
      <?php foreach($brokers as $bk): ?><option value="<?=h($bk['id'])?>" <?=$a&&$a['broker_id']===$bk['id']?'selected':''?>><?=h($bk['name'])?></option><?php endforeach; ?>
    </select></div>
    <div class="form-row"><label>封面图 URL</label><input class="inp" name="cover" value="<?=$v('cover')?>"></div>
    <div class="form-row"><label>作者</label><input class="inp" name="author" value="<?=$a?$v('author'):'汇评测'?>"></div>
    <div class="form-row"><label>来源</label><input class="inp" name="source" value="<?=$v('source')?>"></div>
    <div class="form-row"><label>标签（逗号分隔）</label><input class="inp" name="tags" value="<?=$v('tags')?>"></div>
    <div class="form-row"><label>状态</label><select class="inp" name="status">
      <?php foreach(['published'=>'已发布','draft'=>'草稿','scheduled'=>'定时'] as $k=>$lbl): ?>
        <option value="<?=$k?>" <?=($a?$a['status']:'published')===$k?'selected':''?>><?=$lbl?></option>
      <?php endforeach; ?>
    </select></div>
    <div class="form-row"><label>发布时间（定时用，留空=立即）</label><input class="inp" type="datetime-local" name="publish_at" value="<?=$a&&$a['publish_at']?date('Y-m-d\TH:i',$a['publish_at']):''?>"></div>
    <button class="btn btn-primary" type="submit">💾 保存</button>
  </div>
</form>
<?php admin_foot();
