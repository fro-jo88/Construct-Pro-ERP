<?php
// modules/hr/leaves.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../managers/LeaveManager.php';

AuthManager::requireRole('HR_MANAGER');

$pending = LeaveManager::getPendingLeaves();
$history = LeaveManager::getLeaveHistory();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $leaveId = $_POST['leave_id'];
    $hrId = $_SESSION['user_id'];

    if ($_POST['action'] === 'approve') {
        LeaveManager::approveLeave($leaveId, $hrId);
    } elseif ($_POST['action'] === 'reject') {
        LeaveManager::rejectLeave($leaveId, $hrId);
    }
    AuthManager::safeRedirect("main.php?module=hr/leaves&success=1");
}
?>

<div class="leaves-module">
    <div class="section-header mb-4">
        <h2><i class="fas fa-calendar-check text-gold"></i> HR: Leave Approvals</h2>
        <p class="text-dim">Enterprise workflow for reviewing and finalising employee leave requests.</p>
    </div>

    <!-- Stats for context -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="glass-card">
                <div style="font-size:0.8rem; color:var(--text-dim); text-transform:uppercase; letter-spacing:1px;">Pending Review</div>
                <div style="font-size:2.5rem; font-weight:bold; color:var(--gold);"><?= count($pending) ?></div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="glass-card" style="display: flex; align-items: center; justify-content: space-around; height: 100%;">
                <div class="text-center">
                    <div style="font-size:0.75rem; color:var(--text-dim);">SYSTEM STATUS</div>
                    <div style="color:#00ff64; font-weight:bold;"><i class="fas fa-check-circle"></i> Operational</div>
                </div>
                <div style="width: 1px; height: 40px; background: rgba(255,255,255,0.1);"></div>
                <div class="text-center">
                    <div style="font-size:0.75rem; color:var(--text-dim);">DATABASE</div>
                    <div style="color:var(--gold); font-weight:bold;"><i class="fas fa-database"></i> leave_requests</div>
                </div>
            </div>
        </div>
    </div>

    <div class="glass-card">
        <h3 class="mb-4"><i class="fas fa-hourglass-half me-2" style="color: var(--gold);"></i>Unprocessed Requests</h3>
        
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Leave Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Reason</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pending)): ?>
                        <tr><td colspan="6" style="text-align:center; padding:5rem;" class="text-dim">
                            <i class="fas fa-check-circle fa-3x mb-3" style="opacity: 0.2; display: block;"></i>
                            All leave requests have been processed.
                        </td></tr>
                    <?php else: ?>
                        <?php foreach ($pending as $l): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 35px; height: 35px; background: var(--gold); color: #000; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.8rem;">
                                            <?= strtoupper(substr($l['username'], 0, 2)) ?>
                                        </div>
                                        <div>
                                            <div style="font-weight:bold; color: #fff;"><?= htmlspecialchars($l['username']) ?></div>
                                            <div style="font-size: 0.7rem; color: var(--text-dim);">UID: #<?= $l['user_id'] ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="status-badge" style="background:rgba(255,255,255,0.05); color: #fff; border: 1px solid rgba(255,255,255,0.1);"><?= strtoupper($l['leave_type']) ?></span></td>
                                <td><?= date('M d, Y', strtotime($l['start_date'])) ?></td>
                                <td><?= date('M d, Y', strtotime($l['end_date'])) ?></td>
                                <td style="max-width:250px;">
                                    <div style="font-size: 0.85rem; color: rgba(255,255,255,0.7); line-height: 1.4; background: rgba(0,0,0,0.2); padding: 8px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.03);">
                                        <?= htmlspecialchars($l['reason']) ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 10px;">
                                        <form method="POST" style="margin: 0;">
                                            <input type="hidden" name="action" value="approve">
                                            <input type="hidden" name="leave_id" value="<?= $l['id'] ?>">
                                            <button type="submit" class="btn-primary-sm" style="background: #00ff64; color: #000; border: none; font-weight: 800;">APPROVE</button>
                                        </form>
                                        <form method="POST" style="margin: 0;">
                                            <input type="hidden" name="action" value="reject">
                                            <input type="hidden" name="leave_id" value="<?= $l['id'] ?>">
                                            <button type="submit" class="btn-primary-sm" style="background: #ff4444; color: #fff; border: none; font-weight: 800;">REJECT</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <div class="glass-card mt-4">
        <h3 class="mb-4"><i class="fas fa-history me-2" style="color: var(--gold);"></i>Processed Leave History</h3>
        
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Decision Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($history)): ?>
                        <tr><td colspan="6" style="text-align:center; padding:3rem;" class="text-dim">No leave history found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($history as $h): ?>
                            <tr>
                                <td><div style="font-weight:bold; color: #fff;"><?= htmlspecialchars($h['username']) ?></div></td>
                                <td><?= htmlspecialchars($h['leave_type']) ?></td>
                                <td><?= date('M d, Y', strtotime($h['start_date'])) ?></td>
                                <td><?= date('M d, Y', strtotime($h['end_date'])) ?></td>
                                <td>
                                    <?php if ($h['status'] === 'approved'): ?>
                                        <span class="status-badge approved">APPROVED</span>
                                    <?php else: ?>
                                        <span class="status-badge rejected" style="background: rgba(255, 68, 68, 0.1); color: #ff4444;">REJECTED</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size: 0.8rem; color: var(--text-dim);">
                                    <?= date('M d, Y H:i', strtotime($h['created_at'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
