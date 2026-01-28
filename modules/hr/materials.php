<?php
// modules/hr/materials.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/HRManager.php';

AuthManager::requireRole('HR_MANAGER');

$requests = HRManager::getMaterialRequestsToReview();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'forward') {
    HRManager::forwardMaterialRequest($_POST['request_id'], $_SESSION['user_id']);
    header("Location: main.php?module=hr/materials&success=validated");
    exit();
}
?>

<div class="materials-validation">
    <div class="section-header mb-4">
        <h2><i class="fas fa-boxes"></i> Site Material Validation</h2>
        <p class="text-dim">Review site requests before forwarding to the Store Manager.</p>
    </div>

    <div class="glass-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Site</th>
                    <th>Requester</th>
                    <th>Item(s)</th>
                    <th>Priority</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($requests)): ?>
                    <tr><td colspan="6" style="text-align:center; padding:3rem;" class="text-dim">No pending site requests for validation.</td></tr>
                <?php else: ?>
                    <?php foreach ($requests as $r): ?>
                        <tr>
                            <td><div style="font-weight:bold;"><?= $r['site_name'] ?></div></td>
                            <td><?= $r['requester'] ?></td>
                            <td><?= $r['item_name'] ?> (<?= $r['quantity'] ?> units)</td>
                            <td><span class="status-badge <?= $r['priority'] ?>"><?= strtoupper($r['priority']) ?></span></td>
                            <td><?= date('M d, H:i', strtotime($r['created_at'])) ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="forward">
                                    <input type="hidden" name="request_id" value="<?= $r['id'] ?>">
                                    <button type="submit" class="btn-primary-sm" style="font-size:0.75rem;"><i class="fas fa-share"></i> Validate & Forward</button>
                                </form>
                                <button class="btn-secondary-sm"><i class="fas fa-times"></i> Reject</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Explanation Box -->
    <div class="alert alert-info mt-4 glass-card" style="border-left: 5px solid var(--gold);">
        <i class="fas fa-info-circle"></i> <strong>Workflow Reminder:</strong> HR's role is to validate the *necessity* and *manpower alignment* of material requests. Actual issuance is handled by the <strong>Store Keeper</strong> after your validation.
    </div>
</div>

<style>
.status-badge.urgent { background: rgba(255, 68, 68, 0.2); color: #ff4444; }
.status-badge.emergency { background: #ff4444; color: white; }
.status-badge.normal { background: rgba(255, 255, 255, 0.1); color: #ccc; }
</style>
