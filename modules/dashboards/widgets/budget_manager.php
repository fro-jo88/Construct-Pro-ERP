<?php
// modules/dashboards/widgets/budget_manager.php
require_once __DIR__ . '/../../../includes/Database.php';

$db = Database::getInstance();
$budgets = [];

try {
    $budgets = $db->query("SELECT b.*, p.project_name FROM budgets b JOIN projects p ON b.project_id = p.id ORDER BY b.created_at DESC LIMIT 5")->fetchAll();
} catch (Exception $e) { /* Ignore */ }

?>
<div class="widget glass-card">
    <div class="widget-header">
        <h3><i class="fas fa-wallet text-gold"></i> Project Budgets</h3>
        <button class="btn-primary-sm" onclick="location.href='main.php?module=finance/project_budgets'">New Proposal</button>
    </div>
    <div class="widget-content">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Project</th>
                    <th>Category</th>
                    <th>Allocated</th>
                    <th>Remaining</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($budgets)): ?>
                    <tr><td colspan="4" class="text-center text-dim">No budgets assigned.</td></tr>
                <?php else: ?>
                    <?php foreach ($budgets as $b): ?>
                        <tr>
                            <td><?= htmlspecialchars($b['project_name']) ?></td>
                            <td><span class="badge badge-info"><?= $b['category'] ?? 'General' ?></span></td>
                            <td><?= number_format($b['total_amount'], 2) ?></td>
                            <td class="text-success"><?= number_format($b['remaining_amount'] ?? $b['total_amount'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
