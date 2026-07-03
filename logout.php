<?php
require_once __DIR__ . '/includes/auth.php';
user_logout();
header('Location: /');
exit;
