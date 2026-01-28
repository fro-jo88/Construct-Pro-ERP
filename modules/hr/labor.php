<?php
// modules/hr/labor.php
require_once '../../includes/AuthManager.php';
require_once '../../includes/HRManager.php';
require_once '../../includes/SiteManager.php'; // Assuming SiteManager exists for getting sites

AuthManager::requireRole(['HR_MANAGER', 'GM', 'FORMAN']);

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];
$is_hr_gm = in_array($role, ['hr', 'gm']);

$message = "";

// Handle New Request (Foreman)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request_labor') {
    $site_id = $_POST['site_id']; // Foreman should pick their site or it's auto-assigned
    $role_req = $_POST['role_required'];
    $qty = $_POST['quantity'];
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];
    
    if (HRManager::requestLabor($site_id, $user_id, $role_req, $qty, $start, $end)) {
        $message = "Labor request submitted successfully.";
    } else {
        $message = "Error submitting request.";
    }
}

// Handle Assignment (HR)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'assign_labor' && $is_hr_gm) {
    if (HRManager::assignLaborToSite($_POST['employee_id'], $_POST['site_id'], $user_id, $_POST['start_date'])) {
        $message = "Employee assigned to site.";
    } else {
        $message = "Error assignment.";
    }
}

// Get Data
$requests = HRManager::getAllLaborRequests(); // Need to add this method or similar
$sites = SiteManager::getAllSites(); // Need validation if this exists
$available_employees = HRManager::getAvailableLabor(); // Need to add this

?>

<div class="main-content">
    <div class="page-header">
        <h1>Labor Management</h1>
        <?php if ($role === 'foreman'): ?>
            <button class="btn btn-primary" onclick="document.getElementById('requestModal').style.display='block'">+ Request Labor</button>
        <?php endif; ?>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>

    <!-- TABS -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#requests">Labor Requests</a>
        </li>
        <?php if ($is_hr_gm): ?>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#assignments">Assignments</a>
        </li>
        <?php endif; ?>
    </ul>

    <div class="tab-content">
        <!-- REQUESTS TAB -->
        <div class="tab-pane fade show active" id="requests">
            <div class="card glass-panel">
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Site</th>
                                <th>Role Needed</th>
                                <th>Qty</th>
                                <th>Dates</th>
                                <th>Status</th>
                                <?php if ($is_hr_gm): ?><th>Action</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $req): ?>
                            <tr>
                                <td><?= htmlspecialchars($req['site_name']) ?></td>
                                <td><?= htmlspecialchars($req['role_required']) ?></td>
                                <td><?= $req['quantity'] ?></td>
                                <td><?= $req['start_date'] ?> to <?= $req['end_date'] ?></td>
                                <td><span class="badge badge-<?= $req['status'] === 'pending' ? 'warning' : 'success' ?>"><?= ucfirst($req['status']) ?></span></td>
                                <?php if ($is_hr_gm && $req['status'] === 'pending'): ?>
                                <td>
                                    <button class="btn btn-sm btn-success" onclick="openAssignModal(<?= $req['site_id'] ?>, '<?= $req['role_required'] ?>')">Assign</button>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ASSIGNMENTS TAB (HR ONLY) -->
        <?php if ($is_hr_gm): ?>
        <div class="tab-pane fade" id="assignments">
             <div class="card glass-panel">
                <div class="card-body">
                    <h4>Direct Assignment</h4>
                    <form method="POST" class="form-inline mb-4">
                        <input type="hidden" name="action" value="assign_labor">
                        <select name="employee_id" class="form-control mr-2" required>
                            <option value="">Select Laborer...</option>
                            <?php foreach ($available_employees as $emp): ?>
                                <option value="<?= $emp['id'] ?>"><?= $emp['first_name'] . ' ' . $emp['last_name'] ?> (<?= $emp['designation'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <select name="site_id" class="form-control mr-2" required>
                            <option value="">Select Site...</option>
                            <?php foreach ($sites as $site): ?>
                                <option value="<?= $site['id'] ?>"><?= $site['site_name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="date" name="start_date" class="form-control mr-2" required>
                        <button type="submit" class="btn btn-primary">Assign</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Request Modal -->
<div id="requestModal" class="modal" style="display:none; background:rgba(0,0,0,0.8); position:fixed; top:0; left:0; width:100%; height:100%;">
    <div class="modal-dialog" style="margin-top:10%;">
        <div class="modal-content glass-panel">
            <div class="modal-header">
                <h4>Request Labor</h4>
                <button type="button" class="close text-white" onclick="document.getElementById('requestModal').style.display='none'">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="action" value="request_labor">
                    <div class="form-group">
                        <label>Site</label>
                        <select name="site_id" class="form-control">
                             <?php foreach ($sites as $site): ?>
                                <option value="<?= $site['id'] ?>"><?= $site['site_name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Role Required</label>
                        <input type="text" name="role_required" class="form-control" placeholder="e.g. Carpenter">
                    </div>
                    <div class="form-group">
                        <label>Quantity</label>
                        <input type="number" name="quantity" class="form-control" value="1">
                    </div>
                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" name="start_date" class="form-control">
                    </div>
                     <div class="form-group">
                        <label>End Date</label>
                        <input type="date" name="end_date" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Submit Request</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openAssignModal(siteId, role) {
    // Logic to pre-fill assignment tab or modal
    alert('Quick assign for ' + role + ' at site ' + siteId + ' coming soon. Please use Direct Assignment tab.');
}
</script>
