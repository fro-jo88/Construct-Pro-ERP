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
