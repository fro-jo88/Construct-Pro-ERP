<?php
// modules/store/manager_dashboard/transfers.php

$db = Database::getInstance();
$user_id = $_SESSION['user_id'];

// Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $transfer_id = $_POST['transfer_id'];
    $decision = $_POST['action']; // 'approved' or 'rejected'
    
    $stmt = $db->prepare("UPDATE stock_transfers 
                          SET manager_approval = ?, 
                              approved_by = ?, 
                              transfer_status = ? 
                          WHERE id = ?");
    
    $status = ($decision === 'approved') ? 'in_transit' : 'cancelled';
    $stmt->execute([$decision, $user_id, $status, $transfer_id]);
    
    echo "<div class='alert alert-primary'>Transfer #$transfer_id marked as $decision.</div>";
}

// Fetch Pending Transfers
$transfers = $db->query("SELECT st.*, p.product_name, p.unit, 
                                s1.store_name as from_store, s2.store_name as to_store,
                                u.username as requested_by_user
                         FROM stock_transfers st
                         JOIN products p ON st.material_id = p.id
                         JOIN stores s1 ON st.from_store_id = s1.id
                         JOIN stores s2 ON st.to_store_id = s2.id
                         JOIN users u ON st.requested_by = u.id
                         WHERE st.manager_approval = 'pending'
                         ORDER BY st.created_at DESC")->fetchAll();

?>

<div class="glass-panel">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Inter-Store Stock Transfers</h4>
        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#newTransferModal">Initiate Manual Transfer</button>
    </div>

    <div class="table-responsive">
        <table class="table table-custom text-white">
            <thead class="text-secondary text-xs text-uppercase fw-bold">
                <tr>
                    <th>Ref</th>
                    <th>From Store</th>
                    <th>→ To Store</th>
                    <th>Material</th>
                    <th>Quantity</th>
                    <th>Requestor</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php foreach ($transfers as $t): ?>
                <tr>
                    <td class="font-monospace text-secondary">#TRN-<?= $t['id'] ?></td>
                    <td><span class="badge bg-dark border border-secondary"><?= htmlspecialchars($t['from_store']) ?></span></td>
                    <td><span class="badge bg-primary"><?= htmlspecialchars($t['to_store']) ?></span></td>
                    <td><div class="fw-bold"><?= htmlspecialchars($t['product_name']) ?></div></td>
                    <td><span class="h6 mb-0 fw-bold"><?= $t['quantity'] ?></span> <span class="text-secondary"><?= $t['unit'] ?></span></td>
                    <td>
                        <div class="text-xs text-white"><?= htmlspecialchars($t['requested_by_user']) ?></div>
                        <div class="text-xs text-secondary">Keeper</div>
                    </td>
                    <td class="text-end">
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="transfer_id" value="<?= $t['id'] ?>">
                            <button type="submit" name="action" value="rejected" class="btn btn-sm btn-icon border-danger text-danger"><i class="fas fa-times"></i></button>
                            <button type="submit" name="action" value="approved" class="btn btn-sm btn-primary px-3 fw-bold">Approve</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($transfers)): ?>
                    <tr><td colspan="7" class="text-center py-5 text-secondary">No pending inter-store transfer requests found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
