<?php
// config/role_menus.php

return [
    // --- TOP MANAGEMENT ---
    'GM' => [
        ['label' => 'Command Center', 'icon' => 'crown', 'url' => 'main.php?module=gm/dashboard'],
        ['label' => 'Approvals', 'icon' => 'stamp', 'url' => 'main.php?module=gm/approvals'],
        ['label' => 'Financial Overview', 'icon' => 'coins', 'url' => 'main.php?module=gm/finance_oversight'],
        ['label' => 'Site Progress', 'icon' => 'project-diagram', 'url' => 'main.php?module=gm/sites'], 
        ['label' => 'Audit Reports', 'icon' => 'shield-alt', 'url' => 'main.php?module=gm/audit'],
        ['label' => 'HR Directory', 'icon' => 'users', 'url' => 'main.php?module=hr/employees'],
    ],

    // --- HR ---
    'HR_MANAGER' => [
        ['label' => 'HR Dashboard', 'icon' => 'home', 'url' => 'main.php?module=hr/dashboard'],
        ['label' => 'Employees', 'icon' => 'users', 'url' => 'main.php?module=hr/employees'],
        ['label' => 'Attendance', 'icon' => 'clock', 'url' => 'main.php?module=hr/attendance'],
        ['label' => 'Leave Requests', 'icon' => 'calendar-minus', 'url' => 'main.php?module=hr/leaves'],
        ['label' => 'Payroll', 'icon' => 'file-invoice-dollar', 'url' => 'main.php?module=hr/payroll'],
        ['label' => 'Recruitment', 'icon' => 'briefcase', 'url' => 'main.php?module=hr/recruitment'],
        ['label' => 'Messages', 'icon' => 'envelope', 'url' => 'main.php?module=hr/messages'],
    ],

    // --- FINANCE ---
    'FINANCE_HEAD' => [
        ['label' => 'Finance Dashboard', 'icon' => 'chart-line', 'url' => 'main.php?module=finance/dashboard'],
        ['label' => 'Budgets', 'icon' => 'wallet', 'url' => 'main.php?module=finance/budgets'],
        ['label' => 'Expenditures', 'icon' => 'receipt', 'url' => 'main.php?module=finance/expenses'],
        ['label' => 'Bids & Tenders', 'icon' => 'file-contract', 'url' => 'main.php?module=finance/bids'],
        ['label' => 'Financial Reports', 'icon' => 'print', 'url' => 'main.php?module=finance/reports'],
    ],
    'FINANCE_TEAM' => [
        ['label' => 'Dashboard', 'icon' => 'chart-bar', 'url' => 'main.php?module=finance/dashboard'],
        ['label' => 'Budget Tracking', 'icon' => 'search-dollar', 'url' => 'main.php?module=finance/tracking'],
        ['label' => 'Expenses', 'icon' => 'receipt', 'url' => 'main.php?module=finance/expenses'],
    ],
    'AUDIT_TEAM' => [
        ['label' => 'Audit Dashboard', 'icon' => 'search', 'url' => 'main.php?module=finance/audit_dashboard'],
        ['label' => 'Transaction Logs', 'icon' => 'list', 'url' => 'main.php?module=finance/logs'],
    ],
    'FINANCE_BID_MANAGER' => [
        ['label' => 'Bid Dashboard', 'icon' => 'file-invoice', 'url' => 'main.php?module=bidding/finance_dashboard'],
        ['label' => 'Active Bids', 'icon' => 'gavel', 'url' => 'main.php?module=bidding/active'],
    ],

    // --- TECHNICAL & PLANNING ---
    'TECH_BID_MANAGER' => [
        ['label' => 'Technical Dashboard', 'icon' => 'drafting-compass', 'url' => 'main.php?module=bidding/technical_dashboard'],
        ['label' => 'Specifications', 'icon' => 'ruler-combined', 'url' => 'main.php?module=bidding/specs'],
        ['label' => 'Planning Handovers', 'icon' => 'handshake', 'url' => 'main.php?module=bidding/collaborate'],
    ],
    'PLANNING_MANAGER' => [
        ['label' => 'Planning Hub', 'icon' => 'network-wired', 'url' => 'main.php?module=planning/dashboard'],
        ['label' => 'Master Schedules', 'icon' => 'calendar-alt', 'url' => 'main.php?module=planning/schedules'],
        ['label' => 'Resource Plans', 'icon' => 'tools', 'url' => 'main.php?module=planning/resources'],
    ],
    'PLANNING_ENGINEER' => [
        ['label' => 'My Tasks', 'icon' => 'clipboard-list', 'url' => 'main.php?module=planning/tasks'],
        ['label' => 'Weekly Plans', 'icon' => 'calendar-week', 'url' => 'main.php?module=planning/weekly'],
        ['label' => 'Drawings', 'icon' => 'pencil-ruler', 'url' => 'main.php?module=planning/drawings'],
    ],

    // --- SITE OPERATIONS ---
    'FORMAN' => [
        ['label' => 'Site Dashboard', 'icon' => 'hard-hat', 'url' => 'main.php?module=foreman/dashboard'],
        ['label' => 'Daily Report', 'icon' => 'edit', 'url' => 'main.php?module=foreman/daily_report'],
        ['label' => 'Material Request', 'icon' => 'truck', 'url' => 'main.php?module=foreman/request'],
        ['label' => 'Site Plan', 'icon' => 'map', 'url' => 'main.php?module=foreman/plan'],
    ],

    // --- STORE ---
    'STORE_MANAGER' => [
        ['label' => 'Central Store', 'icon' => 'warehouse', 'url' => 'main.php?module=store/dashboard'],
        ['label' => 'Inventory', 'icon' => 'boxes', 'url' => 'main.php?module=store/inventory'],
        ['label' => 'Transfers', 'icon' => 'dolly-flatbed', 'url' => 'main.php?module=store/transfers'],
    ],
    'STORE_KEEPER' => [
        ['label' => 'My Store', 'icon' => 'archive', 'url' => 'main.php?module=store/site_store'],
        ['label' => 'Issue Items', 'icon' => 'hand-holding', 'url' => 'main.php?module=store/issue'],
        ['label' => 'Stock Take', 'icon' => 'clipboard-check', 'url' => 'main.php?module=store/check'],
    ],

    // --- TRANSPORT ---
    'DRIVER_MANAGER' => [
        ['label' => 'Fleet Manager', 'icon' => 'shipping-fast', 'url' => 'main.php?module=transport/dashboard'],
        ['label' => 'Driver Schedule', 'icon' => 'users-cog', 'url' => 'main.php?module=transport/drivers'],
        ['label' => 'Vehicles', 'icon' => 'car', 'url' => 'main.php?module=transport/vehicles'],
    ],
    'DRIVER' => [
        ['label' => 'My Trips', 'icon' => 'route', 'url' => 'main.php?module=transport/my_trips'],
        ['label' => 'Vehicle Status', 'icon' => 'gas-pump', 'url' => 'main.php?module=transport/vehicle_status'],
    ],

    // --- TENDER & PROCUREMENT ---
    'TENDER_FINANCE' => [
        ['label' => 'Tender Docs (Fin)', 'icon' => 'file-invoice', 'url' => 'main.php?module=tender/finance'],
    ],
    'TENDER_TECHNICAL' => [
        ['label' => 'Tender Docs (Tech)', 'icon' => 'file-alt', 'url' => 'main.php?module=tender/technical'],
    ],
    'PURCHASE_MANAGER' => [
        ['label' => 'Procurement', 'icon' => 'shopping-cart', 'url' => 'main.php?module=procurement/dashboard'],
        ['label' => 'Approvals', 'icon' => 'check-double', 'url' => 'main.php?module=procurement/approvals'],
        ['label' => 'Vendors', 'icon' => 'store', 'url' => 'main.php?module=procurement/vendors'],
    ],
    'PURCHASE_OFFICER' => [
        ['label' => 'Purchase Requests', 'icon' => 'file-import', 'url' => 'main.php?module=procurement/requests'],
        ['label' => 'Orders', 'icon' => 'box-open', 'url' => 'main.php?module=procurement/orders'],
    ],

    // --- AUDIT ---
    'CONSTRUCTION_AUDIT' => [
        ['label' => 'Site Audit', 'icon' => 'search-location', 'url' => 'main.php?module=audit/site_progress'],
        ['label' => 'Material Audit', 'icon' => 'clipboard-list', 'url' => 'main.php?module=audit/materials'],
        ['label' => 'Reports', 'icon' => 'file-signature', 'url' => 'main.php?module=audit/reports'],
    ],

    // --- ADMIN ---
    'SYSTEM_ADMIN' => [
        ['label' => 'System Control', 'icon' => 'cogs', 'url' => 'main.php?module=admin/dashboard'],
        ['label' => 'Users & Roles', 'icon' => 'users-cog', 'url' => 'main.php?module=admin/users'],
        ['label' => 'Settings', 'icon' => 'sliders-h', 'url' => 'main.php?module=admin/settings'],
    ],
    'SUPER_ADMIN' => [
        ['label' => 'GOD MODE', 'icon' => 'skull', 'url' => 'main.php?module=admin/dashboard'],
        ['label' => 'Database', 'icon' => 'database', 'url' => 'main.php?module=admin/db'],
    ],

    'default' => [
        ['label' => 'Home', 'icon' => 'home', 'url' => 'main.php'],
    ]
];
?>
