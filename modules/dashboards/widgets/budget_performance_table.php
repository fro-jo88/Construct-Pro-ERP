<?php
// modules/dashboards/widgets/budget_performance_table.php
require_once __DIR__ . '/../../../includes/Database.php';

$db = Database::getInstance();
$performance = [];

try {
    // SQL provided in the request
    $performance = $db->query("SELECT 
                                p.project_name,
                                COALESCE(b.total_amount, 0) AS total_budget,
                                COALESCE(SUM(e.amount), 0) AS total_expense,
                                (COALESCE(b.total_amount, 0) - COALESCE(SUM(e.amount), 0)) AS balance
                              FROM projects p
                              LEFT JOIN budgets b ON p.id = b.project_id
                              LEFT JOIN expenses e ON p.id = e.project_id
                              GROUP BY p.id")->fetchAll();
} catch (Exception $e) { /* Ignore */ }

?>
<div class="widget glass-card">
    <div class="widget-header">
        <h3><i class="fas fa-chart-line text-gold"></i> Budget vs Expense Analysis</h3>
    </div>
    <div class="widget-content">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Project Name</th>
                    <th>Approved Budget</th>
                    <th>Actual Expense</th>
                    <th>Balance</th>
                    <th style="width: 150px;">Usage (%)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($performance)): ?>
                    <tr><td colspan="5" class="text-center text-dim">No financial data found.</td></tr>
                <?php else: ?>
                    <?php foreach ($performance as $p): 
                        $usage = $p['total_budget'] > 0 ? ($p['total_expense'] / $p['total_budget']) * 100 : 0;
                        $color = 'success';
                        if ($usage >= 100) $color = 'danger';
                        elseif ($usage >= 80) $color = 'warning';
                    ?>
                        <tr>
                            <td style="font-weight: bold;"><?= htmlspecialchars($p['project_name']) ?></td>
                            <td><?= number_format($p['total_budget'], 2) ?></td>
                            <td><?= number_format($p['total_expense'], 2) ?></td>
                            <td class="<?= $p['balance'] < 0 ? 'text-danger' : 'text-success' ?>">
                                <?= number_format($p['balance'], 2) ?>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress-mini" style="flex-grow: 1; height: 6px; background: rgba(255,255,255,0.05); border-radius: 3px; overflow: hidden; margin-right: 8px;">
                                        <div style="width: <?= min(100, $usage) ?>%; height: 100%; background: var(--<?= $color === 'success' ? 'gold' : ($color === 'warning' ? 'warning' : 'danger') ?>);" class="progress-bar-<?= $color ?>"></div>
                                    </div>
                                    <span style="font-size: 0.7rem; min-width: 35px;"><?= number_format($usage, 0) ?>%</span>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.progress-bar-success { background-color: #00ff64 !important; }
.progress-bar-warning { background-color: #ffaa00 !important; }
.progress-bar-danger { background-color: #ff4444 !important; }
</style>
