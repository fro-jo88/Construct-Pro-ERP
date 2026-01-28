<?php
// fix_gm_schema.php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';

try {
    $db = Database::getInstance();
    echo "<h2>Ultimate Enterprise Schema Sync</h2>";
    echo "Synchronizing definitions...<br><br>";

    // Helper function for safe Alter
    function safeAddColumn($db, $table, $column, $definition) {
        try {
            // Check if column exists
            $result = $db->query("SHOW COLUMNS FROM `$table` LIKE '$column'")->fetch();
            if (!$result) {
                $db->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
                echo "<span style='color:green;'>[SUCCESS]</span> Added $table.$column <br>";
            } else {
                echo "<span style='color:blue;'>[INFO]</span> $table.$column already exists.<br>";
            }
        } catch (Exception $e) {
            echo "<span style='color:red;'>[ERROR]</span> Failed to add $table.$column: " . $e->getMessage() . "<br>";
        }
    }

    // 1. BIDS - Transitioning names
    try {
        $check = $db->query("SHOW COLUMNS FROM `bids` LIKE 'tender_file'")->fetch();
        if ($check) {
            $db->exec("ALTER TABLE `bids` CHANGE `tender_file` `bid_file` VARCHAR(255) NULL");
            echo "<span style='color:green;'>[SUCCESS]</span> Migrated tenders.tender_file -> bids.bid_file <br>";
        } else {
            safeAddColumn($db, 'bids', 'bid_file', "VARCHAR(255) NULL");
        }
    } catch (Exception $e) {}
    
    safeAddColumn($db, 'bids', 'submission_mode', "ENUM('softcopy', 'hardcopy') DEFAULT 'softcopy'");

    // Update Enums to the new 7-stage flow
    try {
        $db->exec("ALTER TABLE bids MODIFY COLUMN status ENUM('DRAFT', 'TECHNICAL_COMPLETED', 'FINANCIAL_COMPLETED', 'GM_PRE_APPROVED', 'FINANCE_FINAL_REVIEW', 'WON', 'LOSS') DEFAULT 'DRAFT'");
        echo "<span style='color:green;'>[SUCCESS]</span> Synchronized Bids Status Workflow Enum <br>";
    } catch (Exception $e) {
        echo "<span style='color:red;'>[ERROR]</span> Enum sync failed: " . $e->getMessage() . "<br>";
    }

    // 2. BUDGETS
    safeAddColumn($db, 'budgets', 'budget_name', "VARCHAR(100) AFTER project_id");
    safeAddColumn($db, 'budgets', 'status', "ENUM('pending', 'active', 'rejected') DEFAULT 'pending' AFTER remaining_amount");

    // 3. PROJECTS
    safeAddColumn($db, 'projects', 'progress_percent', "DECIMAL(5,2) DEFAULT 0.00");
    safeAddColumn($db, 'projects', 'risk_score', "INT DEFAULT 0");

    // 4. TECHNICAL BIDS
    try {
        $check = $db->query("SHOW COLUMNS FROM `technical_bids` LIKE 'tender_id'")->fetch();
        if ($check) {
            $db->exec("ALTER TABLE `technical_bids` CHANGE `tender_id` `bid_id` INT NOT NULL");
            echo "<span style='color:green;'>[SUCCESS]</span> Migrated technical_bids.tender_id -> bid_id <br>";
        }
    } catch (Exception $e) {}

    // 5. FINANCIAL BIDS
    try {
        $check = $db->query("SHOW COLUMNS FROM `financial_bids` LIKE 'bid_id'")->fetch();
        if (!$check) {
            // If strictly needed, create table, but alter is safer
        }
        safeAddColumn($db, 'financial_bids', 'labor_cost', "DECIMAL(15,2) DEFAULT 0.00");
        safeAddColumn($db, 'financial_bids', 'material_cost', "DECIMAL(15,2) DEFAULT 0.00");
        safeAddColumn($db, 'financial_bids', 'equipment_cost', "DECIMAL(15,2) DEFAULT 0.00");
        safeAddColumn($db, 'financial_bids', 'overhead_cost', "DECIMAL(15,2) DEFAULT 0.00");
        safeAddColumn($db, 'financial_bids', 'tax', "DECIMAL(15,2) DEFAULT 0.00");
        safeAddColumn($db, 'financial_bids', 'document_path', "VARCHAR(255) NULL");
    } catch (Exception $e) {}

    // 6. EMPLOYEES - Enterprise Profile Module
    safeAddColumn($db, 'employees', 'full_name', "VARCHAR(100) AFTER user_id");
    safeAddColumn($db, 'employees', 'email', "VARCHAR(100) AFTER full_name");
    safeAddColumn($db, 'employees', 'phone', "VARCHAR(20) AFTER email");
    safeAddColumn($db, 'employees', 'emergency_name', "VARCHAR(100) AFTER phone");
    safeAddColumn($db, 'employees', 'emergency_phone', "VARCHAR(20) AFTER emergency_name");
    safeAddColumn($db, 'employees', 'emergency_relationship', "VARCHAR(50) AFTER emergency_phone");
    safeAddColumn($db, 'employees', 'education_level', "VARCHAR(100) AFTER emergency_relationship");
    safeAddColumn($db, 'employees', 'education_field', "VARCHAR(100) AFTER education_level");
    safeAddColumn($db, 'employees', 'education_pdf', "VARCHAR(255) AFTER education_field");
    safeAddColumn($db, 'employees', 'work_experience_years', "INT DEFAULT 0 AFTER education_pdf");
    safeAddColumn($db, 'employees', 'employment_type', "ENUM('Permanent', 'Contract') DEFAULT 'Permanent' AFTER work_experience_years");
    safeAddColumn($db, 'employees', 'approved_by', "INT NULL");
    safeAddColumn($db, 'employees', 'approved_at', "TIMESTAMP NULL");

    // Sync status and position naming
    try {
        $check = $db->query("SHOW COLUMNS FROM `employees` LIKE 'designation'")->fetch();
        if ($check) {
            $db->exec("ALTER TABLE `employees` CHANGE `designation` `position` VARCHAR(100)");
            echo "<span style='color:green;'>[SUCCESS]</span> Migrated employees.designation -> position <br>";
        } else {
            safeAddColumn($db, 'employees', 'position', "VARCHAR(100) AFTER department");
        }
    } catch (Exception $e) {}

    // Sync Status Enum
    try {
        $db->exec("ALTER TABLE employees MODIFY COLUMN status ENUM('pending', 'active', 'rejected') DEFAULT 'pending'");
    } catch (Exception $e) {}

    // 7. MATERIAL REQUESTS (Forwarding)
    safeAddColumn($db, 'material_requests', 'hr_review_status', "ENUM('pending', 'validated', 'rejected') DEFAULT 'pending'");
    safeAddColumn($db, 'material_requests', 'hr_reviewed_by', "INT NULL");
    safeAddColumn($db, 'material_requests', 'store_forwarded_at', "TIMESTAMP NULL");

    // 8. SITE INCIDENTS (Executive Tracking)
    safeAddColumn($db, 'site_incidents', 'gm_acknowledged', "BOOLEAN DEFAULT FALSE");
    safeAddColumn($db, 'site_incidents', 'gm_acknowledgment_at', "TIMESTAMP NULL");

    // 9. PAYROLL (SINGULAR - As used in HRManager/GMManager)
    $db->exec("CREATE TABLE IF NOT EXISTS payroll (
        id INT AUTO_INCREMENT PRIMARY KEY,
        employee_id INT NOT NULL,
        month_year VARCHAR(10) NOT NULL,
        base_salary DECIMAL(15, 2),
        net_pay DECIMAL(15, 2),
        status ENUM('draft', 'pending_gm', 'approved', 'paid') DEFAULT 'draft',
        generated_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (employee_id) REFERENCES employees(id),
        FOREIGN KEY (generated_by) REFERENCES users(id)
    ) ENGINE=InnoDB;");
    echo "[SUCCESS] Checked Payroll (singular) table.<br>";

    // 8. APPROVAL HISTORY (Audit Ledger)
    $db->exec("CREATE TABLE IF NOT EXISTS approval_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        module ENUM('HR', 'FINANCE', 'BIDS', 'PROCUREMENT', 'PLANNING', 'INVENTORY') NOT NULL,
        reference_id INT NOT NULL,
        approver_id INT NOT NULL,
        decision ENUM('approved', 'rejected', 'queried', 'pre_approved', 'won', 'loss') NOT NULL,
        reason TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (approver_id) REFERENCES users(id)
    ) ENGINE=InnoDB;");
    echo "[SUCCESS] Checked Approval History table.<br>";

    // 9. ACTIVITY LOGS
    $db->exec("CREATE TABLE IF NOT EXISTS hr_activity_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        action_type VARCHAR(100),
        details TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    ) ENGINE=InnoDB;");
    echo "[SUCCESS] Checked Activity Logs table.<br>";

    echo "<br><strong>Sync Complete. System Ready.</strong>";

} catch (Exception $e) {
    echo "<br><strong style='color:red;'>FATAL ERROR:</strong> " . $e->getMessage();
}
?>
