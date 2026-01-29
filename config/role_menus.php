<?php
// config/role_menus.php

return [
    'GM' => [
        ['label' => 'Executive Dashboard', 'icon' => 'tachometer-alt', 'url' => 'main.php?module=dashboards/roles/GM'],
        ['label' => 'Pending Approvals', 'icon' => 'gavel', 'url' => 'main.php?module=gm/approvals'],
        ['label' => 'Bid Review', 'icon' => 'file-contract', 'url' => 'main.php?module=bidding/gm_review'],
        ['label' => 'Project Oversight', 'icon' => 'building', 'url' => 'main.php?module=gm/project_details'],
        ['label' => 'Finance Oversight', 'icon' => 'money-bill-wave', 'url' => 'main.php?module=gm/finance_oversight'],
        ['label' => 'Inventory Oversight', 'icon' => 'boxes', 'url' => 'main.php?module=gm/inventory_oversight'],
        ['label' => 'Site Reports', 'icon' => 'clipboard-list', 'url' => 'main.php?module=gm/site_reports'],
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
        ['label' => 'Technical Documentation', 'icon' => 'file-alt', 'url' => 'main.php?module=tender/technical'],
        ['label' => 'Bid Evaluation', 'icon' => 'drafting-compass', 'url' => 'main.php?module=technical/eval'],
        ['label' => 'Planning Integration', 'icon' => 'fill-drip', 'url' => 'main.php?module=technical/planning'],
        ['label' => 'Bid Submission', 'icon' => 'paper-plane', 'url' => 'main.php?module=technical/submit'],
        ['label' => 'Review History', 'icon' => 'history', 'url' => 'main.php?module=technical/history'],
    ],
    'FINANCE_BID_MANAGER' => [
        ['label' => 'Overview', 'icon' => 'th-large', 'url' => 'main.php?module=bidding/finance_bid_dashboard/index&view=overview'],
        ['label' => 'Bid Coordination', 'icon' => 'project-diagram', 'url' => 'main.php?module=bidding/finance_bid_dashboard/index&view=coordination'],
        ['label' => 'Force Estimation', 'icon' => 'calculator', 'url' => 'main.php?module=bidding/finance_bid_dashboard/index&view=preparation'],
        ['label' => 'Internal Review', 'icon' => 'shield-alt', 'url' => 'main.php?module=bidding/finance_bid_dashboard/index&view=verification'],
        ['label' => 'Dispatch to GM', 'icon' => 'external-link-alt', 'url' => 'main.php?module=bidding/finance_bid_dashboard/index&view=submission'],
        ['label' => 'Vault History', 'icon' => 'archive', 'url' => 'main.php?module=bidding/finance_bid_dashboard/index&view=history'],
    ],
    'PLANNING_MANAGER' => [
        ['label' => 'Planning Manager Dashboard', 'icon' => 'network-wired', 'url' => 'main.php?module=planning/manager_dashboard/index'],
        ['label' => 'Master Schedules', 'icon' => 'calendar-alt', 'url' => 'main.php?module=planning/manager_dashboard/index&view=validation'],
        ['label' => 'Weekly Review', 'icon' => 'calendar-week', 'url' => 'main.php?module=planning/manager_dashboard/index&view=weekly_review'],
    ],
    'PLANNING_ENGINEER' => [
        ['label' => 'Engineer Workspace', 'icon' => 'clipboard-list', 'url' => 'main.php?module=planning/engineer_dashboard/index'],
        ['label' => 'My Schedules', 'icon' => 'calendar-alt', 'url' => 'main.php?module=planning/engineer_dashboard/index&view=schedules'],
        ['label' => 'Weekly Plans', 'icon' => 'calendar-week', 'url' => 'main.php?module=planning/engineer_dashboard/index&view=weekly_plans'],
        ['label' => 'Bid Support', 'icon' => 'drafting-compass', 'url' => 'main.php?module=planning/engineer_dashboard/index&view=bid_support'],
    ],
    'FORMAN' => [
        ['label' => 'Site Overview', 'icon' => 'hard-hat', 'url' => 'main.php?module=site/forman_dashboard/index'],
        ['label' => 'Daily Reports', 'icon' => 'edit', 'url' => 'main.php?module=site/forman_dashboard/index&view=reports'],
        ['label' => 'Material Requests', 'icon' => 'truck', 'url' => 'main.php?module=site/forman_dashboard/index&view=materials'],
        ['label' => 'Weekly Plan', 'icon' => 'calendar-week', 'url' => 'main.php?module=site/forman_dashboard/index&view=plans'],
        ['label' => 'Safety & Issues', 'icon' => 'exclamation-triangle', 'url' => 'main.php?module=site/forman_dashboard/index&view=safety'],
        ['label' => 'GM Messages', 'icon' => 'comment-dots', 'url' => 'main.php?module=site/forman_dashboard/index&view=messages'],
    ],
    'STORE_MANAGER' => [
        ['label' => 'Stock Overview', 'icon' => 'warehouse', 'url' => 'main.php?module=store/manager_dashboard/index'],
        ['label' => 'Issue Requests', 'icon' => 'clipboard-check', 'url' => 'main.php?module=store/manager_dashboard/index&view=issues'],
        ['label' => 'Stock Transfers', 'icon' => 'exchange-alt', 'url' => 'main.php?module=store/manager_dashboard/index&view=transfers'],
        ['label' => 'Global Inventory', 'icon' => 'boxes', 'url' => 'main.php?module=store/manager_dashboard/index&view=inventory'],
        ['label' => 'Low Stock Alerts', 'icon' => 'exclamation-triangle', 'url' => 'main.php?module=store/manager_dashboard/index&view=alerts'],
        ['label' => 'Audit & Logs', 'icon' => 'history', 'url' => 'main.php?module=store/manager_dashboard/index&view=logs'],
    ],
    'STORE_KEEPER' => [
        ['label' => 'My Store Overview', 'icon' => 'archive', 'url' => 'main.php?module=store/keeper_dashboard/index'],
        ['label' => 'Approved Issues', 'icon' => 'clipboard-list', 'url' => 'main.php?module=store/keeper_dashboard/index&view=issues'],
        ['label' => 'Stock Updates', 'icon' => 'edit', 'url' => 'main.php?module=store/keeper_dashboard/index&view=updates'],
        ['label' => 'Internal Transfers', 'icon' => 'exchange-alt', 'url' => 'main.php?module=store/keeper_dashboard/index&view=transfers'],
        ['label' => 'Stock History', 'icon' => 'history', 'url' => 'main.php?module=store/keeper_dashboard/index&view=history'],
    ],
    'DRIVER_MANAGER' => [
        ['label' => 'Transport Overview', 'icon' => 'shipping-fast', 'url' => 'main.php?module=transport/driver_manager_dashboard/index'],
        ['label' => 'Pending Requests', 'icon' => 'clock', 'url' => 'main.php?module=transport/driver_manager_dashboard/index&view=pending_requests'],
        ['label' => 'Assign Drivers', 'icon' => 'user-plus', 'url' => 'main.php?module=transport/driver_manager_dashboard/index&view=assign_driver'],
        ['label' => 'Transport Schedule', 'icon' => 'calendar-alt', 'url' => 'main.php?module=transport/driver_manager_dashboard/index&view=schedule'],
        ['label' => 'Live Trips', 'icon' => 'map-marker-alt', 'url' => 'main.php?module=transport/driver_manager_dashboard/index&view=live_trips'],
        ['label' => 'Drivers & Vehicles', 'icon' => 'car-side', 'url' => 'main.php?module=transport/driver_manager_dashboard/index&view=assets'],
    ],
    'DRIVER' => [
        ['label' => 'My Trips', 'icon' => 'route', 'url' => 'main.php?module=dashboards/roles/DRIVER'],
        ['label' => 'Status Update', 'icon' => 'gas-pump', 'url' => 'main.php?module=transport/vehicle_status'],
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
        ['label' => 'Audit Dashboard', 'icon' => 'search-location', 'url' => 'main.php?module=audit/construction_dashboard/index'],
        ['label' => 'Site Audits', 'icon' => 'clipboard-list', 'url' => 'main.php?module=audit/construction_dashboard/index&view=site_audits'],
        ['label' => 'Material Audits', 'icon' => 'boxes', 'url' => 'main.php?module=audit/construction_dashboard/index&view=material_audits'],
        ['label' => 'Reports History', 'icon' => 'history', 'url' => 'main.php?module=audit/construction_dashboard/index&view=reports'],
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
        ['label' => 'Announcements', 'icon' => 'bullhorn', 'url' => 'main.php?module=common/announcements'],
        ['label' => 'HR Feedback', 'icon' => 'comments', 'url' => 'main.php?module=common/my_messages'],
        ['label' => 'Leave Request', 'icon' => 'calendar-plus', 'url' => 'main.php?module=leave/request'],
    ]
];
