<?php
// config/role_menus.php

return [
    'GM' => [
        ['label' => 'Executive Dashboard', 'icon' => 'tachometer-alt', 'url' => 'main.php?module=dashboards/roles/GM'],
        ['label' => 'Bid Approvals', 'icon' => 'gavel', 'url' => 'main.php?module=bidding/gm_review'],
        ['label' => 'Projects Overview', 'icon' => 'building', 'url' => 'main.php?module=gm/projects'],
        ['label' => 'Budget Control', 'icon' => 'money-bill-wave', 'url' => 'main.php?module=gm/finance'],
        ['label' => 'Site Reports', 'icon' => 'clipboard-list', 'url' => 'main.php?module=gm/site_reports'],
        ['label' => 'Employee Approvals', 'icon' => 'user-check', 'url' => 'main.php?module=gm/hr_approvals'],
        ['label' => 'Leave Approvals', 'icon' => 'calendar-check', 'url' => 'main.php?module=gm/leaves'],
        ['label' => 'Planning & Materials', 'icon' => 'map-marked-alt', 'url' => 'main.php?module=gm/planning'],
        ['label' => 'Audit Reports', 'icon' => 'shield-alt', 'url' => 'main.php?module=gm/audit'],
        ['label' => 'System Logs', 'icon' => 'history', 'url' => 'main.php?module=gm/logs'],
    ],
    'HR_MANAGER' => [
        ['label' => 'Dashboard', 'icon' => 'home', 'url' => 'main.php?module=dashboards/roles/HR_MANAGER'],
        ['label' => 'Bids & Projects', 'icon' => 'file-contract', 'url' => 'main.php?module=hr/tenders'],
        ['label' => 'Assignments', 'icon' => 'user-plus', 'url' => 'main.php?module=hr/assignments'],
        ['label' => 'Employees', 'icon' => 'user-tie', 'url' => 'main.php?module=hr/employees'],
        ['label' => 'Attendance', 'icon' => 'clock', 'url' => 'main.php?module=hr/attendance'],
        ['label' => 'Payroll', 'icon' => 'file-invoice-dollar', 'url' => 'main.php?module=hr/payroll'],
        ['label' => 'Leave Requests', 'icon' => 'calendar-minus', 'url' => 'main.php?module=hr/leaves'],
        ['label' => 'Messages', 'icon' => 'envelope', 'url' => 'main.php?module=hr/messages'],
        ['label' => 'Material Requests', 'icon' => 'truck-loading', 'url' => 'main.php?module=hr/materials'],
        ['label' => 'Reports', 'icon' => 'chart-bar', 'url' => 'main.php?module=hr/reports'],
    ],
    'FINANCE_HEAD' => [
        ['label' => 'Finance Overview', 'icon' => 'tachometer-alt', 'url' => 'main.php?module=dashboards/roles/FINANCE_HEAD'],
        ['label' => 'Financial Bid Review', 'icon' => 'gavel', 'url' => 'main.php?module=finance/bid_review'],
        ['label' => 'Budget Monitoring', 'icon' => 'chart-pie', 'url' => 'main.php?module=finance/budgets'],
        ['label' => 'Financial Operations', 'icon' => 'exchange-alt', 'url' => 'main.php?module=finance/operations'],
        ['label' => 'Audit Logs', 'icon' => 'history', 'url' => 'main.php?module=finance/audit_logs'],
        ['label' => 'Reports to GM', 'icon' => 'file-export', 'url' => 'main.php?module=finance/gm_reports'],
    ],
    'FINANCE_TEAM' => [
        ['label' => 'Finance Summary', 'icon' => 'tachometer-alt', 'url' => 'main.php?module=dashboards/roles/FINANCE_TEAM'],
        ['label' => 'Project Budgets', 'icon' => 'wallet', 'url' => 'main.php?module=finance/project_budgets'],
        ['label' => 'Expense Tracking', 'icon' => 'receipt', 'url' => 'main.php?module=finance/expense_tracking'],
        ['label' => 'Financial Bid Drafts', 'icon' => 'file-invoice-dollar', 'url' => 'main.php?module=finance/fin_bid_drafts'],
        ['label' => 'Export Reports', 'icon' => 'file-excel', 'url' => 'main.php?module=finance/reports'],
        ['label' => 'Submission History', 'icon' => 'history', 'url' => 'main.php?module=finance/history'],
    ],
    'AUDIT_TEAM' => [
        ['label' => 'Audit Dashboard', 'icon' => 'tachometer-alt', 'url' => 'main.php?module=dashboards/roles/AUDIT_TEAM'],
        ['label' => 'Budget vs Expense', 'icon' => 'chart-bar', 'url' => 'main.php?module=audit/budget_performance'],
        ['label' => 'Expense Ledger', 'icon' => 'list-alt', 'url' => 'main.php?module=audit/expense_ledger'],
        ['label' => 'Audit Trail', 'icon' => 'history', 'url' => 'main.php?module=audit/trail'],
        ['label' => 'Export Reports', 'icon' => 'file-download', 'url' => 'main.php?module=audit/reports'],
    ],
    'TECH_BID_MANAGER' => [
        ['label' => 'Technical Bid Overview', 'icon' => 'tachometer-alt', 'url' => 'main.php?module=dashboards/roles/TECH_BID_MANAGER'],
        ['label' => 'Bid Evaluation', 'icon' => 'drafting-compass', 'url' => 'main.php?module=technical/eval'],
        ['label' => 'Planning Integration', 'icon' => 'fill-drip', 'url' => 'main.php?module=technical/planning'],
        ['label' => 'Bid Submission', 'icon' => 'paper-plane', 'url' => 'main.php?module=technical/submit'],
        ['label' => 'Review History', 'icon' => 'history', 'url' => 'main.php?module=technical/history'],
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
