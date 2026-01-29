<?php
// modules/store/keeper_dashboard/updates.php

$db = Database::getInstance();
$store_id = $store['id'];

// Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'adjust') {
    $product_id = $_POST['product_id'];
    $change_qty = $_POST['change_qty']; // Positive for add, negative for subtract
    $type = $_POST['movement_type'];
    $reason = $_POST['reason'];

    try {
        $db->beginTransaction();

        // 1. Update/Insert Stock Level
        $stmt = $db->prepare("INSERT INTO stock_levels (store_id, product_id, quantity) 
                              VALUES (?, ?, ?) 
                              ON DUPLICATE KEY UPDATE quantity = quantity + ?");
        $stmt->execute([$store_id, $product_id, $change_qty, $change_qty]);

        // 2. Log Movement
        $stmt = $db->prepare("INSERT INTO inventory_movements (store_id, product_id, quantity, movement_type, performed_by, reason) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$store_id, $product_id, $change_qty, $type, $_SESSION['user_id'], $reason]);

        $db->commit();
        echo "<div class='alert alert-success fw-bold shadow-sm'><i class='fas fa-sync me-2'></i> Stock adjusted and logged.</div>";
    } catch (Exception $e) {
        $db->rollBack();
        echo "<div class='alert alert-danger fw-bold'>Error: " . $e->getMessage() . "</div>";
    }
}

// Fetch current inventory for modal dropdown
$inventory = $db->query("SELECT p.id, p.product_name, p.sku, p.unit, sl.quantity 
                         FROM products p 
                         LEFT JOIN stock_levels sl ON (p.id = sl.product_id AND sl.store_id = ?)
                         ORDER BY p.product_name ASC", [$store_id])->fetchAll();

?>

<div class="row g-4">
    <div class="col-md-5">
        <div class="glass-panel">
            <h4 class="fw-bold mb-4">Stock Adjustment Form</h4>
            <p class="text-sm text-secondary mb-4">Use this to record site returns, breakages, or physical count corrections.</p>
            
            <form method="POST">
                <input type="hidden" name="action" value="adjust">
                
                <div class="mb-3">
                    <label class="form-label text-secondary">Material / Item</label>
                    <select name="product_id" class="form-select bg-dark text-white border-secondary select2" required>
                        <option value="">Select Material...</option>
                        <?php foreach ($inventory as $item): ?>
                            <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['product_name']) ?> (<?= $item['sku'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-secondary">Adjustment Qty</label>
                        <input type="number" step="0.01" name="change_qty" class="form-control bg-dark text-white border-secondary" placeholder="e.g. 10 or -5" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-secondary">Adjustment Type</label>
                        <select name="movement_type" class="form-select bg-dark text-white border-secondary">
                            <option value="return">Return from Site</option>
                            <option value="adjustment">Physical Count Sync</option>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label text-secondary">Detailed Reason</label>
                    <textarea name="reason" class="form-control bg-dark text-white border-secondary" rows="3" required placeholder="Describe why this adjustment is being made..."></textarea>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary fw-bold">Commit Stock Change</button>
                    <span class="text-xs text-secondary mt-2 text-center italic"><i class="fas fa-exclamation-triangle me-1"></i> All manual adjustments are audited by the Store Manager.</span>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-7">
        <div class="glass-panel">
            <h4 class="fw-bold mb-4">Current Stock Snapshot</h4>
            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                <table class="table table-custom text-white">
                    <thead class="text-secondary text-xs text-uppercase sticky-top bg-dark-eval">
                        <tr>
                            <th>Material</th>
                            <th>SKU</th>
                            <th class="text-end">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inventory as $i): ?>
                            <?php if ($i['quantity'] > 0): ?>
                            <tr>
                                <td class="fw-bold"><?= htmlspecialchars($i['product_name']) ?></td>
                                <td class="text-xs text-secondary font-monospace"><?= htmlspecialchars($i['sku']) ?></td>
                                <td class="text-end fw-bold"><?= number_format($i['quantity'], 2) ?> <span class="text-xs text-secondary"><?= $i['unit'] ?></span></td>
                            </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
