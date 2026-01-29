<?php
require_once 'config/config.php';
require_once 'includes/Database.php';

$db = Database::getInstance();
try {
    $db->exec("CREATE TABLE IF NOT EXISTS bid_files (
        id INT AUTO_INCREMENT PRIMARY KEY,
        bid_id INT NOT NULL,
        file_name VARCHAR(255) NOT NULL,
        file_path VARCHAR(255) NOT NULL,
        file_type VARCHAR(50) NOT NULL,
        uploaded_by INT NOT NULL,
        uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (bid_id) REFERENCES bids(id) ON DELETE CASCADE
    )");
    echo "Table bid_files created successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
