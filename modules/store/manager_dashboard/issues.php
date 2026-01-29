<?php
// modules/store/manager_dashboard/issues.php

$db = Database::getInstance();
$user_id = $_SESSION['user_id'];

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $request_id = $_POST['request_id'];
    $decision = $_POST['action']; // 'approved' or 'rejected'
    $comment = $_POST['comment'] ?? '';

    $stmt = $db->prepare("UPDATE material_requests 
                          SET store_manager_approval = ?, 
                              store_manager_id = ?, 
                              updated_at = NOW() 
                          WHERE id = ?");
    $stmt->execute([$decision, $user_id, $request_id]);
    
    echo "<div class='alert alert-success'>Request #$request_id has been $decision.</div>";
}

// Fetch HR-validated requests
$requests = $db->query("SELECT mr.*, s.site_name, p.product_name, p.unit, sl.quantity as available, sl.store_id
                        FROM material_requests mr
                        JOIN sites s ON mr.site_id = s.id
                        JOIN products p ON mr.item_name = p.product_name 
                        LEFT JOIN stock_levels sl ON p.id = sl.product_id
                        WHERE mr.hr_review_status = 'validated' 
                        AND mr.store_manager_approval = 'pending'
                        ORDER BY mr.created_at DESC")->fetchAll();

?>

<div class="glass-panel">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Material Issue Approval Queue</h4>
        <span class="badge bg-primary px-3 rounded-pill"><?= count($requests) ?> Pending Approvals</span>
    </div>

    <div class="table-responsive">
        <table class="table table-custom text-white">
            <thead class="text-secondary text-xs text-uppercase fw-bold">
                <tr>
                    <th>Ref</th>
                    <th>Requester/Site</th>
                    <th>Material</th>
                    <th>Qty Req</th>
                    <th>Stock Avail</th>
                    <th>Planned Reason</th>
                    <th class="text-end">Verification</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php foreach ($requests as $r): ?>
                <tr>
                    <td class="font-monospace text-secondary">#MR-<?= $r['id'] ?></td>
                    <td>
                        <div class="fw-bold"><?= htmlspecialchars($r['site_name']) ?></div>
                        <div class="text-xs text-info">HR Validated</div>
                    </td>
                    <td>
                        <div class="fw-bold"><?= htmlspecialchars($r['product_name']) ?></div>
                        <div class="text-xs text-secondary">ID: <?= $r['id'] ?></div>
                    </td>
                    <td><span class="h6 mb-0 fw-bold"><?= $r['quantity'] ?></span> <span class="text-secondary"><?= $r['unit'] ?></span></td>
                    <td>
                        <?php 
                        $stockStatusColor = ($r['available'] >= $r['quantity']) ? 'success' : 'danger';
                        $percentage = ($r['available'] > 0) ? min(100, ($r['available'] / $r['quantity']) * 100) : 0;
                        ?>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold text-<?= $stockStatusColor ?>"><?= $r['available'] ?? 0 ?></span>
                            <div class="progress flex-grow-1" style="height: 4px; min-width: 60px; background: rgba(255,255,255,0.05);">
                                <div class="progress-bar bg-<?= $stockStatusColor ?>" style="width: <?= $percentage ?>%"></div>
                            </div>
                        </div>
                    </td>
                    <td style="max-width: 250px;">
                        <span class="text-xs text-secondary italic"><?= htmlspecialchars($r['reason']) ?></span>
                    </td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-2">
                             <button class="btn btn-sm btn-outline-danger btn-icon" data-bs-toggle="modal" data-bs-target="#rejectModal<?= $r['id'] ?>"><i class="fas fa-times"></i></button>
                             <button class="btn btn-sm btn-success px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#approveModal<?= $r['id'] ?>">Approve Issue</button>
                        </div>
                    </td>
                </tr>

                <!-- Approve Modal -->
                <div class="modal fade" id="approveModal<?= $r['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content bg-dark-eval border-success">
                            <form method="POST">
                                <input type="hidden" name="request_id" value="<?= $r['id'] ?>">
                                <input type="hidden" name="action" value="approved">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold">Confirm Issue Authorization</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Are you sure you want to authorize the issuance of <strong><?= $r['quantity'] ?> <?= $r['unit'] ?></strong> for <strong><?= htmlspecialchars($r['site_name']) ?></strong>?</p>
                                    <p class="text-xs text-secondary"><i class="fas fa-info-circle me-1"></i> Once approved, the Store Keeper will be notified to execute the physical delivery.</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success fw-bold">Confirm Approval</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Reject Modal -->
                <div class="modal fade" id="rejectModal<?= $r['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content bg-dark-eval border-danger">
                            <form method="POST">
                                <input type="hidden" name="request_id" value="<?= $r['id'] ?>">
                                <input type="hidden" name="action" value="rejected">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold">Reject Material Request</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Rejection Reason</label>
                                        <textarea class="form-control bg-dark text-white" name="comment" rows="3" required placeholder="Explain why the issue is being blocked..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-danger fw-bold">Confirm Rejection</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <?php endforeach; ?>
                <?php if (empty($requests)): ?>
                    <tr><td colspan="7" class="text-center py-5 text-secondary">No pending material issues requiring approval.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
