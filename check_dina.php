<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';
$db = Database::getInstance();
$u = $db->query("SELECT u.username, r.role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.username = 'dina'")->fetch(PDO::FETCH_ASSOC);
print_r($u);
?>
