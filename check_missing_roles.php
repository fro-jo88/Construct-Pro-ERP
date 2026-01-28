<?php
require_once 'config/config.php';
require_once 'includes/Database.php';

try {
    $db = Database::getInstance();
    
    // Get all roles and count users per role
    $stmt = $db->query("
        SELECT r.id, r.role_name, COUNT(u.id) as user_count 
        FROM roles r 
        LEFT JOIN users u ON r.id = u.role_id 
        GROUP BY r.id, r.role_name
        ORDER BY r.id
    ");
    $roles_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h2>Role Coverage Check</h2>";
    echo "<table border='1'><tr><th>ID</th><th>Role Name</th><th>User Count</th></tr>";
    
    $missing_roles = [];
    foreach ($roles_data as $row) {
        echo "<tr><td>{$row['id']}</td><td>{$row['role_name']}</td><td>{$row['user_count']}</td></tr>";
        if ($row['user_count'] == 0) {
            $missing_roles[] = $row;
        }
    }
    echo "</table>";
    
    if (!empty($missing_roles)) {
        echo "<h3>Missing Roles (need employees):</h3>";
        foreach ($missing_roles as $mr) {
            echo " - " . $mr['role_name'] . "\n";
        }
    } else {
        echo "<h3>All roles have at least one user!</h3>";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
