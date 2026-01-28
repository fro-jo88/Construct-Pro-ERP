<?php
// scripts/init_db.php

require_once __DIR__ . '/../config/config.php';

try {
    // Connect to MySQL server (without selecting DB first)
    $dsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    echo "Connected to MySQL server successfully.\n";

    // Create Database if not exists
    $dbName = DB_NAME;
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database '$dbName' checked/created.\n";

    // Connect to the specific database
    $pdo->exec("USE `$dbName`");

    // Check if tables exist
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($tables)) {
        echo "Database is empty. Importing schema...\n";

        $sqlDir = __DIR__ . '/../sql';
        
        // List of SQL files to import in order
        $sqlFiles = [
            'schema.sql',
            'bidding_schema.sql',
            'foreman_schema.sql',
            'hr_schema.sql',
            'hr_schema_update.sql', // It seems to be an update, but might contain needed tables if hr_schema is old
            'seed.sql'
        ];

        foreach ($sqlFiles as $file) {
            $filePath = $sqlDir . '/' . $file;
            if (file_exists($filePath)) {
                echo "Importing $file...\n";
                $sql = file_get_contents($filePath);
                try {
                    $pdo->exec($sql);
                    echo "Imported $file successfully.\n";
                } catch (PDOException $e) {
                    echo "Error importing $file: " . $e->getMessage() . "\n";
                }
            } else {
                echo "Warning: File $file not found.\n";
            }
        }
        echo "Schema import completed.\n";
    } else {
        echo "Database already contains tables. Skipping schema import.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
