<?php
require_once __DIR__ . '/../../includes/db.php';
require_login();
$q = trim($_GET['q'] ?? '');
if (mb_strlen($q) < 2) json_out([]);
$st = db()->prepare("SELECT e.id,e.name,e.license_no,r.name AS reg_name
  FROM reg_entities e LEFT JOIN regulators r ON e.regulator_id=r.id
  WHERE e.name LIKE ? OR e.license_no LIKE ? ORDER BY e.name LIMIT 15");
$st->execute(["%$q%","%$q%"]);
json_out($st->fetchAll());
