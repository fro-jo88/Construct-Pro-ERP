<?php
// modules/dashboards/widgets/inventory_status.php
require_once __DIR__ . '/../../../includes/Database.php';

$db = Database::getInstance();
$status = [];

try {
    $status = $db->query("SELECT s.store_name, COUNT(sl.product_id) as items, SUM(sl.quantity) as total_qty 
                         FROM stores s 
                         LEFT JOIN stock_levels sl ON s.id = sl.store_id 
                         GROUP BY s.id LIMIT 5")->fetchAll();
} catch (Exception $e) { /* Ignore */ }

?>
<div class="widget glass-card">
    <div class="widget-header">
        <h3><i class="fas fa-warehouse"></i> Inventory Heatmap</h3>
    </div>
    <div class="widget-content">
        <?php if (empty($status)): ?>
            <p class="text-dim">No inventory data available.</p>
        <?php else: ?>
            <?php foreach ($status as $s): ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span><?= htmlspecialchars($s['store_name']) ?></span>
                        <span class="text-gold"><?= (int)$s['total_qty'] ?> Units</span>
                    </div>
                    <div class="progress-mini"><div class="progress-bar" style="width: <?= min(100, $s['total_qty']/10) ?>%"></div></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
