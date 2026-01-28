<?php
// modules/dashboards/widgets/expense_ledger.php
require_once __DIR__ . '/../../../includes/Database.php';

$db = Database::getInstance();
$expenses = [];

try {
    // Basic filter simulation for now as widget params handle the query
    $expenses = $db->query("SELECT 
                              e.created_at as date,
                              p.project_name,
                              e.category,
                              e.amount,
                              u.username AS entered_by,
                              e.id as reference_no
                            FROM expenses e
                            JOIN projects p ON p.id = e.project_id
                            JOIN users u ON u.id = e.finance_approved_by
                            ORDER BY e.created_at DESC LIMIT 10")->fetchAll();
} catch (Exception $e) { /* Ignore */ }

?>
<div class="widget glass-card">
    <div class="widget-header d-flex justify-content-between align-items-center">
        <h3><i class="fas fa-list-ul text-gold"></i> Financial Transactions Ledger</h3>
        <div class="header-actions">
            <input type="text" placeholder="Search ref..." class="modern-input-sm" style="width: 120px; font-size: 0.7rem;">
        </div>
    </div>
    <div class="widget-content">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Project</th>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Entered By</th>
                    <th>Ref #</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($expenses)): ?>
                    <tr><td colspan="6" class="text-center text-dim">No transactions recorded.</td></tr>
                <?php else: ?>
                    <?php foreach ($expenses as $e): ?>
                        <tr>
                            <td class="small"><?= date('d M Y', strtotime($e['date'])) ?></td>
                            <td><?= htmlspecialchars($e['project_name']) ?></td>
                            <td><span class="badge badge-secondary"><?= htmlspecialchars($e['category']) ?></span></td>
                            <td class="text-gold"><?= number_format($e['amount'], 2) ?></td>
                            <td class="small"><i class="fas fa-user-shield mr-1"></i><?= htmlspecialchars($e['entered_by']) ?></td>
                            <td class="text-dim">#<?= str_pad($e['reference_no'], 6, '0', STR_PAD_LEFT) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
