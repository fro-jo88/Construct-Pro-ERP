<?php
// seed_demo_users.php
require_once 'config/config.php';
require_once 'includes/Database.php';

echo "<h2>WECHECHA CONSTRUCTION PLC - Demo Data Seeding</h2>";

try {
    $db = Database::getInstance();
    $default_password = password_hash('password123', PASSWORD_BCRYPT);

    $demo_users = [
        // Executive
        ['name' => 'Wendimageng Siyum', 'user' => 'gm.wendi', 'email' => 'gm.wendi@wechecha-con.com', 'role' => 'GM', 'dept' => 'Executive', 'desig' => 'General Manager'],
        
        // HR
        ['name' => 'Nebiyu Engidashet', 'user' => 'hr.nebiyu', 'email' => 'hr.nebiyu@wechecha-con.com', 'role' => 'HR', 'dept' => 'HR', 'desig' => 'HR Manager'],
        
        // Finance
        ['name' => 'Rosa Belete', 'user' => 'fin.rosa', 'email' => 'fin.rosa@wechecha-con.com', 'role' => 'Finance', 'dept' => 'Finance', 'desig' => 'Head of Finance'],
        ['name' => 'Yonas Alemu', 'user' => 'fin.yonas', 'email' => 'fin.yonas@wechecha-con.com', 'role' => 'Finance', 'dept' => 'Finance', 'desig' => 'Finance Officer'],
        ['name' => 'Mekdes Tadesse', 'user' => 'acc.mekdes', 'email' => 'acc.mekdes@wechecha-con.com', 'role' => 'Finance', 'dept' => 'Finance', 'desig' => 'Accountant'],
        
        // Engineering & Planning
        ['name' => 'Birhanu Tesfaye', 'user' => 'plan.birhanu', 'email' => 'plan.birhanu@wechecha-con.com', 'role' => 'Planning', 'dept' => 'Planning', 'desig' => 'Planning Manager'],
        ['name' => 'Samuel Kebede', 'user' => 'plan.sam', 'email' => 'plan.sam@wechecha-con.com', 'role' => 'Planning', 'dept' => 'Planning', 'desig' => 'Planning Engineer'],
        ['name' => 'Fitsum Girma', 'user' => 'eng.fitsum', 'email' => 'eng.fitsum@wechecha-con.com', 'role' => 'Quantity Surveyor', 'dept' => 'Engineering', 'desig' => 'Site Engineer'],
        
        // Site & Ops
        ['name' => 'Amanuel Wolde', 'user' => 'site.amanuel', 'email' => 'site.amanuel@wechecha-con.com', 'role' => 'Foreman', 'dept' => 'Operations', 'desig' => 'Foreman'],
        ['name' => 'Abel Mengistu', 'user' => 'site.abel', 'email' => 'site.abel@wechecha-con.com', 'role' => 'Foreman', 'dept' => 'Operations', 'desig' => 'Assistant Foreman'],
        
        // Store
        ['name' => 'Tsegaye Mulugeta', 'user' => 'store.tsegaye', 'email' => 'store.tsegaye@wechecha-con.com', 'role' => 'Store Manager', 'dept' => 'Store', 'desig' => 'Store Manager'],
        ['name' => 'Haben Ayele', 'user' => 'store.haben', 'email' => 'store.haben@wechecha-con.com', 'role' => 'Store Keeper', 'dept' => 'Store', 'desig' => 'Store Keeper (Site)'],
        
        // Procurement
        ['name' => 'Kalkidan Fekadu', 'user' => 'proc.kalkidan', 'email' => 'proc.kalkidan@wechecha-con.com', 'role' => 'Procurement', 'dept' => 'Procurement', 'desig' => 'Procurement Head'],
        ['name' => 'Dawit Abebe', 'user' => 'proc.dawit', 'email' => 'proc.dawit@wechecha-con.com', 'role' => 'Procurement', 'dept' => 'Procurement', 'desig' => 'Purchase Officer'],
        
        // Logistics
        ['name' => 'Solomon Getachew', 'user' => 'trans.solomon', 'email' => 'trans.solomon@wechecha-con.com', 'role' => 'Logistics', 'dept' => 'Logistics', 'desig' => 'Transport Manager'],
        ['name' => 'Mulatu Desta', 'user' => 'trans.mulatu', 'email' => 'trans.mulatu@wechecha-con.com', 'role' => 'Logistics', 'dept' => 'Logistics', 'desig' => 'Driver Supervisor'],
        ['name' => 'Getu Assefa', 'user' => 'drv.getu', 'email' => 'drv.getu@wechecha-con.com', 'role' => 'Logistics', 'dept' => 'Logistics', 'desig' => 'Driver'],
        ['name' => 'Kassa Demissie', 'user' => 'drv.kassa', 'email' => 'drv.kassa@wechecha-con.com', 'role' => 'Logistics', 'dept' => 'Logistics', 'desig' => 'Driver'],
        
        // Audit
        ['name' => 'Tesfahun Alemayehu', 'user' => 'audit.tesfa', 'email' => 'audit.tesfa@wechecha-con.com', 'role' => 'Audit', 'dept' => 'Audit', 'desig' => 'Audit Manager'],
        ['name' => 'Rahel Worku', 'user' => 'audit.rahel', 'email' => 'audit.rahel@wechecha-con.com', 'role' => 'Audit', 'dept' => 'Audit', 'desig' => 'Audit Officer'],
        
        // SysAdmin
        ['name' => 'Henok Getahun', 'user' => 'sys.henok', 'email' => 'sys.henok@wechecha-con.com', 'role' => 'IT Admin', 'dept' => 'IT', 'desig' => 'System Admin']
    ];

    foreach ($demo_users as $data) {
        $db->beginTransaction();
        
        // Get Role ID
        $stmt = $db->prepare("SELECT id FROM roles WHERE role_name = ?");
        $stmt->execute([$data['role']]);
        $role_id = $stmt->fetchColumn();
        
        if (!$role_id) {
            echo "<p style='color: orange;'>[SKIP] Role not found: {$data['role']} for {$data['user']}</p>";
            $db->rollBack();
            continue;
        }

        // 1. Insert User
        $stmt = $db->prepare("INSERT INTO users (username, password, email, role_id, status) VALUES (?, ?, ?, ?, 'active') ON DUPLICATE KEY UPDATE status='active'");
        $stmt->execute([$data['user'], $default_password, $data['email'], $role_id]);
        $user_id = $db->lastInsertId();
        
        if (!$user_id) {
            // Might exist, get existing ID
            $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$data['user']]);
            $user_id = $stmt->fetchColumn();
        }

        // 2. Insert Employee
        $name_parts = explode(' ', $data['name']);
        $fn = $name_parts[0];
        $ln = $name_parts[1] ?? 'Ethiopia';
        
        $stmt = $db->prepare("INSERT INTO employees (user_id, employee_code, first_name, last_name, department, designation, status) VALUES (?, ?, ?, ?, ?, ?, 'active') ON DUPLICATE KEY UPDATE status='active'");
        $emp_code = 'WCH-' . strtoupper(substr($data['user'], 0, 3)) . '-' . str_pad($user_id, 3, '0', STR_PAD_LEFT);
        $stmt->execute([$user_id, $emp_code, $fn, $ln, $data['dept'], $data['desig']]);

        // 3. Log to Audit
        $stmt = $db->prepare("INSERT INTO audit_logs (user_id, action, table_affected, record_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([1, 'demo_user_created', 'users', $user_id]); // Assuming admin(1) created them

        $db->commit();
        echo "<p style='color: green;'>[SUCCESS] Created: {$data['name']} ({$data['user']}) as {$data['role']}</p>";
    }

    echo "<p><b>All demo users set to password:</b> password123</p>";
    echo "<p><a href='index.php'>Go to Login</a></p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>[ERROR] " . $e->getMessage() . "</p>";
}
?>
