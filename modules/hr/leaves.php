<?php
// modules/hr/leaves.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/HRManager.php';

AuthManager::requireRole(['HR_MANAGER', 'GM']);

$pending = HRManager::getPendingLeaveRequests();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'approve') {
        HRManager::approveLeave($_POST['leave_id'], $_SESSION['user_id']);
    }
    header("Location: main.php?module=hr/leaves&success=1");
    exit();
}
?>

<div class="leaves-module">
    <div class="section-header mb-4">
        <h2><i class="fas fa-calendar-minus"></i> Leave Management</h2>
        <p class="text-dim">Review and approve employee leave requests.</p>
    </div>

    <!-- Stats for context -->
    <div class="row row-cols-1 row-cols-md-3 g-4 mb-4" style="display:flex; gap:1.5rem;">
        <div class="glass-card" style="flex:1;">
            <div style="font-size:0.8rem; color:var(--text-dim);">Pending Review</div>
            <div style="font-size:2rem; font-weight:bold;"><?= count($pending) ?></div>
        </div>
        <div class="glass-card" style="flex:1;">
            <div style="font-size:0.8rem; color:var(--text-dim);">Currently on Leave</div>
            <div style="font-size:2rem; font-weight:bold;">12</div>
        </div>
        <div class="glass-card" style="flex:1;">
            <div style="font-size:0.8rem; color:var(--text-dim);">Upcoming Returns (7d)</div>
            <div style="font-size:2rem; font-weight:bold;">5</div>
        </div>
    </div>

    <div class="glass-card">
        <h3>Pending Requests</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Type</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pending)): ?>
                    <tr><td colspan="7" style="text-align:center; padding:3rem;" class="text-dim">No pending leave requests.</td></tr>
                <?php else: ?>
                    <?php foreach ($pending as $l): ?>
                        <tr>
                            <td><div style="font-weight:bold;"><?= $l['first_name'] . ' ' . $l['last_name'] ?></div></td>
                            <td><span class="status-badge" style="background:rgba(255,255,255,0.1);"><?= strtoupper($l['leave_type']) ?></span></td>
                            <td><?= $l['start_date'] ?></td>
                            <td><?= $l['end_date'] ?></td>
                            <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?= htmlspecialchars($l['reason']) ?></td>
                            <td><span class="status-badge pending">PENDING</span></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="approve">
                                    <input type="hidden" name="leave_id" value="<?= $l['id'] ?>">
                                    <button type="submit" class="btn-primary-sm" style="font-size:0.75rem;">Approve</button>
                                </form>
                                <button class="btn-secondary-sm">Reject</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
