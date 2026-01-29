<?php
// modules/dashboards/engine/RoleWidgetMap.php

return [
    'GM' => [
        'layout' => 'grid',
        'widgets' => [
            // Row 1: Executive KPIs
            ['id' => 'kpi_card', 'size' => 'quarter', 'params' => ['type' => 'active_projects']],
            ['id' => 'kpi_card', 'size' => 'quarter', 'params' => ['type' => 'active_bids']],
            ['id' => 'kpi_card', 'size' => 'quarter', 'params' => ['type' => 'pending_approvals']],
            ['id' => 'kpi_card', 'size' => 'quarter', 'params' => ['type' => 'audit_flags']],
            
            // Row 2: Approvals & Pipeline (Core Decision Center)
            ['id' => 'bid_pipeline', 'size' => 'half', 'params' => ['type' => 'gm_review']],
            ['id' => 'approval_queue', 'size' => 'half', 'params' => ['module' => 'GLOBAL']],

            // Row 3: Operations & Planning
            ['id' => 'daily_reports', 'size' => 'half', 'params' => ['scope' => 'all']],
            ['id' => 'schedule_overview', 'size' => 'half', 'params' => ['type' => 'master']],

            // Row 4: Financial & Audit Control
            ['id' => 'budget_overview', 'size' => 'half', 'params' => ['type' => 'vs_actual']],
            ['id' => 'audit_logs', 'size' => 'half', 'params' => ['type' => 'construction_summary']]
        ]
    ],
    'FINANCE_HEAD' => [
        'layout' => 'grid',
        'widgets' => [
            // Row 1: Financial KPIs
            ['id' => 'kpi_card', 'size' => 'quarter', 'params' => ['type' => 'total_project_budgets']],
            ['id' => 'kpi_card', 'size' => 'quarter', 'params' => ['type' => 'budget_remaining']],
            ['id' => 'kpi_card', 'size' => 'quarter', 'params' => ['type' => 'finance_bids_pending']],
            ['id' => 'kpi_card', 'size' => 'quarter', 'params' => ['type' => 'budget_overrun_alerts']],

            // Row 2: Control & Review
            ['id' => 'finance_bid_review', 'size' => 'half'],
            ['id' => 'approval_queue', 'size' => 'half', 'params' => ['module' => 'FINANCE']],

            // Row 3: Operations Monitor
            ['id' => 'budget_overview', 'size' => 'half'],
            ['id' => 'finance_ops_monitor', 'size' => 'half'],

            // Row 4: Compliance & Reporting
            ['id' => 'audit_logs', 'size' => 'half', 'params' => ['type' => 'financial']],
            ['id' => 'gm_reporting_panel', 'size' => 'half']
        ]
    ],
    'HR_MANAGER' => [
        'layout' => 'grid',
        'widgets' => [
            // Row 1: Status Cards
            ['id' => 'kpi_card', 'size' => 'quarter', 'params' => ['type' => 'headcount']],
            ['id' => 'kpi_card', 'size' => 'quarter', 'params' => ['type' => 'active_projects']],
            ['id' => 'kpi_card', 'size' => 'quarter', 'params' => ['type' => 'pending_leaves']],
            ['id' => 'kpi_card', 'size' => 'quarter', 'params' => ['type' => 'pending_material_reqs']],
            
            // Row 2: Operational Control centers
            ['id' => 'hr_actions', 'size' => 'half'],
            ['id' => 'hr_bridge', 'size' => 'half'],
            
            // Row 3: Bidding & Assignments
            ['id' => 'bid_pipeline', 'size' => 'half', 'params' => ['type' => 'all']],
            ['id' => 'hr_assignments', 'size' => 'half'],
            
            // Row 4: Payroll & Admin
            ['id' => 'payroll_summary', 'size' => 'half'],
            ['id' => 'approval_queue', 'size' => 'half', 'params' => ['module' => 'HR']]
        ]
    ],
    'FINANCE_TEAM' => [
        'layout' => 'grid',
        'widgets' => [
            // Row 1: Finance Summary KPIs
            ['id' => 'kpi_card', 'size' => 'quarter', 'params' => ['type' => 'active_projects']],
            ['id' => 'kpi_card', 'size' => 'quarter', 'params' => ['type' => 'total_project_budgets']],
            ['id' => 'kpi_card', 'size' => 'quarter', 'params' => ['type' => 'total_expenses']],
            ['id' => 'kpi_card', 'size' => 'quarter', 'params' => ['type' => 'budget_remaining']],

            // Row 2: Budgeting & Expenses
            ['id' => 'budget_manager', 'size' => 'half'],
            ['id' => 'expense_entry', 'size' => 'half'],

            // Row 3: Bidding & Exports
            ['id' => 'fin_bid_drafts', 'size' => 'half'],
            ['id' => 'submission_history', 'size' => 'half']
        ]
    ],
    'AUDIT_TEAM' => [
        'layout' => 'grid',
        'widgets' => [
            // Row 1: Audit KPIs
            ['id' => 'kpi_card', 'size' => 'quarter', 'params' => ['type' => 'projects_audited']],
            ['id' => 'kpi_card', 'size' => 'quarter', 'params' => ['type' => 'total_budget_global']],
            ['id' => 'kpi_card', 'size' => 'quarter', 'params' => ['type' => 'total_expenses_global']],
            ['id' => 'kpi_card', 'size' => 'quarter', 'params' => ['type' => 'budget_utilization_global']],

            // Row 2: Performance Tracking
            ['id' => 'budget_performance', 'size' => 'full'],

            // Row 3: Transaction Ledger
            ['id' => 'expense_ledger', 'size' => 'full'],

            // Row 4: Risk & Compliance
            ['id' => 'audit_alerts', 'size' => 'half'],
            ['id' => 'financial_audit_log', 'size' => 'half'],

            // Row 5: Actions
            ['id' => 'audit_exports', 'size' => 'full']
        ]
    ],
    'TECH_BID_MANAGER' => [
        'layout' => 'grid',
        'widgets' => [
            // Row 1: Technical KPIs
            ['id' => 'kpi_card', 'size' => 'quarter', 'params' => ['type' => 'active_tech_bids']],
            ['id' => 'kpi_card', 'size' => 'quarter', 'params' => ['type' => 'evaluations_pending']],
            ['id' => 'kpi_card', 'size' => 'quarter', 'params' => ['type' => 'sent_to_planning']],
            ['id' => 'kpi_card', 'size' => 'quarter', 'params' => ['type' => 'tech_submitted_to_gm']],

            // Row 2: Evaluation & Planning
            ['id' => 'tech_bid_evaluator', 'size' => 'half'],
            ['id' => 'planning_interface', 'size' => 'half'],

            // Row 3: Submission & Pipeline
            ['id' => 'tech_bid_submission', 'size' => 'half'],
            ['id' => 'bid_pipeline', 'size' => 'half', 'params' => ['type' => 'technical']]
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
