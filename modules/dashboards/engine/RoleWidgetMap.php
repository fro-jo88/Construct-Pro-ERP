<?php
// modules/dashboards/engine/RoleWidgetMap.php

return [
    'GM' => [
        'layout' => 'grid',
        'widgets' => [
            ['id' => 'kpi_card', 'size' => 'quarter', 'params' => ['type' => 'system_kpis']],
            ['id' => 'kpi_card', 'size' => 'quarter', 'params' => ['type' => 'budget_utilization']],
            ['id' => 'approval_queue', 'size' => 'half'],
            ['id' => 'project_list', 'size' => 'half'],
            ['id' => 'audit_logs', 'size' => 'half', 'params' => ['type' => 'construction_summary']],
            ['id' => 'daily_reports', 'size' => 'full']
        ]
    ],
    'HR_MANAGER' => [
        'layout' => 'grid',
        'widgets' => [
            ['id' => 'kpi_card', 'size' => 'quarter', 'params' => ['type' => 'headcount']],
            ['id' => 'attendance_today', 'size' => 'quarter'],
            ['id' => 'project_list', 'size' => 'half', 'params' => ['type' => 'active_bids']],
            ['id' => 'approval_queue', 'size' => 'half', 'params' => ['module' => 'HR']],
            ['id' => 'material_requests', 'size' => 'half'],
            ['id' => 'payroll_summary', 'size' => 'half']
        ]
    ],
    'FINANCE_HEAD' => [
        'layout' => 'grid',
        'widgets' => [
            ['id' => 'budget_overview', 'size' => 'half', 'params' => ['type' => 'vs_actual']],
            ['id' => 'approval_queue', 'size' => 'half', 'params' => ['module' => 'FINANCE']],
            ['id' => 'kpi_card', 'size' => 'half', 'params' => ['type' => 'expense_trends']],
            ['id' => 'bid_pipeline', 'size' => 'half', 'params' => ['type' => 'financial_pending']]
        ]
    ],
    'FINANCE_TEAM' => [
        'layout' => 'grid',
        'widgets' => [
            ['id' => 'budget_overview', 'size' => 'full'],
            ['id' => 'kpi_card', 'size' => 'half', 'params' => ['type' => 'excel_exports']]
        ]
    ],
    'AUDIT_TEAM' => [
        'layout' => 'grid',
        'widgets' => [
            ['id' => 'audit_logs', 'size' => 'full', 'params' => ['type' => 'financial_logs']],
            ['id' => 'kpi_card', 'size' => 'full', 'params' => ['type' => 'anomalies']]
        ]
    ],
    'TECH_BID_MANAGER' => [
        'layout' => 'grid',
        'widgets' => [
            ['id' => 'bid_pipeline', 'size' => 'half', 'params' => ['type' => 'technical']],
            ['id' => 'kpi_card', 'size' => 'half', 'params' => ['type' => 'specs_review']]
        ]
    ],
    'FINANCE_BID_MANAGER' => [
        'layout' => 'grid',
        'widgets' => [
            ['id' => 'bid_pipeline', 'size' => 'half', 'params' => ['type' => 'financial_drafts']],
            ['id' => 'kpi_card', 'size' => 'half', 'params' => ['type' => 'validation_status']]
        ]
    ],
    'PLANNING_MANAGER' => [
        'layout' => 'grid',
        'widgets' => [
            ['id' => 'schedule_overview', 'size' => 'full', 'params' => ['type' => 'master']],
            ['id' => 'daily_reports', 'size' => 'full', 'params' => ['type' => 'weekly_plans']]
        ]
    ],
    'PLANNING_ENGINEER' => [
        'layout' => 'grid',
        'widgets' => [
            ['id' => 'schedule_overview', 'size' => 'half', 'params' => ['type' => 'ms_schedules']],
            ['id' => 'kpi_card', 'size' => 'half', 'params' => ['type' => 'manpower_plan']]
        ]
    ],
    'FORMAN' => [
        'layout' => 'grid',
        'widgets' => [
            ['id' => 'daily_reports', 'size' => 'half', 'params' => ['scope' => 'assigned_tasks']],
            ['id' => 'material_requests', 'size' => 'half', 'params' => ['scope' => 'site']],
            ['id' => 'kpi_card', 'size' => 'full', 'params' => ['type' => 'progress_submission']]
        ]
    ],
    'STORE_MANAGER' => [
        'layout' => 'grid',
        'widgets' => [
            ['id' => 'inventory_status', 'size' => 'full', 'params' => ['scope' => 'all_sites']],
            ['id' => 'approval_queue', 'size' => 'full', 'params' => ['module' => 'STORE']]
        ]
    ],
    'STORE_KEEPER' => [
        'layout' => 'grid',
        'widgets' => [
            ['id' => 'inventory_status', 'size' => 'full', 'params' => ['scope' => 'site_store']],
            ['id' => 'material_requests', 'size' => 'full', 'params' => ['type' => 'issued']]
        ]
    ],
    'DRIVER_MANAGER' => [
        'layout' => 'grid',
        'widgets' => [
            ['id' => 'transport_trips', 'size' => 'full', 'params' => ['type' => 'fleet_overview']]
        ]
    ],
    'DRIVER' => [
        'layout' => 'grid',
        'widgets' => [
            ['id' => 'transport_trips', 'size' => 'full', 'params' => ['scope' => 'my_trips']]
        ]
    ],
    'TENDER_FINANCE' => [
        'layout' => 'grid',
        'widgets' => [
            ['id' => 'bid_pipeline', 'size' => 'full', 'params' => ['module' => 'finance']]
        ]
    ],
    'TENDER_TECHNICAL' => [
        'layout' => 'grid',
        'widgets' => [
            ['id' => 'bid_pipeline', 'size' => 'full', 'params' => ['module' => 'technical']]
        ]
    ],
    'PURCHASE_MANAGER' => [
        'layout' => 'grid',
        'widgets' => [
            ['id' => 'approval_queue', 'size' => 'half', 'params' => ['module' => 'PROCUREMENT']],
            ['id' => 'material_requests', 'size' => 'half'],
            ['id' => 'kpi_card', 'size' => 'full', 'params' => ['type' => 'vendor_kpis']]
        ]
    ],
    'PURCHASE_OFFICER' => [
        'layout' => 'grid',
        'widgets' => [
            ['id' => 'material_requests', 'size' => 'half', 'params' => ['type' => 'my_prs']],
            ['id' => 'kpi_card', 'size' => 'half', 'params' => ['type' => 'pr_status']]
        ]
    ],
    'CONSTRUCTION_AUDIT' => [
        'layout' => 'grid',
        'widgets' => [
            ['id' => 'audit_queue', 'size' => 'half'],
            ['id' => 'budget_overview', 'size' => 'half', 'params' => ['type' => 'planned_vs_actual']],
            ['id' => 'kpi_card', 'size' => 'full', 'params' => ['type' => 'material_variance']]
        ]
    ],
    'SYSTEM_ADMIN' => [
        'layout' => 'grid',
        'widgets' => [
            ['id' => 'system_health', 'size' => 'half'],
            ['id' => 'audit_logs', 'size' => 'half', 'params' => ['type' => 'security']]
        ]
    ],
    'SUPER_ADMIN' => [
        'layout' => 'grid',
        'widgets' => [
            ['id' => 'system_health', 'size' => 'quarter'],
            ['id' => 'approval_queue', 'size' => 'half'],
            ['id' => 'project_list', 'size' => 'quarter'],
            ['id' => 'audit_logs', 'size' => 'full']
        ]
    ],
    'default' => [
        'layout' => 'grid',
        'widgets' => [
            ['id' => 'kpi_card', 'size' => 'full', 'params' => ['type' => 'welcome']]
        ]
    ]
];
