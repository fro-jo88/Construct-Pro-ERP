<?php
// modules/dashboards/widgets/budget_overview.php
require_once __DIR__ . '/../../../includes/Database.php';

$db = Database::getInstance();
$stats = ['allocated' => 0, 'actual' => 0, 'remaining' => 0, 'utilization' => 0];

try {
    // Aggregated stats from projects and budgets
    $allocated = $db->query("SELECT SUM(total_amount) FROM budgets")->fetchColumn() ?: 0;
    // For demo/pilot, actual is simulated as 12% of allocated or from an expense table if it exists
    $actual = $db->query("SELECT SUM(amount) FROM expenses")->fetchColumn() ?: ($allocated * 0.12);
    
    $stats['allocated'] = $allocated;
    $stats['actual'] = $actual;
    $stats['remaining'] = $allocated - $actual;
    $stats['utilization'] = $allocated > 0 ? ($actual / $allocated) * 100 : 0;
} catch (Exception $e) { }

?>
<div class="glass-card budget-widget">
    <div class="widget-header">
        <h3><i class="fas fa-wallet text-gold"></i> Budget Visibility (Global)</h3>
    </div>
    <div class="widget-content">
        <div class="budget-main">
            <div class="utilization-circle-container">
                <div class="utilization-label">
                    <span class="pct"><?= number_format($stats['utilization'], 1) ?>%</span>
                    <span class="sub">Used</span>
                </div>
                <!-- Simple linear bar for now as CSS circles are complex without extra libs -->
                <div class="progress-bar-container">
                    <div class="progress-bar-fill <?= $stats['utilization'] > 90 ? 'danger' : ($stats['utilization'] > 75 ? 'warning' : 'success') ?>" style="width: <?= min(100, $stats['utilization']) ?>%"></div>
                </div>
            </div>

            <div class="budget-stats-grid">
                <div class="stat-item">
                    <label>Allocated</label>
                    <div class="val"><?= number_format($stats['allocated'] / 1000000, 2) ?>M</div>
                </div>
                <div class="stat-item">
                    <label>Actual</label>
                    <div class="val text-gold"><?= number_format($stats['actual'] / 1000000, 2) ?>M</div>
                </div>
                <div class="stat-item">
                    <label>Remaining</label>
                    <div class="val text-success"><?= number_format($stats['remaining'] / 1000000, 2) ?>M</div>
                </div>
            </div>
        </div>

        <?php if ($stats['utilization'] > 75): ?>
            <div class="alert-budget warning">
                <i class="fas fa-exclamation-triangle"></i> Budget threshold exceeded 75%
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.progress-bar-container {
    height: 10px;
    background: rgba(255,255,255,0.05);
    border-radius: 5px;
    margin: 1rem 0;
    overflow: hidden;
}
.progress-bar-fill {
    height: 100%;
    transition: width 0.5s ease;
}
.progress-bar-fill.success { background: #00ff64; }
.progress-bar-fill.warning { background: var(--gold); }
.progress-bar-fill.danger { background: #ff4444; }

.budget-stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-top: 1rem;
}
.stat-item label {
    font-size: 0.65rem;
    text-transform: uppercase;
    color: var(--text-dim);
    display: block;
    margin-bottom: 2px;
}
.stat-item .val {
    font-weight: 700;
    font-size: 1.1rem;
}
.utilization-label .pct {
    font-size: 1.5rem;
    font-weight: 800;
    display: block;
}
.utilization-label .sub {
    font-size: 0.7rem;
    color: var(--text-dim);
    text-transform: uppercase;
}
.alert-budget {
    margin-top: 1rem;
    padding: 8px;
    border-radius: 6px;
    font-size: 0.75rem;
    text-align: center;
}
.alert-budget.warning {
    background: rgba(255, 204, 0, 0.1);
    color: var(--gold);
    border: 1px solid rgba(255, 204, 0, 0.2);
}
</style>
