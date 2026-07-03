<?php
require_once __DIR__ . '/../../includes/db.php';
require_login();
$keys=['siteName','siteNameEn','siteSlogan','siteDesc','siteKeywords','siteUrl','serviceTime','icp'];
$st=db()->prepare('INSERT INTO site_settings (k,v) VALUES (?,?) ON DUPLICATE KEY UPDATE v=VALUES(v)');
foreach($keys as $k){ if(isset($_POST[$k])) $st->execute([$k, trim($_POST[$k])]); }
// 测评审核开关（checkbox）
$st->execute(['reviewModeration', !empty($_POST['reviewModeration']) ? '1' : '0']);
write_log('settings_save','');
header('Location: /admin/settings.php?saved=1');
