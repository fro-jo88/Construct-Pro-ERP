<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';
$db = Database::getInstance();
$cols = $db->query("DESCRIBE financial_bids")->fetchAll(PDO::FETCH_COLUMN);
echo implode(',', $cols);
?>
