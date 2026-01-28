<?php
// config/demo_users.php - UPDATED FOR NEW ROLE STRUCTURE

return [
    'Top Management' => [
        ['name' => 'Wendimageng Siyum', 'user' => 'gm.wendi', 'role' => 'GM', 'dept' => 'Executive', 'dashboard' => 'GM Dashboard'],
    ],
    'Human Resources' => [
        ['name' => 'Nebiyu Engidashet', 'user' => 'hr.nebiyu', 'role' => 'HR_MANAGER', 'dept' => 'HR', 'dashboard' => 'HR Dashboard'],
    ],
    'Finance & Audit' => [
        ['name' => 'Rosa Belete', 'user' => 'fin.rosa', 'role' => 'FINANCE_HEAD', 'dept' => 'Finance', 'dashboard' => 'Finance Dashboard'],
        ['name' => 'Yonas Alemu', 'user' => 'fin.yonas', 'role' => 'FINANCE_TEAM', 'dept' => 'Finance', 'dashboard' => 'Finance Team'],
        ['name' => 'Rahel Worku', 'user' => 'audit.rahel', 'role' => 'AUDIT_TEAM', 'dept' => 'Finance', 'dashboard' => 'Audit Dashboard'],
        ['name' => 'Helen Tesfaye', 'user' => 'finbid.helen', 'role' => 'FINANCE_BID_MANAGER', 'dept' => 'Bidding', 'dashboard' => 'Finance Bid Dashboard'],
        ['name' => 'Tesfahun Alemayehu', 'user' => 'const.tesfa', 'role' => 'CONSTRUCTION_AUDIT', 'dept' => 'Audit', 'dashboard' => 'Construction Audit'],
    ],
    'Technical & Planning' => [
        ['name' => 'Dawit Kebede', 'user' => 'tech.dawit', 'role' => 'TECH_BID_MANAGER', 'dept' => 'Bidding', 'dashboard' => 'Tech Bid Dashboard'],
        ['name' => 'Birhanu Tesfaye', 'user' => 'plan.birhanu', 'role' => 'PLANNING_MANAGER', 'dept' => 'Planning', 'dashboard' => 'Planning Dashboard'],
        ['name' => 'Samuel Kebede', 'user' => 'plan.sam', 'role' => 'PLANNING_ENGINEER', 'dept' => 'Planning', 'dashboard' => 'Planning Tasks'],
    ],
    'Site Operations' => [
        ['name' => 'Amanuel Wolde', 'user' => 'site.amanuel', 'role' => 'FORMAN', 'dept' => 'Operations', 'dashboard' => 'Site Dashboard'],
        ['name' => 'Abel Mengistu', 'user' => 'site.abel', 'role' => 'FORMAN', 'dept' => 'Operations', 'dashboard' => 'Site Dashboard'],
    ],
    'Store & Logistics' => [
        ['name' => 'Tsegaye Mulugeta', 'user' => 'store.tsegaye', 'role' => 'STORE_MANAGER', 'dept' => 'Store', 'dashboard' => 'Central Store'],
        ['name' => 'Haben Ayele', 'user' => 'store.haben', 'role' => 'STORE_KEEPER', 'dept' => 'Store', 'dashboard' => 'Store Keeper'],
        ['name' => 'Solomon Getachew', 'user' => 'trans.solomon', 'role' => 'DRIVER_MANAGER', 'dept' => 'Logistics', 'dashboard' => 'Fleet Manager'],
        ['name' => 'Getu Assefa', 'user' => 'drv.getu', 'role' => 'DRIVER', 'dept' => 'Logistics', 'dashboard' => 'Driver App'],
    ],
    'Procurement' => [
        ['name' => 'Kalkidan Fekadu', 'user' => 'proc.kalkidan', 'role' => 'PURCHASE_MANAGER', 'dept' => 'Procurement', 'dashboard' => 'Procurement Dashboard'],
        ['name' => 'Dawit Abebe', 'user' => 'proc.dawit', 'role' => 'PURCHASE_OFFICER', 'dept' => 'Procurement', 'dashboard' => 'Purchasing'],
        ['name' => 'Lema Gurmu', 'user' => 'tend.lema', 'role' => 'TENDER_FINANCE', 'dept' => 'Tender', 'dashboard' => 'Tender Docs'],
        ['name' => 'Sarah Ali', 'user' => 'tend.sarah', 'role' => 'TENDER_TECHNICAL', 'dept' => 'Tender', 'dashboard' => 'Tender Docs'],
    ],
    'System Admin' => [
        ['name' => 'Henok Getahun', 'user' => 'sys.henok', 'role' => 'SYSTEM_ADMIN', 'dept' => 'IT', 'dashboard' => 'Admin Panel'],
        ['name' => 'Super Admin', 'user' => 'admin.super', 'role' => 'SUPER_ADMIN', 'dept' => 'IT', 'dashboard' => 'GOD MODE'],
    ],
];
?>
