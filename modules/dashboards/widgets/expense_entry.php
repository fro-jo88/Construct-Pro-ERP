<?php
// modules/dashboards/widgets/expense_entry.php
require_once __DIR__ . '/../../../includes/Database.php';

$db = Database::getInstance();
$recent_expenses = [];

try {
    $recent_expenses = $db->query("SELECT e.*, p.project_name FROM expenses e JOIN projects p ON e.project_id = p.id ORDER BY e.created_at DESC LIMIT 5")->fetchAll();
} catch (Exception $e) { /* Ignore */ }

?>
<div class="widget glass-card">
    <div class="widget-header">
        <h3><i class="fas fa-receipt text-gold"></i> Quick Expense Entry</h3>
        <button class="btn-primary-sm" onclick="location.href='main.php?module=finance/expense_tracking'">Add Full</button>
    </div>
    <div class="widget-content">
        <form action="api/finance/add_expense.php" method="POST" class="mb-3">
             <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                <input type="text" name="amount" placeholder="Amount" class="modern-input-sm" required>
                <select name="project_id" class="modern-input-sm" required>
                    <option value="">Select Project</option>
                    <!-- Projects list -->
                </select>
             </div>
             <input type="text" name="description" placeholder="Description" class="modern-input-sm w-100 mt-2" required>
             <button type="submit" class="btn-secondary-sm w-100 mt-2">Record Expense</button>
        </form>

        <h4 class="small text-dim mt-4 mb-2">Recent Records</h4>
        <div class="list-group">
            <?php foreach ($recent_expenses as $exp): ?>
                <div class="list-item d-flex justify-content-between small border-bottom border-light-subtle pb-1 mb-1">
                    <span><?= htmlspecialchars($exp['description']) ?></span>
                    <span class="text-gold">ETB <?= number_format($exp['amount'], 0) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
.modern-input-sm {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 0.8rem;
}
</style>
