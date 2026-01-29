<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';

$db = Database::getInstance();
try {
    echo "🏗️ Implementing Bid Lifecycle Workflow Schema...\n";

    // 1. Handle Tenders -> Bids migration/renaming
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('tenders', $tables) && !in_array('bids', $tables)) {
        $db->exec("RENAME TABLE tenders TO bids");
        echo "✅ Renamed table 'tenders' to 'bids'.\n";
    } elseif (!in_array('bids', $tables)) {
        $db->exec("CREATE TABLE bids (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tender_no VARCHAR(50),
            title VARCHAR(255) NOT NULL,
            client_name VARCHAR(255),
            description TEXT,
            deadline DATETIME,
            status ENUM('DRAFT', 'TECHNICAL_COMPLETED', 'FINANCIAL_COMPLETED', 'GM_PRE_APPROVED', 'FINANCE_FINAL_REVIEW', 'WON', 'LOSS') DEFAULT 'DRAFT',
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            submission_mode ENUM('softcopy', 'hardcopy') DEFAULT 'softcopy',
            bid_file VARCHAR(255) NULL
        ) ENGINE=InnoDB;");
        echo "✅ Created table 'bids'.\n";
    }

    // 2. Adjust Bids status enum if it exists but is old
    $db->exec("ALTER TABLE bids MODIFY COLUMN status ENUM('DRAFT', 'TECHNICAL_COMPLETED', 'FINANCIAL_COMPLETED', 'GM_PRE_APPROVED', 'FINANCE_FINAL_REVIEW', 'WON', 'LOSS') DEFAULT 'DRAFT'");
    echo "✅ Synchronized Bids status flow.\n";

    // 3. Create bid_decisions table
    $db->exec("CREATE TABLE IF NOT EXISTS bid_decisions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        bid_id INT NOT NULL,
        gm_id INT NOT NULL,
        decision ENUM('PRE_APPROVED', 'WON', 'LOSS', 'REJECTED', 'QUERIED') NOT NULL,
        reason TEXT,
        decided_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (bid_id) REFERENCES bids(id),
        FOREIGN KEY (gm_id) REFERENCES users(id)
    ) ENGINE=InnoDB;");
    echo "✅ Created 'bid_decisions' audit table.\n";

    // 4. Ensure Supporting Bidding Tables
    $db->exec("CREATE TABLE IF NOT EXISTS technical_bids (
        id INT AUTO_INCREMENT PRIMARY KEY,
        bid_id INT NOT NULL,
        status ENUM('draft', 'ready', 'completed') DEFAULT 'ready',
        details JSON NULL,
        completed_at TIMESTAMP NULL,
        FOREIGN KEY (bid_id) REFERENCES bids(id)
    ) ENGINE=InnoDB;");
    
    $db->exec("CREATE TABLE IF NOT EXISTS financial_bids (
        id INT AUTO_INCREMENT PRIMARY KEY,
        bid_id INT NOT NULL,
        status ENUM('draft', 'ready', 'completed') DEFAULT 'ready',
        total_cost DECIMAL(15,2),
        margin_percent DECIMAL(5,2),
        final_total DECIMAL(15,2),
        completed_at TIMESTAMP NULL,
        FOREIGN KEY (bid_id) REFERENCES bids(id)
    ) ENGINE=InnoDB;");
    echo "✅ Created/Verified specialized bidding support tables.\n";

    echo "\n🚀 Schema Sync Complete.";

} catch (Exception $e) {
    echo "❌ FATAL ERROR: " . $e->getMessage();
}
?>
