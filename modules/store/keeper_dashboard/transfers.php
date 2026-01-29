<?php
// modules/store/keeper_dashboard/transfers.php

$db = Database::getInstance();
$store_id = $store['id'];

// Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $transfer_id = $_POST['transfer_id'];
    $new_status = $_POST['action']; // 'in_transit' for dispatch, 'completed' for receive

    try {
        $db->beginTransaction();

        $stmt = $db->prepare("UPDATE stock_transfers SET transfer_status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$new_status, $transfer_id]);

        // If completed (Received), increment stock in this store
        if ($new_status === 'completed') {
            $tData = $db->query("SELECT * FROM stock_transfers WHERE id = ?", [$transfer_id])->fetch();
            
            // 1. Add Stock to Incoming Store
            $stmt = $db->prepare("INSERT INTO stock_levels (store_id, product_id, quantity) 
                                  VALUES (?, ?, ?) 
                                  ON DUPLICATE KEY UPDATE quantity = quantity + ?");
            $stmt->execute([$tData['to_store_id'], $tData['material_id'], $tData['quantity'], $tData['quantity']]);

            // 2. Log Movement (In)
            $stmt = $db->prepare("INSERT INTO inventory_movements (store_id, product_id, quantity, movement_type, reference_id, performed_by, reason) 
                                  VALUES (?, ?, ?, 'transfer_in', ?, ?, 'Transfer Received')");
            $stmt->execute([$tData['to_store_id'], $tData['material_id'], $tData['quantity'], $transfer_id, $_SESSION['user_id']]);
        }
        
        // If in_transit (Dispatched), decrement stock from this store
        if ($new_status === 'in_transit') {
             $tData = $db->query("SELECT * FROM stock_transfers WHERE id = ?", [$transfer_id])->fetch();
             
             // 1. Remove Stock from Outgoing Store
             $stmt = $db->prepare("UPDATE stock_levels SET quantity = quantity - ? WHERE store_id = ? AND product_id = ?");
             $stmt->execute([$tData['quantity'], $tData['from_store_id'], $tData['material_id']]);

             // 2. Log Movement (Out)
             $stmt = $db->prepare("INSERT INTO inventory_movements (store_id, product_id, quantity, movement_type, reference_id, performed_by, reason) 
                                   VALUES (?, ?, ?, 'transfer_out', ?, ?, 'Transfer Dispatched')");
             $stmt->execute([$tData['from_store_id'], $tData['material_id'], -$tData['quantity'], $transfer_id, $_SESSION['user_id']]);
        }

        $db->commit();
        echo "<div class='alert alert-info fw-bold'><i class='fas fa-info-circle me-1'></i> Transfer status updated to: " . strtoupper($new_status) . "</div>";
    } catch (Exception $e) {
        $db->rollBack();
        echo "<div class='alert alert-danger fw-bold'>Error: " . $e->getMessage() . "</div>";
    }
}

// Fetch transfers involving this store
$transfers = $db->query("SELECT st.*, p.product_name, p.unit, s1.store_name as from_store, s2.store_name as to_store 
                         FROM stock_transfers st
                         JOIN products p ON st.material_id = p.id
                         JOIN stores s1 ON st.from_store_id = s1.id
                         JOIN stores s2 ON st.to_store_id = s2.id
                         WHERE (st.from_store_id = ? OR st.to_store_id = ?)
                         AND st.manager_approval = 'approved'
                         AND st.transfer_status != 'cancelled'
                         AND st.transfer_status != 'completed'
                         ORDER BY st.created_at DESC", [$store_id, $store_id])->fetchAll();

?>

<div class="glass-panel">
    <h4 class="fw-bold mb-4">Inter-Store Movements</h4>
    
    <div class="table-responsive">
        <table class="table table-custom text-white">
            <thead class="text-secondary text-xs text-uppercase fw-bold">
                <tr>
                    <th>Transfer #</th>
                    <th>Type</th>
                    <th>Origin / Destination</th>
                    <th>Material</th>
                    <th>Quantity</th>
                    <th class="text-end">Execution</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php foreach ($transfers as $t): 
                    $isOutgoing = ($t['from_store_id'] == $store_id);
                ?>
                <tr>
                    <td class="font-monospace text-secondary">#TRN-<?= $t['id'] ?></td>
                    <td>
                        <span class="badge border <?= $isOutgoing ? 'border-danger text-danger' : 'border-success text-success' ?>">
                            <?= $isOutgoing ? 'OUTGOING' : 'INCOMING' ?>
                        </span>
                    </td>
                    <td>
                        <div class="text-xs text-secondary"><?= $isOutgoing ? 'To:' : 'From:' ?></div>
                        <div class="fw-bold"><?= $isOutgoing ? htmlspecialchars($t['to_store']) : htmlspecialchars($t['from_store']) ?></div>
                    </td>
                    <td><?= htmlspecialchars($t['product_name']) ?></td>
                    <td><span class="fw-bold"><?= $t['quantity'] ?></span> <?= $t['unit'] ?></td>
                    <td class="text-end">
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="transfer_id" value="<?= $t['id'] ?>">
                            <?php if ($isOutgoing && $t['transfer_status'] === 'pending'): ?>
                                <button type="submit" name="action" value="in_transit" class="btn btn-primary btn-sm fw-bold">Dispatch Now</button>
                            <?php elseif (!$isOutgoing && $t['transfer_status'] === 'in_transit'): ?>
                                <button type="submit" name="action" value="completed" class="btn btn-success btn-sm fw-bold">Confirm Receipt</button>
                            <?php else: ?>
                                <span class="badge bg-dark border border-secondary"><?= strtoupper($t['transfer_status']) ?></span>
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($transfers)): ?>
                    <tr><td colspan="6" class="text-center py-5 text-secondary">No active transfers requiring execution.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
