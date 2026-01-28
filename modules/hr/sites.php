<?php
// modules/hr/sites.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/HRManager.php';

AuthManager::requireRole('HR_MANAGER');

$won_tenders = array_filter(HRManager::getTenders(), function($t) { return $t['status'] === 'WON'; });
$sites = HRManager::getSites();
// For role assignment, we need users who are Foremen or Store Keepers
$db = Database::getInstance();
$staff = $db->query("SELECT u.id, u.username, r.role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE r.role_name IN ('FOREMAN', 'STORE_KEEPER')")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'init_project') {
        HRManager::convertTenderToProject($_POST['tender_id'], $_SESSION['user_id']);
    } elseif ($_POST['action'] === 'create_site') {
        HRManager::createSite($_POST, $_SESSION['user_id']);
    } elseif ($_POST['action'] === 'assign_staff') {
        HRManager::assignSiteStaff($_POST['site_id'], $_POST['user_id'], $_POST['role_type'], $_SESSION['user_id']);
    }
    header("Location: main.php?module=hr/sites&success=1");
    exit();
}
?>

<div class="sites-module">
    <div class="row" style="display:flex; gap:1.5rem;">
        <!-- Left: Project Initialization -->
        <div style="flex: 1;">
            <div class="section-header mb-3">
                <h3><i class="fas fa-rocket"></i> Launch Site</h3>
                <p class="text-dim">Convert WON tenders into active project sites.</p>
            </div>
            
            <div class="glass-card mb-4">
                <h4>Initialize Project from Won Bid</h4>
                <form method="POST" style="margin-top:1rem;">
                    <input type="hidden" name="action" value="init_project">
                    <select name="tender_id" required style="width:100%; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); color:white; padding:0.8rem; border-radius:8px; margin-bottom:1rem;">
                        <option value="">Select Won Tender...</option>
                        <?php foreach ($won_tenders as $wt): ?>
                            <option value="<?= $wt['id'] ?>"><?= $wt['tender_no'] ?> - <?= $wt['title'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn-primary-sm" style="width:100%;">Create Project</button>
                </form>
            </div>

            <div class="glass-card">
                <h4>New Site Location</h4>
                <form method="POST" style="margin-top:1rem;">
                    <input type="hidden" name="action" value="create_site">
                    <div class="form-group">
                        <label>Select Project</label>
                        <select name="project_id" required style="width:100%; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); color:white; padding:0.8rem; border-radius:8px;">
                            <?php 
                            $projs = $db->query("SELECT id, project_name FROM projects")->fetchAll();
                            foreach ($projs as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= $p['project_name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Site Name</label>
                        <input type="text" name="site_name" required placeholder="e.g. Block B Foundation">
                    </div>
                    <div class="form-group">
                        <label>Location</label>
                        <input type="text" name="location" required placeholder="City / District">
                    </div>
                    <button type="submit" class="btn-primary-sm" style="width:100%;">Register Site</button>
                </form>
            </div>
        </div>

        <!-- Right: Site Assignments -->
        <div style="flex: 2;">
            <div class="section-header mb-3">
                <h3><i class="fas fa-hard-hat"></i> Role Assignments</h3>
            </div>
            
            <div class="glass-card mb-4">
                <h4>Assign Foreman / Store Keeper</h4>
                <form method="POST" style="display:flex; gap:1rem; margin-top:1rem; align-items:flex-end;">
                    <input type="hidden" name="action" value="assign_staff">
                    <div style="flex:1;">
                        <label>Site</label>
                        <select name="site_id" required style="width:100%; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); color:white; padding:0.6rem; border-radius:8px;">
                            <?php foreach ($sites as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= $s['site_name'] ?> (<?= $s['project_name'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="flex:1;">
                        <label>Staff Member</label>
                        <select name="user_id" required style="width:100%; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); color:white; padding:0.6rem; border-radius:8px;">
                            <?php foreach ($staff as $st): ?>
                                <option value="<?= $st['id'] ?>"><?= $st['username'] ?> [<?= $st['role_name'] ?>]</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="flex:0.5;">
                        <label>Role</label>
                        <select name="role_type" required style="width:100%; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); color:white; padding:0.6rem; border-radius:8px;">
                            <option value="foreman">Foreman</option>
                            <option value="store_keeper">Store Keeper</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-primary-sm">Confirm</button>
                </form>
            </div>

            <div class="glass-card">
                <h4>Active Assignments</h4>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Site</th>
                            <th>Staff Name</th>
                            <th>Role</th>
                            <th>Assigned At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $assignments = $db->query("SELECT ssa.*, s.site_name, u.username FROM site_staff_assignments ssa JOIN sites s ON ssa.site_id = s.id JOIN users u ON ssa.user_id = u.id WHERE ssa.status = 'active'")->fetchAll();
                        foreach ($assignments as $as): ?>
                            <tr>
                                <td><?= $as['site_name'] ?></td>
                                <td><?= $as['username'] ?></td>
                                <td><span class="status-badge" style="background:rgba(255,255,255,0.1);"><?= strtoupper($as['role_type']) ?></span></td>
                                <td style="font-size:0.8rem;"><?= date('M d, Y', strtotime($as['assigned_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
