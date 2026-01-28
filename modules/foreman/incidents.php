<?php
// modules/foreman/incidents.php
require_once '../../includes/AuthManager.php';
require_once '../../includes/ForemanManager.php';

AuthManager::requireRole('FORMAN');
$site = ForemanManager::getAssignedSite($_SESSION['user_id']);
$site_id = $site['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ForemanManager::reportIncident($site_id, $_SESSION['user_id'], $_POST['title'], $_POST['description'], $_POST['severity']);
    header("Location: index.php?module=foreman/incidents&success=1");
    exit;
}

$db = Database::getInstance();
$incidents = $db->query("SELECT * FROM site_incidents WHERE site_id = $site_id ORDER BY created_at DESC LIMIT 20")->fetchAll();
?>

<div class="main-content mobile-layout">
    <div class="mb-3">
        <a href="index.php?module=foreman/dashboard" class="text-muted"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        <h2 class="text-danger mt-2">Incident Reporting</h2>
    </div>

    <!-- REPORT FORM -->
    <div class="card glass-panel mb-4">
        <div class="card-header border-bottom border-danger">
            <h4 class="text-danger"><i class="fas fa-exclamation-circle"></i> Repor New Incident</h4>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="form-group">
                    <label>Incident Title</label>
                    <input type="text" name="title" class="form-control bg-dark text-white" placeholder="e.g. Excavator Breakdown" required>
                </div>
                <div class="form-group">
                    <label>Severity Level</label>
                    <select name="severity" class="form-control bg-dark text-white" onchange="updateColor(this)">
                        <option value="low">Low - Minor Issue</option>
                        <option value="medium">Medium - Needs Attention</option>
                        <option value="high">High - Work Stoppage</option>
                        <option value="critical">CRITICAL - Safety Hazard / Injury</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Detailed Description</label>
                    <textarea name="description" class="form-control bg-dark text-white" rows="3" required placeholder="Describe what happened, who was involved, and current status."></textarea>
                </div>
                <button type="submit" class="btn btn-danger btn-block btn-lg">Submit Report</button>
            </form>
        </div>
    </div>

    <!-- INCIDENT HISTORY -->
    <h5 class="text-muted">History</h5>
    <?php foreach ($incidents as $inc): ?>
    <div class="card glass-panel mb-2">
        <div class="card-body p-2">
            <div class="d-flex justify-content-between">
                <strong class="text-<?= $inc['severity'] === 'critical' ? 'danger' : 'white' ?>"><?= htmlspecialchars($inc['title']) ?></strong>
                <span class="badge badge-<?= $inc['severity'] === 'critical' ? 'danger' : ($inc['severity'] === 'high' ? 'warning' : 'secondary') ?>">
                    <?= strtoupper($inc['severity']) ?>
                </span>
            </div>
            <p class="small text-muted mb-0"><?= date('d M Y H:i', strtotime($inc['incident_date'])) ?></p>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
function updateColor(select) {
    if(select.value === 'critical') select.style.border = '2px solid red';
    else select.style.border = '1px solid #444';
}
</script>
