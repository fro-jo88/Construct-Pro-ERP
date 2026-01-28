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
            'template' => 'kpi_card.php', // Reusing KPI template
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
