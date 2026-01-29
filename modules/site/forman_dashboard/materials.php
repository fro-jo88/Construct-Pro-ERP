<?php
// modules/site/forman_dashboard/materials.php

$db = Database::getInstance();
$user_id = $_SESSION['user_id'];
$site_id = $site['id'];
$action = $_GET['action'] ?? 'list';

if ($action === 'request') {
    ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">Create Material Request</h3>
        <a href="?module=site/forman_dashboard/index&view=materials" class="btn btn-outline-secondary btn-sm">Cancel</a>
    </div>

    <div class="glass-panel p-4" style="max-width: 600px; margin: 0 auto;">
        <form method="POST" action="modules/site/forman_dashboard/save_request.php">
            <input type="hidden" name="site_id" value="<?= $site_id ?>">
            
            <div class="mb-3">
                <label class="form-label text-secondary">Material Name / Description</label>
                <input type="text" class="form-control bg-dark text-white border-secondary" name="item_name" placeholder="e.g. Portland Cement Grade 42.5" required>
            </div>
            
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label text-secondary">Quantity</label>
                    <input type="number" step="0.01" class="form-control bg-dark text-white border-secondary" name="quantity" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-secondary">Required Date</label>
                    <input type="date" class="form-control bg-dark text-white border-secondary" name="required_date" value="<?= date('Y-m-d', strtotime('+3 days')) ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label text-secondary">Priority</label>
                <select class="form-select bg-dark text-white border-secondary" name="priority">
                    <option value="normal">Normal</option>
                    <option value="urgent">Urgent</option>
                    <option value="emergency">Emergency</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label text-secondary">Reason / Matching Weekly Plan Activity</label>
                <textarea class="form-control bg-dark text-white border-secondary" name="reason" rows="3" placeholder="Explain why this is needed for next week's activities..." required></textarea>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-warning fw-bold text-dark">Submit Request</button>
            </div>
        </form>
    </div>
<?php
} else {
    // List View
    $requests = $db->query("SELECT * FROM material_requests WHERE site_id = ? ORDER BY created_at DESC", [$site_id])->fetchAll();
?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">Material Supply Tracking</h3>
        <a href="?module=site/forman_dashboard/index&view=materials&action=request" class="btn btn-warning fw-bold text-dark">
            <i class="fas fa-plus me-1"></i> New Request
        </a>
    </div>

    <div class="glass-panel">
        <table class="table table-hover align-middle custom-dark-table">
            <thead class="text-secondary text-xs">
                <tr>
                    <th>ITEM</th>
                    <th>QTY</th>
                    <th>REQ DATE</th>
                    <th>STAGE / STATUS</th>
                    <th class="text-end">PRIORITY</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $mr): ?>
                <tr>
                    <td><div class="fw-bold text-white"><?= htmlspecialchars($mr['item_name']) ?></div></td>
                    <td><?= $mr['quantity'] ?></td>
                    <td><?= date('M d', strtotime($mr['created_at'])) ?></td>
                    <td>
                        <?php
                        // Map status to readable stage
                        $statusClass = 'status-draft';
                        $statusLabel = $mr['status'];
                        
                        if ($mr['status'] == 'pending') { $statusLabel = 'Awaiting Planning'; $statusClass = 'status-draft'; }
                        if ($mr['status'] == 'approved') { $statusLabel = 'HR Approved'; $statusClass = 'status-approved'; }
                        if ($mr['status'] == 'ordered') { $statusLabel = 'Procurement'; $statusClass = 'status-submitted'; }
                        if ($mr['status'] == 'delivered') { $statusLabel = 'At Store Site'; $statusClass = 'status-approved'; }
                        ?>
                        <span class="status-badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                    </td>
                    <td class="text-end">
                        <span class="badge <?= $mr['priority'] == 'urgent' ? 'bg-danger' : 'bg-dark' ?> rounded-pill px-3">
                            <?= strtoupper($mr['priority']) ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($requests)): ?>
                    <tr><td colspan="5" class="text-center py-5 text-secondary">No current supply requests.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php } ?>
