<?php
require_once __DIR__ . '/../../includes/db.php';
require_login();
$in=json_in(); $id=trim($in['id']??''); $act=$in['action']??'';
$new = $act==='ban' ? 'banned' : 'active';
db()->prepare('UPDATE site_users SET status=? WHERE id=?')->execute([$new,$id]);
write_log('user_'.$act,'',$id);
json_out(['ok'=>true]);
