<?php
// modules/store/keeper_dashboard/issues.php

$db = Database::getInstance();
$store_id = $store['id'];

// Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'issue') {
    $request_id = $_POST['request_id'];
    $product_id = $_POST['product_id'];
    $issue_qty = $_POST['issue_qty'];
    $notes = $_POST['notes'] ?? '';

    try {
        $db->beginTransaction();

        // 1. Deduct Stock Level
        $stmt = $db->prepare("UPDATE stock_levels SET quantity = quantity - ? WHERE store_id = ? AND product_id = ?");
        $stmt->execute([$issue_qty, $store_id, $product_id]);

        // 2. Log Movement
        $stmt = $db->prepare("INSERT INTO inventory_movements (store_id, product_id, quantity, movement_type, reference_id, performed_by, reason) 
                              VALUES (?, ?, ?, 'issue', ?, ?, ?)");
        $stmt->execute([$store_id, $product_id, -$issue_qty, $request_id, $_SESSION['user_id'], $notes]);

        // 3. Update Request Status
        $stmt = $db->prepare("UPDATE material_requests SET status = 'issued', updated_at = NOW() WHERE id = ?");
        $stmt->execute([$request_id]);

        $db->commit();
        echo "<div class='alert alert-success fw-bold shadow-sm'><i class='fas fa-check-circle me-2'></i> Materials issued successfully. Stock updated.</div>";
    } catch (Exception $e) {
        $db->rollBack();
        echo "<div class='alert alert-danger fw-bold'>Error: " . $e->getMessage() . "</div>";
    }
}

// Fetch Approved but not Issued requests for THIS store
$approvedRequests = $db->query("SELECT mr.*, s.site_name, p.product_name, p.unit, p.id as product_id, sl.quantity as stock_on_hand
                                FROM material_requests mr
                                JOIN sites s ON mr.site_id = s.id
                                JOIN products p ON mr.item_name = p.product_name
                                LEFT JOIN stock_levels sl ON (p.id = sl.product_id AND sl.store_id = ?)
                                WHERE mr.store_manager_approval = 'approved' 
                                AND mr.fulfilling_store_id = ?
                                AND mr.status != 'issued'
                                ORDER BY mr.updated_at DESC", [$store_id, $store_id])->fetchAll();

?>

<div class="glass-panel">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Approved Release Orders</h4>
        <span class="text-secondary text-sm">Execution Layer: Authorized by Store Manager</span>
    </div>

    <div class="table-responsive">
        <table class="table table-custom text-white">
            <thead class="text-secondary text-xs text-uppercase fw-bold">
                <tr>
                    <th>Ref</th>
                    <th>Destination Site</th>
                    <th>Material</th>
                    <th>Auth Qty</th>
                    <th>In Store</th>
                    <th class="text-end">Execution</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php foreach ($approvedRequests as $req): ?>
                <tr>
                    <td class="font-monospace text-secondary">#ORD-<?= $req['id'] ?></td>
                    <td>
                        <div class="fw-bold"><?= htmlspecialchars($req['site_name']) ?></div>
                        <div class="text-xs text-info">Authorized by Manager</div>
                    </td>
                    <td><?= htmlspecialchars($req['product_name']) ?></td>
                    <td class="fw-bold h5 mb-0"><?= $req['quantity'] ?> <span class="text-xs text-secondary fw-normal"><?= $req['unit'] ?></span></td>
                    <td>
                        <span class="badge bg-dark border <?= ($req['stock_on_hand'] >= $req['quantity']) ? 'border-success text-success' : 'border-danger text-danger' ?>">
                            <?= number_format($req['stock_on_hand'], 2) ?> Available
                        </span>
                    </td>
                    <td class="text-end">
                        <button class="btn btn-warning btn-sm fw-bold text-dark px-3 rounded-pill" 
                                data-bs-toggle="modal" 
                                data-bs-target="#issueModal<?= $req['id'] ?>"
                                <?= ($req['stock_on_hand'] < $req['quantity']) ? 'disabled' : '' ?>>
                            <i class="fas fa-sign-out-alt me-1"></i> Confirm Issue
                        </button>
                    </td>
                </tr>

                <!-- Issue Modal -->
                <div class="modal fade" id="issueModal<?= $req['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content bg-dark-eval border-warning">
                            <form method="POST">
                                <input type="hidden" name="action" value="issue">
                                <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                <input type="hidden" name="product_id" value="<?= $req['product_id'] ?>">
                                
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold">Execute Material Release</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="bg-dark p-3 rounded-3 mb-4">
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="text-xs text-secondary text-uppercase">Material</label>
                                                <div class="fw-bold"><?= htmlspecialchars($req['product_name']) ?></div>
                                            </div>
                                            <div class="col-6">
                                                <label class="text-xs text-secondary text-uppercase">Approved Qty</label>
                                                <div class="fw-bold text-warning"><?= $req['quantity'] ?> <?= $req['unit'] ?></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label text-secondary">Physical Issue Quantity</label>
                                        <input type="number" step="0.01" class="form-control bg-dark text-white border-secondary" 
                                               name="issue_qty" value="<?= $req['quantity'] ?>" max="<?= $req['quantity'] ?>" required>
                                        <div class="form-text text-secondary">Cannot exceed approved quantity.</div>
                                    </div>

                                    <div class="mb-0">
                                        <label class="form-label text-secondary">Issue Notes / Reference</label>
                                        <textarea class="form-control bg-dark text-white border-secondary" name="notes" rows="3" placeholder="Driver name, vehicle #, or condition..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-warning fw-bold text-dark">Confirm Dispatch</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($approvedRequests)): ?>
                    <tr><td colspan="6" class="text-center py-5 text-secondary italic">No approved orders pending for physical release.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
