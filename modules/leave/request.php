<?php
// modules/leave/request.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../managers/LeaveManager.php';

if (!AuthManager::isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$userId = $_SESSION['user_id'];
$successMsg = '';
$errorMsg = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request_leave') {
    $data = [
        'leave_type' => $_POST['leave_type'],
        'start_date' => $_POST['start_date'],
        'end_date'   => $_POST['end_date'],
        'reason'     => $_POST['reason']
    ];

    if (LeaveManager::createLeaveRequest($userId, $data)) {
        $successMsg = "Leave request submitted successfully!";
    } else {
        $errorMsg = "Failed to submit leave request. Please try again.";
    }
}

$myLeaves = LeaveManager::getUserLeaves($userId);
?>

<div class="leave-module">
    <div class="section-header mb-4">
        <h2><i class="fas fa-calendar-alt text-gold"></i> Leave Management</h2>
        <p class="text-dim">Apply for leave and track your request status.</p>
    </div>

    <div class="row">
        <!-- Leave Request Form - FULL WIDTH -->
        <div class="col-12 mb-4">
            <div class="glass-card">
                <h3 class="card-title mb-4"><i class="fas fa-plus-circle me-2"></i>New Leave Request</h3>
                
                <?php if ($successMsg): ?>
                    <div class="alert alert-success" style="background: rgba(0,255,100,0.1); color: #00ff64; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                        <?= $successMsg ?>
                    </div>
                <?php endif; ?>

                <?php if ($errorMsg): ?>
                    <div class="alert alert-danger" style="background: rgba(255,68,68,0.1); color: #ff4444; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                        <?= $errorMsg ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="action" value="request_leave">
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label text-dim">Leave Type</label>
                                <select name="leave_type" class="form-control" required style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 12px; height: auto;">
                                    <option value="Annual Leave">Annual Leave</option>
                                    <option value="Sick Leave">Sick Leave</option>
                                    <option value="Maternity/Paternity">Maternity/Paternity</option>
                                    <option value="Unpaid Leave">Unpaid Leave</option>
                                    <option value="Emergency Leave">Emergency Leave</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label text-dim">Start Date</label>
                                <input type="date" name="start_date" class="form-control" required style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 12px; height: auto;">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label text-dim">End Date</label>
                                <input type="date" name="end_date" class="form-control" required style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 12px; height: auto;">
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label text-dim">Reason / Remarks</label>
                        <textarea name="reason" rows="6" class="form-control" placeholder="Provide more details..." style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 15px; width: 100%;"></textarea>
                    </div>

                    <div style="display: flex; justify-content: flex-end;">
                        <button type="submit" class="btn-primary-sm py-3 px-5" style="font-size: 1.1rem; min-width: 300px;">
                            <i class="fas fa-paper-plane me-2"></i> Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Status View - FULL WIDTH BELOW -->
        <div class="col-12">
            <div class="glass-card">
                <h3 class="card-title mb-4"><i class="fas fa-history me-2"></i>My Leave History</h3>
                
                <div class="table-responsive custom-scrollbar">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Requested At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($myLeaves)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-dim py-4">No leave requests found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($myLeaves as $leave): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($leave['leave_type']) ?></strong></td>
                                        <td style="font-size: 0.85rem;">
                                            <?= date('M d', strtotime($leave['start_date'])) ?> - <?= date('M d, Y', strtotime($leave['end_date'])) ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $statusClass = 'pending';
                                            $statusText = 'PENDING HR';
                                            if ($leave['status'] === 'approved') {
                                                $statusClass = 'active';
                                                $statusText = 'APPROVED';
                                            } elseif ($leave['status'] === 'rejected') {
                                                $statusClass = 'suspended';
                                                $statusText = 'REJECTED';
                                            }
                                            ?>
                                            <span class="status-badge <?= $statusClass ?>"><?= $statusText ?></span>
                                        </td>
                                        <td style="font-size: 0.75rem; color: var(--text-dim);">
                                            <?= date('Y-m-d', strtotime($leave['created_at'])) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
