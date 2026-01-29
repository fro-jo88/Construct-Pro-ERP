<?php
require_once 'config/config.php';
require_once 'includes/Database.php';
$db = Database::getInstance();
try {
    $db->exec("ALTER TABLE financial_bids ADD COLUMN boq_json LONGTEXT AFTER bid_id");
    echo "Successfully added boq_json column to financial_bids table.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
