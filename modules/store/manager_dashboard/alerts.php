<?php
// modules/store/manager_dashboard/alerts.php

$db = Database::getInstance();

// Fetch items below threshold
$alerts = $db->query("SELECT sl.*, p.product_name, p.sku, p.unit, p.min_threshold, s.store_name 
                      FROM stock_levels sl
                      JOIN products p ON sl.product_id = p.id
                      JOIN stores s ON sl.store_id = s.id
                      WHERE sl.quantity <= p.min_threshold AND p.min_threshold > 0
                      ORDER BY sl.quantity / p.min_threshold ASC")->fetchAll();

?>

<div class="glass-panel border-start border-4 border-danger">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-white">Critical Reorder Alerts</h4>
            <p class="text-sm text-secondary mb-0">The following items have crossed their minimum safety threshold.</p>
        </div>
        <button class="btn btn-danger fw-bold shadow-lg" onclick="window.print()">
            <i class="fas fa-file-pdf me-2"></i> Export Reorder List
        </button>
    </div>

    <div class="row g-4">
        <?php foreach ($alerts as $a): 
            $ratio = ($a['min_threshold'] > 0) ? ($a['quantity'] / $a['min_threshold']) : 0;
            $severity = ($ratio < 0.2) ? 'danger' : 'warning';
        ?>
        <div class="col-md-6 col-lg-4">
            <div class="p-4 rounded-3 border-start border-4 border-<?= $severity ?> bg-dark bg-opacity-20">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="fw-bold mb-0 text-white"><?= htmlspecialchars($a['product_name']) ?></h6>
                        <span class="text-xs text-secondary font-monospace"><?= $a['sku'] ?></span>
                    </div>
                    <span class="badge bg-<?= $severity ?> text-uppercase"><?= $severity ?></span>
                </div>
                
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-xs text-secondary">Store:</span>
                    <span class="text-xs fw-bold text-info"><?= htmlspecialchars($a['store_name']) ?></span>
                </div>

                <div class="d-flex justify-content-between mb-4">
                    <div>
                        <div class="h4 fw-bold mb-0"><?= number_format($a['quantity'], 2) ?></div>
                        <div class="text-xs text-secondary">Actual Stock</div>
                    </div>
                    <div class="text-end">
                        <div class="h4 fw-bold mb-0 text-secondary"><?= $a['min_threshold'] ?></div>
                        <div class="text-xs text-secondary">Threshold</div>
                    </div>
                </div>

                <div class="progress" style="height: 6px; background: rgba(0,0,0,0.3);">
                    <div class="progress-bar bg-<?= $severity ?>" style="width: <?= min(100, $ratio * 100) ?>%"></div>
                </div>
                
                <div class="mt-4 d-grid">
                    <button class="btn btn-xs btn-outline-secondary" onclick="alert('Notification sent to Purchase Manager for <?= htmlspecialchars($a['product_name']) ?>')">
                        <i class="fas fa-bell me-1"></i> Notify Purchase Manager
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php if (empty($alerts)): ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-shield-check fa-4x text-success mb-3 opacity-20"></i>
                <h4 class="text-secondary">System Secured</h4>
                <p class="text-muted">No inventory items are currently below reorder levels.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
