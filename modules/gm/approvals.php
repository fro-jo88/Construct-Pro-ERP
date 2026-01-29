<?php
// modules/gm/approvals.php - Unified Approval Center
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/GMManager.php';
require_once __DIR__ . '/../../includes/Database.php';

AuthManager::requireRole('GM');

$db = Database::getInstance();
$user_id = $_SESSION['user_id'];

// Handle approval/rejection actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        $module = $_POST['module'];
        $ref_id = $_POST['ref_id'];
        $decision = $_POST['decision'];
        $reason = $_POST['reason'] ?? '';
        
        GMManager::processApproval($module, $ref_id, $decision, $reason, $user_id);
        $msg = ucfirst($decision) . " successfully processed.";
        $msg_type = 'success';
    } catch (Exception $e) {
        $msg = "Error: " . $e->getMessage();
        $msg_type = 'danger';
    }
}

// Get all pending approvals
$pending_approvals = GMManager::getPendingApprovals();

// Get overview stats
$hr_overview = GMManager::getHROverview();
$finance_overview = GMManager::getFinanceOverview();
$planning_overview = GMManager::getPlanningOverview();

?>

<style>
    .approval-card { 
        background: rgba(30, 41, 59, 0.4); 
        border: 1px solid rgba(255,255,255,0.08); 
        border-radius: 16px; 
        padding: 20px; 
        margin-bottom: 15px;
        transition: 0.3s;
    }
    .approval-card:hover { 
        border-color: var(--gold); 
        box-shadow: 0 8px 16px rgba(0,0,0,0.2); 
    }
    .module-badge {
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .badge-hr { background: rgba(59, 130, 246, 0.1); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.2); }
    .badge-finance { background: rgba(34, 197, 94, 0.1); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.2); }
    .badge-bids { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.2); }
    .badge-procurement { background: rgba(168, 85, 247, 0.1); color: #a855f7; border: 1px solid rgba(168, 85, 247, 0.2); }
    .badge-planning { background: rgba(236, 72, 153, 0.1); color: #ec4899; border: 1px solid rgba(236, 72, 153, 0.2); }
</style>

<div class="gm-approvals">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <span class="badge bg-warning mb-2 px-3 fw-bold">EXECUTIVE APPROVAL CENTER</span>
            <h1 class="fw-extrabold mb-1" style="font-size: 2.5rem; letter-spacing: -1.5px;">Pending Approvals</h1>
            <p class="text-secondary mb-0 fw-medium">Centralized oversight of all cross-departmental approval requests.</p>
        </div>
        <div class="text-end">
            <div class="h2 fw-bold mb-0 text-white"><?= count($pending_approvals) ?></div>
            <div class="text-secondary text-sm">Total Pending</div>
        </div>
    </div>

    <?php if (isset($msg)): ?>
        <div class="alert alert-<?= $msg_type ?> border-0 mb-4">
            <i class="fas fa-<?= $msg_type === 'success' ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i> <?= $msg ?>
        </div>
    <?php endif; ?>

    <!-- OVERVIEW CARDS -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="glass-card p-3">
                <div class="text-xs text-secondary mb-1">HR PENDING</div>
                <div class="h3 fw-bold text-primary mb-0"><?= $hr_overview['pending_hires'] + $hr_overview['pending_leaves'] ?></div>
                <div class="text-xs text-secondary">Hires + Leaves</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card p-3">
                <div class="text-xs text-secondary mb-1">FINANCE PENDING</div>
                <div class="h3 fw-bold text-success mb-0"><?= $finance_overview['pending_budgets'] ?></div>
                <div class="text-xs text-secondary">Budget Approvals</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card p-3">
                <div class="text-xs text-secondary mb-1">PLANNING PENDING</div>
                <div class="h3 fw-bold text-warning mb-0"><?= $planning_overview['pending_schedules'] ?></div>
                <div class="text-xs text-secondary">Schedule Reviews</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card p-3">
                <div class="text-xs text-secondary mb-1">ACTIVE PROJECTS</div>
                <div class="h3 fw-bold text-info mb-0"><?= $planning_overview['active_projects'] ?></div>
                <div class="text-xs text-secondary">Under Oversight</div>
            </div>
        </div>
    </div>

    <!-- APPROVAL QUEUE -->
    <div class="glass-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold mb-0"><i class="fas fa-gavel me-2 text-warning"></i> Approval Queue</h5>
            <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-secondary active">All</button>
                <button class="btn btn-outline-secondary">HR</button>
                <button class="btn btn-outline-secondary">Finance</button>
                <button class="btn btn-outline-secondary">Bids</button>
            </div>
        </div>

        <?php if (empty($pending_approvals)): ?>
            <div class="text-center py-5">
                <i class="fas fa-check-circle fa-3x text-success mb-3 opacity-20"></i>
                <h5 class="text-secondary">All Clear!</h5>
                <p class="text-muted">No pending approvals at this time.</p>
            </div>
        <?php else: ?>
            <?php foreach ($pending_approvals as $approval): ?>
            <div class="approval-card">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="module-badge badge-<?= strtolower($approval['module_type']) ?>">
                            <?= $approval['module_type'] ?>
                        </span>
                        <span class="ms-2 text-secondary text-xs"><?= $approval['approval_type'] ?></span>
                    </div>
                    <div class="text-end text-xs text-secondary">
                        <?= date('M d, Y H:i', strtotime($approval['created_at'])) ?>
                    </div>
                </div>

                <div class="mb-3">
                    <?php if ($approval['module_type'] === 'HR'): ?>
                        <h6 class="fw-bold text-white mb-1">
                            <?= $approval['approval_type'] === 'Employee Approval' ? 
                                htmlspecialchars($approval['first_name'] . ' ' . $approval['last_name']) : 
                                htmlspecialchars($approval['employee_name']) ?>
                        </h6>
                        <p class="text-sm text-secondary mb-0">
                            <?= $approval['approval_type'] === 'Employee Approval' ? 
                                'Position: ' . htmlspecialchars($approval['designation']) : 
                                'Leave Type: ' . htmlspecialchars($approval['leave_type'] ?? 'N/A') ?>
                        </p>
                    <?php elseif ($approval['module_type'] === 'FINANCE'): ?>
                        <h6 class="fw-bold text-white mb-1">
                            Budget: <?= htmlspecialchars($approval['project_name'] ?? 'General') ?>
                        </h6>
                        <p class="text-sm text-secondary mb-0">
                            Amount: $<?= number_format($approval['total_amount'] ?? 0, 2) ?>
                        </p>
                    <?php elseif ($approval['module_type'] === 'BIDS'): ?>
                        <h6 class="fw-bold text-white mb-1">
                            Bid: <?= htmlspecialchars($approval['tender_no']) ?>
                        </h6>
                        <p class="text-sm text-secondary mb-0">
                            Client: <?= htmlspecialchars($approval['client_name']) ?> | 
                            Deadline: <?= date('M d, Y', strtotime($approval['deadline'])) ?>
                        </p>
                    <?php elseif ($approval['module_type'] === 'PROCUREMENT'): ?>
                        <h6 class="fw-bold text-white mb-1">
                            Material Request - <?= htmlspecialchars($approval['site_name']) ?>
                        </h6>
                        <p class="text-sm text-secondary mb-0">
                            Request ID: #<?= $approval['id'] ?>
                        </p>
                    <?php elseif ($approval['module_type'] === 'PLANNING'): ?>
                        <h6 class="fw-bold text-white mb-1">
                            Schedule: <?= htmlspecialchars($approval['project_name']) ?>
                        </h6>
                        <p class="text-sm text-secondary mb-0">
                            Schedule ID: #<?= $approval['id'] ?>
                        </p>
                    <?php endif; ?>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-success rounded-pill px-4" 
                            onclick="showApprovalModal(<?= $approval['id'] ?>, '<?= $approval['module_type'] ?>', 'approved')">
                        <i class="fas fa-check me-1"></i> Approve
                    </button>
                    <button class="btn btn-sm btn-danger rounded-pill px-4" 
                            onclick="showApprovalModal(<?= $approval['id'] ?>, '<?= $approval['module_type'] ?>', 'rejected')">
                        <i class="fas fa-times me-1"></i> Reject
                    </button>
                    <button class="btn btn-sm btn-outline-secondary rounded-pill px-4">
                        <i class="fas fa-eye me-1"></i> Details
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- APPROVAL MODAL -->
<div id="approvalModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="modalTitle">Confirm Decision</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="process">
                    <input type="hidden" name="ref_id" id="modalRefId">
                    <input type="hidden" name="module" id="modalModule">
                    <input type="hidden" name="decision" id="modalDecision">
                    
                    <div class="mb-3">
                        <label class="form-label">Reason / Notes</label>
                        <textarea name="reason" class="form-control bg-dark text-white border-secondary" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="modalSubmitBtn">Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showApprovalModal(refId, module, decision) {
    document.getElementById('modalRefId').value = refId;
    document.getElementById('modalModule').value = module;
    document.getElementById('modalDecision').value = decision;
    document.getElementById('modalTitle').textContent = decision === 'approved' ? 'Approve Request' : 'Reject Request';
    document.getElementById('modalSubmitBtn').className = decision === 'approved' ? 'btn btn-success' : 'btn btn-danger';
    document.getElementById('modalSubmitBtn').textContent = decision === 'approved' ? 'Approve' : 'Reject';
    
    new bootstrap.Modal(document.getElementById('approvalModal')).show();
}
</script>
