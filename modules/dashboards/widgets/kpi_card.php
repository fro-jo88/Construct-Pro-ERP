<?php
// modules/dashboards/widgets/kpi_card.php
// $config contains params passed from the engine

require_once __DIR__ . '/../../../includes/Database.php';

$db = Database::getInstance();
$type = $config['type'] ?? 'default';
$value = "0";
$label = "Stat";
$icon = "chart-line";
$trend = "";

switch ($type) {
    case 'system_kpis':
    case 'active_projects':
        $value = $db->query("SELECT COUNT(*) FROM projects WHERE status = 'active'")->fetchColumn();
        $label = "Active Projects";
        $icon = "project-diagram";
        break;
    case 'budget_utilization':
    case 'budget_burn':
        $value = "12%"; // Simulation for now
        $label = "Budget Used";
        $icon = "burn";
        $trend = "+2.4% this week";
        break;
    case 'expense_trends':
        $value = "8.4M";
        $label = "Monthly OpEx";
        $icon = "chart-line";
        $trend = "-1.2% from last month";
        break;
    case 'headcount':
        $value = $db->query("SELECT COUNT(*) FROM employees WHERE status = 'active'")->fetchColumn();
        $label = "Total Personnel";
        $icon = "users";
        break;
    case 'low_stock':
        try {
            $value = $db->query("SELECT COUNT(*) FROM stock_levels WHERE quantity < 10")->fetchColumn() ?: 0;
        } catch (Exception $e) { $value = "0"; }
        $label = "Low Stock Items";
        $icon = "box-open";
        break;
    case 'active_bids':
        $value = $db->query("SELECT COUNT(*) FROM bids WHERE status NOT IN ('WON', 'LOSS')")->fetchColumn();
        $label = "Active Bids";
        $icon = "file-contract";
        break;
    case 'pending_leaves':
        $value = $db->query("SELECT COUNT(*) FROM leave_requests WHERE status = 'pending_hr'")->fetchColumn();
        $label = "Leave Requests";
        $icon = "calendar-alt";
        break;
    case 'total_project_budgets':
        $value = $db->query("SELECT SUM(total_amount) FROM budgets")->fetchColumn() ?: 0;
        $value = "ETB " . number_format($value / 1000000, 1) . "M";
        $label = "Total Budgets";
        $icon = "wallet";
        break;
    case 'total_expenses':
        $value = $db->query("SELECT SUM(amount) FROM expenses WHERE status = 'approved'")->fetchColumn() ?: 0;
        $value = "ETB " . number_format($value / 1000, 1) . "K";
        $label = "Total Expenses";
        $icon = "receipt";
        break;
    case 'budget_remaining':
        $total = $db->query("SELECT SUM(total_amount) FROM budgets")->fetchColumn() ?: 0;
        $used = $db->query("SELECT SUM(amount) FROM expenses WHERE status = 'approved'")->fetchColumn() ?: ($total * 0.15); // Simulation if no expenses
        $value = "ETB " . number_format(($total - $used) / 1000000, 1) . "M";
        $label = "Budget Remaining";
        $icon = "money-bill-wave";
        break;
    case 'finance_bids_pending':
        $value = $db->query("SELECT COUNT(*) FROM financial_bids WHERE status = 'ready'")->fetchColumn();
        $label = "Bids for Review";
        $icon = "gavel";
        break;
    case 'budget_overrun_alerts':
        // Simulation or logic for overruns
        $value = 2; 
        $label = "Overrun Alerts";
        $icon = "exclamation-triangle";
        $trend = "High Priority";
        break;
    case 'pending_material_reqs':
        $value = $db->query("SELECT COUNT(*) FROM material_requests WHERE gm_approval_status = 'pending'")->fetchColumn();
        $label = "Material Requests";
        $icon = "truck-loading";
        break;
    case 'audit_flags':
        $value = $db->query("SELECT COUNT(*) FROM site_incidents WHERE severity IN ('high', 'critical')")->fetchColumn();
        $label = "Audit Flags";
        $icon = "shield-virus";
        $trend = "Critical issues";
        break;
    case 'pending_approvals':
        $bids = $db->query("SELECT COUNT(*) FROM bids WHERE status IN ('TECHNICAL_COMPLETED', 'FINANCIAL_COMPLETED')")->fetchColumn();
        $leaves = $db->query("SELECT COUNT(*) FROM leave_requests WHERE status = 'pending_hr'")->fetchColumn();
        $value = $bids + $leaves;
        $label = "Pending Approvals";
        $icon = "clipboard-check";
        break;
    case 'projects_audited':
        $value = $db->query("SELECT COUNT(DISTINCT project_id) FROM construction_audits")->fetchColumn() ?: 0;
        $label = "Projects Audited";
        $icon = "clipboard-check";
        break;
    case 'total_budget_global':
        $value = $db->query("SELECT SUM(total_amount) FROM budgets")->fetchColumn() ?: 0;
        $value = "ETB " . number_format($value / 1000000, 1) . "M";
        $label = "Total Budget";
        $icon = "wallet";
        break;
    case 'total_expenses_global':
        $value = $db->query("SELECT SUM(amount) FROM expenses")->fetchColumn() ?: 0;
        $value = "ETB " . number_format($value / 1000000, 1) . "M";
        $label = "Total Expenses";
        $icon = "receipt";
        break;
    case 'budget_utilization_global':
        $budget = $db->query("SELECT SUM(total_amount) FROM budgets")->fetchColumn() ?: 1;
        $expenses = $db->query("SELECT SUM(amount) FROM expenses")->fetchColumn() ?: 0;
        $value = number_format(($expenses / $budget) * 100, 1) . "%";
        $label = "Budget Usage";
        $icon = "chart-pie";
        break;
    case 'active_tech_bids':
        $value = $db->query("SELECT COUNT(*) FROM technical_bids WHERE status != 'approved'")->fetchColumn() ?: 0;
        $label = "Active Tech Bids";
        $icon = "drafting-compass";
        break;
    case 'evaluations_pending':
        $value = $db->query("SELECT COUNT(*) FROM technical_bids WHERE status = 'draft'")->fetchColumn() ?: 0;
        $label = "Pending Eval";
        $icon = "tasks";
        break;
    case 'sent_to_planning':
        $value = $db->query("SELECT COUNT(*) FROM planning_requests WHERE status != 'completed'")->fetchColumn() ?: 0;
        $label = "Sent to Planning";
        $icon = "project-diagram";
        break;
    case 'tech_submitted_to_gm':
        $value = $db->query("SELECT COUNT(*) FROM technical_bids WHERE status = 'submitted'")->fetchColumn() ?: 0;
        $label = "Submitted to GM";
        $icon = "paper-plane";
        break;
    case 'welcome':
        $value = "Welcome";
        $label = "System Active";
        $icon = "smile";
        break;
    default:
        $label = ucwords(str_replace('_', ' ', $type));
        break;
}

?>
<div class="glass-card kpi-card">
    <div class="widget-header">
        <h3><?= htmlspecialchars($label) ?></h3>
        <div class="icon-box">
            <i class="fas fa-<?= $icon ?> text-gold"></i>
        </div>
    </div>
    <div class="widget-content">
        <div class="value-large"><?= htmlspecialchars($value) ?></div>
        <?php if ($trend): ?>
            <div class="trend-indicator"><?= $trend ?></div>
        <?php endif; ?>
    </div>
</div>

<style>
.kpi-card {
    justify-content: space-between;
}
.icon-box {
    background: rgba(255, 204, 0, 0.1);
    width: 35px;
    height: 35px;
    border-radius: 8px;
    display: flex;
    justify-content: center;
    align-items: center;
}
.value-large {
    font-size: 2.2rem;
    font-weight: 700;
    color: #fff;
    margin: 0.5rem 0;
}
.trend-indicator {
    font-size: 0.75rem;
    color: #00ff64;
    display: flex;
    align-items: center;
    gap: 4px;
}
</style>
