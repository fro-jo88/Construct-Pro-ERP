<?php
require_once 'config/config.php';
require_once 'includes/Database.php';
$db = Database::getInstance();
try {
    $cols = $db->query("DESCRIBE financial_bids")->fetchAll();
    echo "Columns in financial_bids:\n";
    foreach ($cols as $col) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
