<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';
$db = Database::getInstance();
try {
    $columns = $db->query("DESCRIBE bids")->fetchAll(PDO::FETCH_COLUMN);
    echo "Columns in bids: " . implode(", ", $columns) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
