<?php
require_once 'config/config.php';
require_once 'includes/Database.php';

try {
    $db = Database::getInstance();
    
    $role_map = [
        'GM' => 'General Manager',
        'HR_MANAGER' => 'HR Manager',
        'FINANCE_HEAD' => 'Head of Finance',
        'FINANCE_TEAM' => 'Finance Team Member',
        'AUDIT_TEAM' => 'Finance Audit Team',
        'TECH_BID_MANAGER' => 'Technical Bid Manager',
        'FINANCE_BID_MANAGER' => 'Finance Bid Manager',
        'PLANNING_MANAGER' => 'Planning Manager',
        'PLANNING_ENGINEER' => 'Planning Engineer',
        'FORMAN' => 'Forman',
        'STORE_MANAGER' => 'Store Manager',
        'STORE_KEEPER' => 'Store Keeper',
        'DRIVER_MANAGER' => 'Driver Manager',
        'DRIVER' => 'Driver',
        'TENDER_FINANCE' => 'Tender Finance Officer',
        'TENDER_TECHNICAL' => 'Tender Technical Officer',
        'PURCHASE_MANAGER' => 'Purchase Manager',
        'PURCHASE_OFFICER' => 'Purchase Officer',
        'CONSTRUCTION_AUDIT' => 'Construction Audit Team',
        'SYSTEM_ADMIN' => 'System Admin',
        'SUPER_ADMIN' => 'Super Admin'
    ];

    $db->beginTransaction();
    foreach ($role_map as $code => $name) {
        // Update employees position based on the role tied to their user account
        $sql = "UPDATE employees e 
                JOIN users u ON e.user_id = u.id 
                JOIN roles r ON u.role_id = r.id 
                SET e.position = ? 
                WHERE r.role_name = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$name, $code]);
    }
    $db->commit();
    echo "Updated employee positions to friendly names.";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
