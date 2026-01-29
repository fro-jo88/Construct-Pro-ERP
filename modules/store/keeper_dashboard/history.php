<?php
// modules/store/keeper_dashboard/history.php

$db = Database::getInstance();
$store_id = $store['id'];

// Log levels
$movements = $db->query("SELECT im.*, p.product_name, p.sku, u.username as keeper_name
                         FROM inventory_movements im
                         JOIN products p ON im.product_id = p.id
                         JOIN users u ON im.performed_by = u.id
                         WHERE im.store_id = ?
                         ORDER BY im.created_at DESC", [$store_id])->fetchAll();
?>

<div class="glass-panel">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Operational Stock Ledger</h4>
        <button class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="fas fa-print me-1"></i> Print Log</button>
    </div>

    <div class="table-responsive">
        <table class="table table-custom text-white">
            <thead class="text-secondary text-xs text-uppercase fw-bold">
                <tr>
                    <th>Timestamp</th>
                    <th>Type</th>
                    <th>Material</th>
                    <th>Quantity</th>
                    <th>Reference</th>
                    <th>Performed By</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php foreach ($movements as $m): ?>
                <tr>
                    <td class="text-secondary font-monospace"><?= date('M d, H:i', strtotime($m['created_at'])) ?></td>
                    <td>
                        <?php
                        $badgeClass = 'bg-dark';
                        if ($m['movement_type'] == 'issue') $badgeClass = 'bg-warning text-dark';
                        if ($m['movement_type'] == 'return') $badgeClass = 'bg-success';
                        if ($m['movement_type'] == 'adjustment') $badgeClass = 'bg-info text-dark';
                        if (str_contains($m['movement_type'], 'transfer')) $badgeClass = 'bg-primary';
                        ?>
                        <span class="badge <?= $badgeClass ?>"><?= strtoupper(str_replace('_', ' ', $m['movement_type'])) ?></span>
                    </td>
                    <td>
                        <div class="fw-bold"><?= htmlspecialchars($m['product_name']) ?></div>
                        <div class="text-xs text-secondary"><?= $m['sku'] ?></div>
                    </td>
                    <td class="fw-bold <?= $m['quantity'] < 0 ? 'text-danger' : 'text-success' ?>">
                        <?= $m['quantity'] ?>
                    </td>
                    <td class="text-xs">
                        <?php if ($m['reference_id']): ?>
                            <span class="text-secondary">Ref: #<?= $m['reference_id'] ?></span>
                        <?php endif; ?>
                        <div class="text-secondary italic"><?= htmlspecialchars($m['reason'] ?: '--') ?></div>
                    </td>
                    <td>
                        <span class="text-xs opacity-70"><?= htmlspecialchars($m['keeper_name']) ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($movements)): ?>
                    <tr><td colspan="6" class="text-center py-5 text-secondary">No stock movements recorded for this store.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
