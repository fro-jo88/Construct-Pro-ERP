<?php
// setup.php
require_once 'config/config.php';
require_once 'includes/Database.php';

echo "<h2>CONSTRUCT PRO - Database Setup</h2>";

try {
    // Connect without DB first to create it
    $pdo = new PDO("mysql:host=" . DB_HOST . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    echo "<p style='color: green;'>[SUCCESS] Database " . DB_NAME . " created or already exists.</p>";

    $db = Database::getInstance();
    
    // Read schema
    $schema = file_get_contents('sql/schema.sql');
    $db->exec($schema);
    echo "<p style='color: green;'>[SUCCESS] Schema imported.</p>";
    
    // Read seed
    $seed = file_get_contents('sql/seed.sql');
    $db->exec($seed);
    echo "<p style='color: green;'>[SUCCESS] Seed data imported.</p>";
    
    echo "<p><b>Default Login:</b> admin / password (if changed in seed)</p>";
    echo "<p><a href='index.php'>Go to Login</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>[ERROR] " . $e->getMessage() . "</p>";
}
?>
