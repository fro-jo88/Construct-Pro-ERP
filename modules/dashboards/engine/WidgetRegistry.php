<?php
// modules/dashboards/engine/WidgetRegistry.php

class WidgetRegistry {
    private static $widgets = [
        'kpi_card' => [
            'template' => 'kpi_card.php',
            'controller' => 'KPIController::getStats'
        ],
        'approval_queue' => [
            'template' => 'approval_queue.php',
            'controller' => 'ApprovalController::getQueue'
        ],
        'project_list' => [
            'template' => 'project_list.php',
            'controller' => 'ProjectController::getRecent'
        ],
        'daily_reports' => [
            'template' => 'daily_reports_list.php',
            'controller' => 'ReportController::getDaily'
        ],
        'material_requests' => [
            'template' => 'material_requests.php',
            'controller' => 'InventoryController::getRequests'
        ],
        'budget_overview' => [
            'template' => 'budget_overview.php',
            'controller' => 'FinanceController::getBudgetKPIs'
        ],
        'attendance_today' => [
            'template' => 'kpi_card.php',
            'controller' => 'HRController::getAttendance'
        ],
        'inventory_status' => [
            'template' => 'inventory_status.php',
            'controller' => 'InventoryController::getStatus'
        ],
        'transport_trips' => [
            'template' => 'transport_trips.php',
            'controller' => 'TransportController::getTrips'
        ],
        'audit_logs' => [
            'template' => 'audit_findings.php',
            'controller' => 'AuditController::getLogs'
        ],
        'system_health' => [
            'template' => 'kpi_card.php',
            'controller' => 'AdminController::getHealth'
        ],
        'bid_pipeline' => [
            'template' => 'bid_pipeline.php',
            'controller' => 'BidController::getPipeline'
        ],
        'schedule_overview' => [
            'template' => 'schedule_overview.php',
            'controller' => 'PlanningController::getSchedules'
        ],
        'payroll_summary' => [
            'template' => 'payroll_summary.php',
            'controller' => 'HRController::getPayroll'
        ],
        'audit_queue' => [
            'template' => 'audit_queue.php',
            'controller' => 'AuditController::getQueue'
        ],
        'finance_bid_review' => [
            'template' => 'finance_bid_review.php',
            'controller' => 'FinanceController::getPendingBids'
        ],
        'finance_ops_monitor' => [
            'template' => 'finance_ops_monitor.php',
            'controller' => 'FinanceController::getOpsData'
        ],
        'gm_reporting_panel' => [
            'template' => 'gm_reporting_panel.php',
            'controller' => 'FinanceController::getReportStats'
        ],
        'hr_actions' => [
            'template' => 'hr_actions.php',
            'controller' => 'HRController::getActions'
        ],
        'hr_bridge' => [
            'template' => 'hr_bridge.php',
            'controller' => 'HRController::getBridgeInfo'
        ],
        'hr_assignments' => [
            'template' => 'hr_assignments.php',
            'controller' => 'HRController::getAssignmentNeeds'
        ],
        'budget_manager' => [
            'template' => 'budget_manager.php',
            'controller' => 'FinanceController::getBudgets'
        ],
        'expense_entry' => [
            'template' => 'expense_entry.php',
            'controller' => 'FinanceController::getExpenses'
        ],
        'fin_bid_drafts' => [
            'template' => 'fin_bid_drafts.php',
            'controller' => 'FinanceController::getBidDrafts'
        ],
        'submission_history' => [
            'template' => 'submission_history.php',
            'controller' => 'FinanceController::getHistory'
        ],
        'budget_performance' => [
            'template' => 'budget_performance_table.php',
            'controller' => 'AuditController::getPerformance'
        ],
        'expense_ledger' => [
            'template' => 'expense_ledger.php',
            'controller' => 'AuditController::getLedger'
        ],
        'audit_alerts' => [
            'template' => 'audit_alerts.php',
            'controller' => 'AuditController::getAlerts'
        ],
        'financial_audit_log' => [
            'template' => 'financial_audit_log.php',
            'controller' => 'AuditController::getLogs'
        ],
        'audit_exports' => [
            'template' => 'audit_export_controls.php',
            'controller' => 'AuditController::getExportStats'
        ],
        'tech_bid_evaluator' => [
            'template' => 'tech_bid_evaluator.php',
            'controller' => 'TechnicalController::getPendingEvals'
        ],
        'planning_interface' => [
            'template' => 'planning_interface.php',
            'controller' => 'TechnicalController::getPlanningStatus'
        ],
        'tech_bid_submission' => [
            'template' => 'tech_bid_submission.php',
            'controller' => 'TechnicalController::getReadyBids'
        ]
    ];

    public static function get($widgetId) {
        return self::$widgets[$widgetId] ?? null;
    }

    public static function render($widgetId, $data = [], $config = []) {
        $widget = self::get($widgetId);
        if (!$widget) return "<!-- Widget $widgetId not found -->";

        $templatePath = __DIR__ . '/../widgets/' . $widget['template'];
        if (!file_exists($templatePath)) return "<!-- Template " . $widget['template'] . " missing -->";

        ob_start();
        include $templatePath;
        return ob_get_clean();
    }
}
