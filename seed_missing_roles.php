<?php
require_once 'config/config.php';
require_once 'includes/Database.php';

echo "<h2>Seeding Missing Roles</h2>";

try {
    $db = Database::getInstance();
    $default_password = password_hash('password123', PASSWORD_BCRYPT);

    $missing_users = [
        ['role' => 'Project Manager', 'name' => 'Yared Haile', 'user' => 'pm.yared', 'dept' => 'Engineering'],
        ['role' => 'Site Manager', 'name' => 'Dawit Kebede', 'user' => 'site.dawit', 'dept' => 'Operations'],
        ['role' => 'Client', 'name' => 'Elias Ahmed', 'user' => 'client.elias', 'dept' => 'External'],
        ['role' => 'Safety Officer', 'name' => 'Abdi Mohammed', 'user' => 'safety.abdi', 'dept' => 'Safety'],
        ['role' => 'Executive Board', 'name' => 'Lakech Tekle', 'user' => 'board.lakech', 'dept' => 'Executive'],
        ['role' => 'Legal Counsel', 'name' => 'Belete Tadesse', 'user' => 'legal.belete', 'dept' => 'Legal'],
        ['role' => 'Quality Control', 'name' => 'Tigist Assefa', 'user' => 'qc.tigist', 'dept' => 'Quality'],
        ['role' => 'Maintenance', 'name' => 'Tariku Belay', 'user' => 'maint.tariku', 'dept' => 'Maintenance'],
        ['role' => 'Operations', 'name' => 'Seifu Alemu', 'user' => 'ops.seifu', 'dept' => 'Operations']
    ];

    foreach ($missing_users as $data) {
        $db->beginTransaction();

        // Check if role exists
        $stmt = $db->prepare("SELECT id FROM roles WHERE role_name = ?");
        $stmt->execute([$data['role']]);
        $role_id = $stmt->fetchColumn();

        if (!$role_id) {
            echo "<p style='color:red'>Role not found: {$data['role']}</p>";
            $db->rollBack();
            continue;
        }

        // Check if user exists
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$data['user']]);
        $existing_user_id = $stmt->fetchColumn();

        if ($existing_user_id) {
            echo "<p style='color:orange'>User {$data['user']} already exists. Skipping.</p>";
            $db->rollBack();
            continue;
        }

        // 1. Create User
        $email = $data['user'] . "@wechecha-con.com";
        $stmt = $db->prepare("INSERT INTO users (username, password, email, role_id, status) VALUES (?, ?, ?, ?, 'active')");
        $stmt->execute([$data['user'], $default_password, $email, $role_id]);
        $user_id = $db->lastInsertId();

        // 2. Create Employee
        $name_parts = explode(' ', $data['name']);
        $fn = $name_parts[0];
        $ln = $name_parts[1];
        
        $emp_code = 'WCH-' . strtoupper(substr($data['user'], 0, 3)) . '-' . rand(100, 999);
        
        $stmt = $db->prepare("INSERT INTO employees (user_id, employee_code, first_name, last_name, full_name, department, position, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");
        $stmt->execute([$user_id, $emp_code, $fn, $ln, $data['name'], $data['dept'], $data['role']]);

        $db->commit();
        echo "<p style='color:green'>Created: {$data['name']} ({$data['role']})</p>";
    }

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    echo "Error: " . $e->getMessage();
}
?>
