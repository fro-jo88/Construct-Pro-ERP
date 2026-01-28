<?php
// config/role_menus.php

return [
    'GM' => [
        ['label' => 'Command Center', 'icon' => 'crown', 'url' => 'main.php?module=dashboards/roles/GM'],
        ['label' => 'Approvals', 'icon' => 'stamp', 'url' => 'main.php?module=gm/approvals'],
        ['label' => 'Financial Overview', 'icon' => 'coins', 'url' => 'main.php?module=gm/finance_oversight'],
        ['label' => 'Site Progress', 'icon' => 'project-diagram', 'url' => 'main.php?module=gm/sites'], 
        ['label' => 'Audit Reports', 'icon' => 'shield-alt', 'url' => 'main.php?module=gm/audit'],
    ],
    'HR_MANAGER' => [
        ['label' => 'HR Hub', 'icon' => 'users', 'url' => 'main.php?module=dashboards/roles/HR_MANAGER'],
        ['label' => 'Employees', 'icon' => 'user-tie', 'url' => 'main.php?module=hr/employees'],
        ['label' => 'Attendance', 'icon' => 'clock', 'url' => 'main.php?module=hr/attendance'],
        ['label' => 'Payroll', 'icon' => 'file-invoice-dollar', 'url' => 'main.php?module=hr/payroll'],
    ],
    'FINANCE_HEAD' => [
        ['label' => 'Finance Executive', 'icon' => 'chart-line', 'url' => 'main.php?module=dashboards/roles/FINANCE_HEAD'],
        ['label' => 'Budgets', 'icon' => 'wallet', 'url' => 'main.php?module=finance/budgets'],
        ['label' => 'Expenses', 'icon' => 'receipt', 'url' => 'main.php?module=finance/expenses'],
    ],
    'FINANCE_TEAM' => [
        ['label' => 'Finance Desk', 'icon' => 'calculator', 'url' => 'main.php?module=dashboards/roles/FINANCE_TEAM'],
        ['label' => 'Budget Tracking', 'icon' => 'search-dollar', 'url' => 'main.php?module=finance/tracking'],
    ],
    'AUDIT_TEAM' => [
        ['label' => 'Audit Dashboard', 'icon' => 'search', 'url' => 'main.php?module=dashboards/roles/AUDIT_TEAM'],
        ['label' => 'Transaction Logs', 'icon' => 'list', 'url' => 'main.php?module=finance/logs'],
    ],
    'TECH_BID_MANAGER' => [
        ['label' => 'Technical Bidding', 'icon' => 'drafting-compass', 'url' => 'main.php?module=dashboards/roles/TECH_BID_MANAGER'],
        ['label' => 'Specifications', 'icon' => 'ruler-combined', 'url' => 'main.php?module=bidding/specs'],
    ],
    'FINANCE_BID_MANAGER' => [
        ['label' => 'Financial Bidding', 'icon' => 'file-invoice', 'url' => 'main.php?module=dashboards/roles/FINANCE_BID_MANAGER'],
        ['label' => 'Active Bids', 'icon' => 'gavel', 'url' => 'main.php?module=bidding/active'],
    ],
    'PLANNING_MANAGER' => [
        ['label' => 'Planning Hub', 'icon' => 'network-wired', 'url' => 'main.php?module=dashboards/roles/PLANNING_MANAGER'],
        ['label' => 'Master Schedules', 'icon' => 'calendar-alt', 'url' => 'main.php?module=planning/schedules'],
    ],
    'PLANNING_ENGINEER' => [
        ['label' => 'Engineering Desk', 'icon' => 'clipboard-list', 'url' => 'main.php?module=dashboards/roles/PLANNING_ENGINEER'],
        ['label' => 'Weekly Plans', 'icon' => 'calendar-week', 'url' => 'main.php?module=planning/weekly'],
    ],
    'FORMAN' => [
        ['label' => 'Site Dashboard', 'icon' => 'hard-hat', 'url' => 'main.php?module=dashboards/roles/FORMAN'],
        ['label' => 'Daily Report', 'icon' => 'edit', 'url' => 'main.php?module=foreman/daily_report'],
        ['label' => 'Material Request', 'icon' => 'truck', 'url' => 'main.php?module=foreman/request'],
    ],
    'STORE_MANAGER' => [
        ['label' => 'Store Oversight', 'icon' => 'warehouse', 'url' => 'main.php?module=dashboards/roles/STORE_MANAGER'],
        ['label' => 'All Inventory', 'icon' => 'boxes', 'url' => 'main.php?module=store/inventory'],
    ],
    'STORE_KEEPER' => [
        ['label' => 'My Store', 'icon' => 'archive', 'url' => 'main.php?module=dashboards/roles/STORE_KEEPER'],
        ['label' => 'Issue Materials', 'icon' => 'hand-holding', 'url' => 'main.php?module=store/issue'],
    ],
    'DRIVER_MANAGER' => [
        ['label' => 'Fleet Control', 'icon' => 'shipping-fast', 'url' => 'main.php?module=dashboards/roles/DRIVER_MANAGER'],
        ['label' => 'Vehicles', 'icon' => 'car', 'url' => 'main.php?module=transport/vehicles'],
    ],
    'DRIVER' => [
        ['label' => 'My Trips', 'icon' => 'route', 'url' => 'main.php?module=dashboards/roles/DRIVER'],
        ['label' => 'Status Update', 'icon' => 'gas-pump', 'url' => 'main.php?module=transport/vehicle_status'],
    ],
    'TENDER_FINANCE' => [
        ['label' => 'Tender Finance', 'icon' => 'file-invoice', 'url' => 'main.php?module=dashboards/roles/TENDER_FINANCE'],
        ['label' => 'Docs', 'icon' => 'file-invoice', 'url' => 'main.php?module=tender/finance'],
    ],
    'TENDER_TECHNICAL' => [
        ['label' => 'Tender Technical', 'icon' => 'file-alt', 'url' => 'main.php?module=dashboards/roles/TENDER_TECHNICAL'],
        ['label' => 'Docs', 'icon' => 'file-alt', 'url' => 'main.php?module=tender/technical'],
    ],
    'PURCHASE_MANAGER' => [
        ['label' => 'Procurement Hub', 'icon' => 'shopping-cart', 'url' => 'main.php?module=dashboards/roles/PURCHASE_MANAGER'],
        ['label' => 'Approvals', 'icon' => 'check-double', 'url' => 'main.php?module=procurement/approvals'],
    ],
    'PURCHASE_OFFICER' => [
        ['label' => 'Purchasing', 'icon' => 'file-import', 'url' => 'main.php?module=dashboards/roles/PURCHASE_OFFICER'],
        ['label' => 'My PRs', 'icon' => 'box-open', 'url' => 'main.php?module=procurement/requests'],
    ],
    'CONSTRUCTION_AUDIT' => [
        ['label' => 'Audit Center', 'icon' => 'search-location', 'url' => 'main.php?module=dashboards/roles/CONSTRUCTION_AUDIT'],
        ['label' => 'Site Progress', 'icon' => 'clipboard-list', 'url' => 'main.php?module=audit/site_progress'],
    ],
    'SYSTEM_ADMIN' => [
        ['label' => 'Admin Console', 'icon' => 'cogs', 'url' => 'main.php?module=dashboards/roles/SYSTEM_ADMIN'],
        ['label' => 'Users & Roles', 'icon' => 'users-cog', 'url' => 'main.php?module=admin/users'],
    ],
    'SUPER_ADMIN' => [
        ['label' => 'God Mode Dashboard', 'icon' => 'skull', 'url' => 'main.php?module=dashboards/roles/SUPER_ADMIN'],
        ['label' => 'Full System Control', 'icon' => 'database', 'url' => 'main.php?module=admin/db'],
    ],
    'default' => [
        ['label' => 'Home', 'icon' => 'home', 'url' => 'main.php'],
    ]
];
