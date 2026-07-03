<?php
require_once __DIR__ . '/../../includes/db.php';
require_login();
$in=json_in(); $id=trim($in['id']??'');
db()->prepare('DELETE FROM articles WHERE id=?')->execute([$id]);
write_log('article_delete','',$id);
json_out(['ok'=>true]);
