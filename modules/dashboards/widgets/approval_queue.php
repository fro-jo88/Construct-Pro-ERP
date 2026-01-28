<?php
// modules/dashboards/widgets/approval_queue.php
require_once __DIR__ . '/../../../includes/Database.php';

$db = Database::getInstance();
$module = $config['module'] ?? 'ALL';
$items = [];

try {
    if ($module === 'HR' || $module === 'ALL') {
        $hr = $db->query("SELECT 'HR' as mod_type, id, full_name as info, 'Employee Registration' as type FROM employees WHERE gm_approval_status = 'pending'")->fetchAll();
        $items = array_merge($items, $hr);
    }
    if ($module === 'FINANCE' || $module === 'ALL') {
        $fin = $db->query("SELECT 'FINANCE' as mod_type, id, total_amount as info, 'Budget Approval' as type FROM budgets WHERE status = 'pending'")->fetchAll();
        foreach($fin as &$f) $f['info'] = "$".number_format($f['info'], 2);
        $items = array_merge($items, $fin);
    }
    // Bids
    if ($module === 'BIDS' || $module === 'ALL') {
        $bids = $db->query("SELECT 'BIDS' as mod_type, id, title as info, 'Bid Decision' as type FROM bids WHERE status IN ('FINANCIAL_COMPLETED', 'FINANCE_FINAL_REVIEW')")->fetchAll();
        $items = array_merge($items, $bids);
    }
} catch (Exception $e) { /* Table missing? */ }

?>
<div class="widget glass-card">
    <div class="widget-header">
        <h3><i class="fas fa-stamp"></i> Pending Approvals (<?= count($items) ?>)</h3>
        <a href="main.php?module=gm/approvals" class="btn-primary-sm">Open Queue</a>
    </div>
    <div class="widget-content">
        <?php if (empty($items)): ?>
            <p class="text-dim">Queue is empty. Everything is approved.</p>
        <?php else: ?>
            <div class="list-group">
                <?php foreach (array_slice($items, 0, 5) as $item): ?>
                    <div class="list-item d-flex justify-content-between align-items-center mb-2" style="border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 0.5rem;">
                        <div>
                            <span class="badge badge-gold" style="font-size: 0.6rem;"><?= $item['mod_type'] ?></span>
                            <span class="ml-2"><?= htmlspecialchars($item['info']) ?></span>
                            <br><small class="text-dim"><?= $item['type'] ?></small>
                        </div>
                        <button class="btn-secondary-sm" onclick="location.href='main.php?module=gm/approvals'">Review</button>
                    </div>
                <?php endforeach; ?>
                <?php if (count($items) > 5): ?>
                    <p class="text-center mt-2"><small class="text-dim">+ <?= count($items)-5 ?> more pending</small></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
