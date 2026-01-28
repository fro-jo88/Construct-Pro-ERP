<?php
// scripts/init_all.php

require_once __DIR__ . '/../config/config.php';

function log_msg($msg) {
    echo "[" . date('Y-m-d H:i:s') . "] " . $msg . "\n";
}

try {
    // 1. Connect and create DB
    log_msg("Connecting to MySQL server...");
    $dsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $dbName = DB_NAME;
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    log_msg("Database '$dbName' ready.");

    $pdo->exec("USE `$dbName`");

    // 2. Import SQL files in a reasonable order
    $sqlDir = __DIR__ . '/../sql';
    $sqlFiles = [
        'schema.sql',
        'bidding_schema.sql',
        'foreman_schema.sql',
        'hr_schema.sql',
        'hr_schema_update.sql',
        'gm_core_extensions.sql',
        'hr_final_extensions.sql',
        'fix_budgets.sql',
        'fix_incidents.sql',
        'seed.sql'
    ];

    foreach ($sqlFiles as $file) {
        $filePath = $sqlDir . '/' . $file;
        if (file_exists($filePath)) {
            log_msg("Importing SQL: $file...");
            $sql = file_get_contents($filePath);
            try {
                // PDO::exec might struggle with files containing multiple statements if there are delimiters like DELIMITER //
                // But for standard SQL it should be fine.
                $pdo->exec($sql);
                log_msg("Success: $file");
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'already exists') !== false || strpos($e->getMessage(), 'Duplicate column name') !== false) {
                    log_msg("Info: $file (already applied or partially applied)");
                } else {
                    log_msg("Warning: $file - " . $e->getMessage());
                }
            }
        }
    }

    // 3. Run PHP Setup Scripts
    // We will use 'include' so they use the same DB connection if we modify them, 
    // but they mostly instantiate their own Database::getInstance().
    // So we just run them via CLI.

    $setupScripts = [
        'bid_workflow_init.php',
        'fix_gm_schema.php',
        'install_foreman.php',
        'seed_missing_roles.php',
        'seed_demo_users.php',
        'fix_employee_positions.php', // Seen in file list
        'check_missing_roles.php'     // Seen in file list
    ];

    $phpExe = 'c:\xampp\php\php.exe';
    foreach ($setupScripts as $script) {
        $scriptPath = __DIR__ . '/../' . $script;
        if (file_exists($scriptPath)) {
            log_msg("Running script: $script...");
            $output = shell_exec("$phpExe $scriptPath 2>&1");
            echo $output . "\n";
            log_msg("Finished script: $script");
        }
    }

    log_msg("COMPLETED: All database tables created and data filled.");

} catch (Exception $e) {
    log_msg("CRITICAL ERROR: " . $e->getMessage());
    exit(1);
}
