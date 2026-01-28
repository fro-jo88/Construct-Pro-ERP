<?php
// modules/hr/recruitment.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/HRManager.php';

AuthManager::requireRole('HR_MANAGER');

$jobs = HRManager::getJobRequests();
$applicants = HRManager::getApplicants();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create_job') {
        HRManager::createJobRequest($_POST, $_SESSION['user_id']);
    } elseif ($_POST['action'] === 'hire') {
        HRManager::hireApplicant($_POST['applicant_id'], $_SESSION['user_id']);
    }
    header("Location: main.php?module=hr/recruitment&success=1");
    exit();
}
?>

<div class="recruitment-module">
    <div class="row" style="display:flex; gap:1.5rem;">
        <!-- Left: Job Requests -->
        <div style="flex: 1;">
            <div class="section-header mb-3" style="display:flex; justify-content:space-between; align-items:center;">
                <h3><i class="fas fa-briefcase"></i> Job Requests</h3>
                <button class="btn-primary-sm" onclick="document.getElementById('jobModal').style.display='flex'">+ New Request</button>
            </div>
            <div class="glass-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Job Title</th>
                            <th>Dept</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jobs as $j): ?>
                            <tr>
                                <td><?= htmlspecialchars($j['title']) ?></td>
                                <td><?= htmlspecialchars($j['department']) ?></td>
                                <td><span class="status-badge"><?= $j['status'] ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right: Recent Applicants -->
        <div style="flex: 2;">
            <div class="section-header mb-3">
                <h3><i class="fas fa-users-rays"></i> Recent Applicants</h3>
            </div>
            <div class="glass-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Status</th>
                            <th>Match</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applicants as $a): ?>
                            <tr>
                                <td>
                                    <div style="font-weight:bold;"><?= $a['first_name'] . ' ' . $a['last_name'] ?></div>
                                    <div style="font-size:0.75rem; color:var(--text-dim);"><?= $a['email'] ?></div>
                                </td>
                                <td><?= $a['job_title'] ?></td>
                                <td><span class="status-badge <?= $a['status'] ?>"><?= $a['status'] ?></span></td>
                                <td><div style="width:50px; height:10px; background:#333; border-radius:5px;"><div style="width:85%; height:100%; background:var(--gold); border-radius:5px;"></div></div></td>
                                <td>
                                    <?php if ($a['status'] !== 'hired'): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="hire">
                                            <input type="hidden" name="applicant_id" value="<?= $a['id'] ?>">
                                            <button type="submit" class="btn-primary-sm" style="font-size:0.7rem;">Hire & ERP ID</button>
                                        </form>
                                    <?php endif; ?>
                                    <button class="btn-secondary-sm"><i class="fas fa-file-pdf"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- New Job Modal -->
<div id="jobModal" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:2000; justify-content:center; align-items:center;">
    <div class="glass-card" style="width:500px; padding:2rem;">
        <h3>Open Job Request</h3>
        <form method="POST">
            <input type="hidden" name="action" value="create_job">
            <div class="form-group">
                <label>Job Title</label>
                <input type="text" name="title" required placeholder="e.g. Senior Site Engineer">
            </div>
            <div class="form-group">
                <label>Department</label>
                <select name="department" style="width:100%; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); color:white; padding:0.8rem; border-radius:8px;">
                    <option value="Operations">Operations</option>
                    <option value="Engineering">Engineering</option>
                    <option value="Finance">Finance</option>
                    <option value="HR">HR</option>
                </select>
            </div>
            <div class="form-group">
                <label>Requirements</label>
                <textarea name="requirements" rows="3" style="width:100%; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); border-radius:8px; color:white; padding:0.5rem;"></textarea>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:1rem; margin-top:2rem;">
                <button type="button" class="btn-secondary-sm" onclick="document.getElementById('jobModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn-primary-sm">Open Vacancy</button>
            </div>
        </form>
    </div>
</div>

<style>
.recruitment-module .status-badge.hired { background: rgba(0, 255, 100, 0.2); color: #00ff64; }
.recruitment-module .status-badge.applied { background: rgba(255, 204, 0, 0.2); color: var(--gold); }
</style>
