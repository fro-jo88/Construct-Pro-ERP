<?php
// modules/gm/finance_oversight.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/GMManager.php';

AuthManager::requireRole('GM');

$db = Database::getInstance();

// Aggregate Project Budgets
$budgets = $db->query("SELECT b.*, p.project_name, p.budget as initial_est,
                       (SELECT SUM(amount) FROM expenses WHERE project_id = p.id AND status = 'approved') as actual_spent
                       FROM budgets b 
                       JOIN projects p ON b.project_id = p.id")->fetchAll();

// Major Expenses (High Value > $5000)
$major_expenses = $db->query("SELECT e.*, p.project_name, u.username as approver 
                             FROM expenses e 
                             JOIN projects p ON e.project_id = p.id 
                             LEFT JOIN users u ON e.gm_approved_by = u.id 
                             WHERE e.amount > 5000 ORDER BY e.created_at DESC LIMIT 10")->fetchAll();
?>

<div class="gm-finance">
    <div class="page-header mb-4">
        <h2><i class="fas fa-coins"></i> Enterprise Financial Intelligence</h2>
        <p class="text-dim">Global burn rates, budget utilization, and high-value expenditure oversight.</p>
    </div>

    <div style="display:grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
        <!-- Budget Burn Rates -->
        <section class="glass-card">
            <h3><i class="fas fa-chart-line"></i> Project Burn Rates</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Project</th>
                        <th>Approved Budget</th>
                        <th>Actual Spent</th>
                        <th>Remaining</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($budgets as $b): ?>
                    <tr>
                        <td><strong><?= $b['project_name'] ?></strong></td>
                        <td>$<?= number_format($b['total_amount'], 2) ?></td>
                        <td>$<?= number_format($b['actual_spent'], 2) ?></td>
                        <td style="color:var(--gold); font-weight:bold;">$<?= number_format($b['total_amount'] - $b['actual_spent'], 2) ?></td>
                        <td>
                            <?php 
                            $burn = $b['total_amount'] > 0 ? ($b['actual_spent'] / $b['total_amount']) * 100 : 0;
                            $color = $burn > 90 ? '#ff4444' : ($burn > 70 ? '#ffcc00' : '#00ff64');
                            ?>
                            <div style="display:flex; align-items:center; gap:0.5rem;">
                                <div style="width:40px; height:4px; background:rgba(255,255,255,0.05); border-radius:2px;">
                                    <div style="width:<?= min(100, $burn) ?>%; height:100%; background:<?= $color ?>;"></div>
                                </div>
                                <small style="color:<?= $color ?>;"><?= round($burn, 0) ?>%</small>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <!-- High-Value Expenditures -->
        <section class="glass-card">
            <h3 style="color:#ffcc00;"><i class="fas fa-exclamation-circle"></i> High-Value Control</h3>
            <p class="text-dim mb-3">Recent expenses exceeding $5,000 threshold.</p>
            <div class="expense-stream">
                <?php foreach ($major_expenses as $e): ?>
                <div style="background:rgba(255,255,255,0.03); border-left:3px solid #ffcc00; padding:1rem; border-radius:8px; margin-bottom:1rem;">
                    <div style="display:flex; justify-content:space-between;">
                        <span style="font-weight:bold; color:var(--gold);">$<?= number_format($e['amount'], 2) ?></span>
                        <small class="text-dim"><?= date('M d', strtotime($e['created_at'])) ?></small>
                    </div>
                    <div style="font-size:0.8rem; margin:0.3rem 0;"><?= $e['description'] ?></div>
                    <div style="font-size:0.7rem; color:var(--text-dim);">Project: <?= $e['project_name'] ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</div>
