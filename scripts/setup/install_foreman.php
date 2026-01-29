<?php
// install_foreman.php
require_once 'config/config.php';
require_once 'includes/Database.php';

try {
    $db = Database::getInstance();
    echo "Connected to database.\n";

    $sqlFile = 'sql/foreman_schema.sql';
    if (!file_exists($sqlFile)) {
        die("Error: SQL file not found at $sqlFile\n");
    }

    $sql = file_get_contents($sqlFile);
    
    // Split into individual statements to handle errors better if needed, 
    // or just run if simple. The file has comments and multiple CREATE statements.
    // PDO::exec handles multiple statements if supported by driver/config.
    
    // Let's try executing.
    $db->exec($sql);
    echo "Database schema from $sqlFile applied successfully.\n";

} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'already exists') !== false) {
        echo "Note: Some tables already exist. Schema update might be partial.\n";
        echo "Error detail: " . $e->getMessage() . "\n";
    } else {
        echo "CRITICAL ERROR applying schema: " . $e->getMessage() . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
