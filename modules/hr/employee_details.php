<?php
// modules/hr/employee_details.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/HRManager.php';

AuthManager::requireRole(['HR_MANAGER', 'GM']);

$emp_id = $_GET['id'] ?? null;
if (!$emp_id) {
    echo "<div class='glass-card alert-danger'>Employee ID required.</div>";
    return;
}

$employee = HRManager::getEmployeeById($emp_id);
if (!$employee) {
    echo "<div class='glass-card alert-danger'>Employee not found.</div>";
    return;
}

$documents = HRManager::getEmployeeDocuments($emp_id);
$is_gm = $_SESSION['role'] === 'GM';

// Handle Approval Actions
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_gm) {
    try {
        if (isset($_POST['action']) && $_POST['action'] === 'approve') {
            HRManager::approveEmployee($emp_id, $_SESSION['user_id']);
            header("Location: main.php?module=hr/employee_details&id=$emp_id&success=approved");
            exit;
        }
        if (isset($_POST['action']) && $_POST['action'] === 'reject') {
            HRManager::rejectEmployee($emp_id, $_SESSION['user_id'], $_POST['reason']);
            header("Location: main.php?module=hr/employee_details&id=$emp_id&success=rejected");
            exit;
        }
    } catch (Exception $e) {
        $msg = "Error: " . $e->getMessage();
    }
}

$success_msg = $_GET['success'] ?? '';
?>

<div class="employee-details-module">
    <div class="section-header mb-4" style="display:flex; justify-content:space-between; align-items:center;">
        <h2><i class="fas fa-id-card"></i> Employee Profile: <?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?></h2>
        <a href="main.php?module=hr/employees" class="btn-secondary-sm" style="text-decoration:none;">Back to Directory</a>
    </div>

    <?php if ($success_msg): ?>
        <div class="alert alert-success glass-card mb-4" style="color: #00ff64; border-left: 5px solid #00ff64;">
            <i class="fas fa-check-circle"></i> Action completed successfully: <?= htmlspecialchars($success_msg) ?>
        </div>
    <?php endif; ?>

    <?php if ($employee['gm_approval_status'] === 'pending'): ?>
        <div class="glass-card mb-4" style="border-left: 5px solid var(--gold); background: rgba(255, 204, 0, 0.05);">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h4 style="color:var(--gold); margin:0;">⚠️ Pending General Manager Approval</h4>
                    <p style="margin: 0.5rem 0 0 0; color:var(--text-dim);">This profile is restricted until GM reviews the credentials.</p>
                </div>
                <?php if ($is_gm): ?>
                    <div style="display:flex; gap:1rem;">
                        <form method="POST" style="margin:0;">
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" class="btn-primary-sm" style="background:#00ff64; color:black;">Approve Access</button>
                        </form>
                        <button class="btn-secondary-sm" onclick="document.getElementById('rejectModal').style.display='flex'" style="background:#ff4444; color:white;">Reject</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
        <!-- Left: Core Info -->
        <div style="display:flex; flex-direction:column; gap:1.5rem;">
            <div class="glass-card">
                <h4 style="color:var(--gold); margin-bottom:1.5rem; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:0.5rem;">Professional Identity</h4>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                    <div>
                        <div class="info-group mb-3">
                            <label style="color:var(--text-dim); font-size:0.8rem; display:block;">Employee ERP Code</label>
                            <span style="font-size:1.1rem; font-weight:bold; letter-spacing:1px;"><?= htmlspecialchars($employee['employee_code']) ?></span>
                        </div>
                        <div class="info-group mb-3">
                            <label style="color:var(--text-dim); font-size:0.8rem; display:block;">Department</label>
                            <span><?= htmlspecialchars($employee['department']) ?></span>
                        </div>
                        <div class="info-group">
                            <label style="color:var(--text-dim); font-size:0.8rem; display:block;">Designation</label>
                            <span><?= htmlspecialchars($employee['position'] ?? 'Not Assigned') ?></span>
                        </div>
                    </div>
                    <div>
                        <div class="info-group mb-3">
                            <label style="color:var(--text-dim); font-size:0.8rem; display:block;">System Username</label>
                            <span style="color:var(--gold);"><?= htmlspecialchars($employee['username']) ?></span>
                        </div>
                        <div class="info-group mb-3">
                            <label style="color:var(--text-dim); font-size:0.8rem; display:block;">Base Compensation</label>
                            <span style="font-weight:bold;"><?= number_format($employee['base_salary'] ?? 0, 2) ?> USD / <?= ucfirst($employee['salary_type'] ?? 'monthly') ?></span>
                        </div>
                        <div class="info-group">
                            <label style="color:var(--text-dim); font-size:0.8rem; display:block;">System Role</label>
                            <span class="status-badge" style="background:rgba(255,255,255,0.1);"><?= htmlspecialchars($employee['role_name'] ?? 'User') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="glass-card">
                <h4 style="color:var(--gold); margin-bottom:1.5rem; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:0.5rem;">Employee Documents</h4>
                <?php if (empty($documents)): ?>
                    <p style="color:var(--text-dim); text-align:center; padding: 2rem;">No verified documents on file.</p>
                <?php else: ?>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                        <?php foreach ($documents as $doc): ?>
                            <div style="background:rgba(0,0,0,0.2); padding:1rem; border-radius:8px; display:flex; justify-content:space-between; align-items:center;">
                                <div>
                                    <i class="fas fa-file-pdf" style="color:#ff4444; margin-right:0.5rem;"></i>
                                    <span><?= htmlspecialchars($doc['doc_type']) ?></span>
                                </div>
                                <a href="<?= htmlspecialchars($doc['file_path']) ?>" target="_blank" class="btn-secondary-sm"><i class="fas fa-download"></i></a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right: Status/Timeline -->
        <div style="display:flex; flex-direction:column; gap:1.5rem;">
            <div class="glass-card">
                <h4 style="color:var(--gold); margin-bottom:1.5rem; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:0.5rem;">Status Tracking</h4>
                <div class="mb-4">
                    <label style="color:var(--text-dim); font-size:0.8rem; display:block; margin-bottom:0.5rem;">Current Employment Status</label>
                    <span class="status-badge <?= $employee['status'] ?>"><?= ucfirst($employee['status']) ?></span>
                </div>
                <div>
                    <label style="color:var(--text-dim); font-size:0.8rem; display:block; margin-bottom:0.5rem;">GM Approval State</label>
                    <span class="status-badge <?= $employee['gm_approval_status'] ?>"><?= ucfirst($employee['gm_approval_status']) ?></span>
                </div>
            </div>

            <div class="glass-card">
                <h4 style="color:var(--gold); margin-bottom:1.5rem; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:0.5rem;">Quick Actions</h4>
                <button class="btn-secondary-sm mb-2" style="width:100%; text-align:left;"><i class="fas fa-edit"></i> Edit Details</button>
                <button class="btn-secondary-sm mb-2" style="width:100%; text-align:left;"><i class="fas fa-user-shield"></i> Reset Password</button>
                <button class="btn-secondary-sm" style="width:100%; text-align:left; color:#ff4444;"><i class="fas fa-archive"></i> Terminate/Archive</button>
            </div>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div id="rejectModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); z-index:9999; justify-content:center; align-items:center;">
    <div class="glass-card" style="width:100%; max-width:500px; padding:2rem;">
        <h3 style="color:#ff4444; margin-bottom:1.5rem;">Reject Registration</h3>
        <form method="POST">
            <input type="hidden" name="action" value="reject">
            <div class="form-group mb-4">
                <label>Reason for rejection (Required)</label>
                <textarea name="reason" required style="width:100%; background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.1); border-radius:8px; color:white; padding:1rem; min-height:100px;"></textarea>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:1rem;">
                <button type="button" onclick="document.getElementById('rejectModal').style.display='none'" class="btn-secondary-sm">Cancel</button>
                <button type="submit" class="btn-primary-sm" style="background:#ff4444; color:white;">Confirm Reject</button>
            </div>
        </form>
    </div>
</div>
