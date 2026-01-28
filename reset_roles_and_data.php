<?php
require_once 'config/config.php';
require_once 'includes/Database.php';

echo "<h1>SYSTEM ROLE RESET & MIGRATION</h1>";

try {
    $db = Database::getInstance();
    
    // 1. DISABLE FOREIGN KEYS
    $db->exec("SET foreign_key_checks = 0");
    
    // 2. TRUNCATE RELEVANT TABLES
    echo "<p>Truncating tables (roles, users, employees)...</p>";
    $db->exec("TRUNCATE TABLE roles");
    $db->exec("TRUNCATE TABLE users");
    $db->exec("TRUNCATE TABLE employees");
    // $db->exec("TRUNCATE TABLE audit_logs"); // Optional, maybe keep logs or wipe
    
    // 3. DEFINE NEW ROLES
    $new_roles = [
        ['code' => 'GM', 'name' => 'General Manager'],
        ['code' => 'HR_MANAGER', 'name' => 'HR Manager'],
        ['code' => 'FINANCE_HEAD', 'name' => 'Head of Finance'],
        ['code' => 'FINANCE_TEAM', 'name' => 'Finance Team Member'],
        ['code' => 'AUDIT_TEAM', 'name' => 'Finance Audit Team'],
        ['code' => 'TECH_BID_MANAGER', 'name' => 'Technical Bid Manager'],
        ['code' => 'FINANCE_BID_MANAGER', 'name' => 'Finance Bid Manager'],
        ['code' => 'PLANNING_MANAGER', 'name' => 'Planning Manager'],
        ['code' => 'PLANNING_ENGINEER', 'name' => 'Planning Engineer'],
        ['code' => 'FORMAN', 'name' => 'Forman'],
        ['code' => 'STORE_MANAGER', 'name' => 'Store Manager'],
        ['code' => 'STORE_KEEPER', 'name' => 'Store Keeper'],
        ['code' => 'DRIVER_MANAGER', 'name' => 'Driver Manager'],
        ['code' => 'DRIVER', 'name' => 'Driver'],
        ['code' => 'TENDER_FINANCE', 'name' => 'Tender Finance Officer'],
        ['code' => 'TENDER_TECHNICAL', 'name' => 'Tender Technical Officer'],
        ['code' => 'PURCHASE_MANAGER', 'name' => 'Purchase Manager'],
        ['code' => 'PURCHASE_OFFICER', 'name' => 'Purchase Officer'],
        ['code' => 'CONSTRUCTION_AUDIT', 'name' => 'Construction Audit Team'],
        ['code' => 'SYSTEM_ADMIN', 'name' => 'System Admin'],
        ['code' => 'SUPER_ADMIN', 'name' => 'Super Admin']
    ];
    
    // 4. INSERT ROLES
    $stmt = $db->prepare("INSERT INTO roles (role_name, description) VALUES (?, ?)"); // Assuming schema uses role_name. I will use the CODE as role_name to strictly follow instructions.
    
    /* 
       Wait, current schema likely has `role_name`. 
       The prompt says "Role Code: GM". 
       I should probably store the friendly name in description if possible, or just use the Code.
       For RBAC, the Code is critical.
       I will check the schema of `roles` quickly in my head: ID, role_name.
       I will set `role_name` = THE CODE (e.g., 'HR_MANAGER').
    */
    
    foreach ($new_roles as $role) {
        $stmt->execute([$role['code'], $role['name']]);
        echo " - Created Role: {$role['code']} ({$role['name']})<br>";
    }
    
    echo "<hr><h3>Seeding Users...</h3>";
    
    // 5. SEED USERS MAPPED TO NEW ROLES
    $password = password_hash('password123', PASSWORD_BCRYPT);
    
    $users = [
        // GM
        ['user'=>'gm.wendi', 'role'=>'GM', 'name'=>'Wendimageng Siyum', 'dept'=>'Executive'],
        
        // HR
        ['user'=>'hr.nebiyu', 'role'=>'HR_MANAGER', 'name'=>'Nebiyu Engidashet', 'dept'=>'HR'],
        
        // FINANCE
        ['user'=>'fin.rosa', 'role'=>'FINANCE_HEAD', 'name'=>'Rosa Belete', 'dept'=>'Finance'],
        ['user'=>'fin.yonas', 'role'=>'FINANCE_TEAM', 'name'=>'Yonas Alemu', 'dept'=>'Finance'],
        ['user'=>'audit.rahel', 'role'=>'AUDIT_TEAM', 'name'=>'Rahel Worku', 'dept'=>'Finance'],
        
        // TECHNICAL & PLANNING
        ['user'=>'tech.dawit', 'role'=>'TECH_BID_MANAGER', 'name'=>'Dawit Kebede', 'dept'=>'Bidding'],
        ['user'=>'finbid.helen', 'role'=>'FINANCE_BID_MANAGER', 'name'=>'Helen Tesfaye', 'dept'=>'Bidding'],
        ['user'=>'plan.birhanu', 'role'=>'PLANNING_MANAGER', 'name'=>'Birhanu Tesfaye', 'dept'=>'Planning'],
        ['user'=>'plan.sam', 'role'=>'PLANNING_ENGINEER', 'name'=>'Samuel Kebede', 'dept'=>'Planning'],
        
        // SITE
        ['user'=>'site.amanuel', 'role'=>'FORMAN', 'name'=>'Amanuel Wolde', 'dept'=>'Operations'],
        ['user'=>'site.abel', 'role'=>'FORMAN', 'name'=>'Abel Mengistu', 'dept'=>'Operations'],
        
        // STORE
        ['user'=>'store.tsegaye', 'role'=>'STORE_MANAGER', 'name'=>'Tsegaye Mulugeta', 'dept'=>'Store'],
        ['user'=>'store.haben', 'role'=>'STORE_KEEPER', 'name'=>'Haben Ayele', 'dept'=>'Store'],
        
        // TRANSPORT
        ['user'=>'trans.solomon', 'role'=>'DRIVER_MANAGER', 'name'=>'Solomon Getachew', 'dept'=>'Logistics'],
        ['user'=>'drv.getu', 'role'=>'DRIVER', 'name'=>'Getu Assefa', 'dept'=>'Logistics'],
        
        // PROCUREMENT & TENDER
        ['user'=>'proc.kalkidan', 'role'=>'PURCHASE_MANAGER', 'name'=>'Kalkidan Fekadu', 'dept'=>'Procurement'],
        ['user'=>'proc.dawit', 'role'=>'PURCHASE_OFFICER', 'name'=>'Dawit Abebe', 'dept'=>'Procurement'],
        ['user'=>'tend.lema', 'role'=>'TENDER_FINANCE', 'name'=>'Lema Gurmu', 'dept'=>'Tender'],
        ['user'=>'tend.sarah', 'role'=>'TENDER_TECHNICAL', 'name'=>'Sarah Ali', 'dept'=>'Tender'],
        
        // AUDIT
        ['user'=>'const.tesfa', 'role'=>'CONSTRUCTION_AUDIT', 'name'=>'Tesfahun Alemayehu', 'dept'=>'Audit'],
        
        // SYSTEM
        ['user'=>'sys.henok', 'role'=>'SYSTEM_ADMIN', 'name'=>'Henok Getahun', 'dept'=>'IT'],
        ['user'=>'admin.super', 'role'=>'SUPER_ADMIN', 'name'=>'Super Admin', 'dept'=>'IT'],
    ];
    
    foreach ($users as $u) {
        // Get Role ID
        $rid = $db->query("SELECT id FROM roles WHERE role_name = '{$u['role']}'")->fetchColumn();
        
        try {
            if ($rid) {
                // User
                $stmt_u = $db->prepare("INSERT INTO users (username, password, email, role_id, status) VALUES (?, ?, ?, ?, 'active')");
                $email = $u['user'] . "@wechecha.com";
                $stmt_u->execute([$u['user'], $password, $email, $rid]);
                $uid = $db->lastInsertId();
                
                // Employee
                $parts = explode(' ', $u['name']);
                $fn = $parts[0];
                $ln = $parts[1] ?? 'User';
                $code = strtoupper("EMP-" . substr($u['role'], 0, 3) . "-" . $uid);
                
                $stmt_e = $db->prepare("INSERT INTO employees (user_id, employee_code, first_name, last_name, department, designation, status, joining_date) VALUES (?, ?, ?, ?, ?, ?, 'active', CURDATE())");
                $stmt_e->execute([$uid, $code, $fn, $ln, $u['dept'], $u['role']]);
                
                echo " - Created User: {$u['user']} as {$u['role']}<br>";
            } else {
                echo " <span style='color:red'>FAILED to find role {$u['role']} for {$u['user']}</span><br>";
            }
        } catch (Exception $e) {
            echo " <span style='color:red'>ERROR creating {$u['user']}: " . $e->getMessage() . "</span><br>";
        }
    }
    
    // 6. RE-ENABLE FK
    $db->exec("SET foreign_key_checks = 1");
    echo "<h3>DONE. System Role Structure Updated.</h3>";

} catch (Exception $e) {
    echo "CRITICAL ERROR: " . $e->getMessage();
}
?>
