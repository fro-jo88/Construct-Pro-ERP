<?php
// modules/foreman/materials.php
require_once '../../includes/AuthManager.php';
require_once '../../includes/ForemanManager.php';

AuthManager::requireRole('FORMAN');
$site = ForemanManager::getAssignedSite($_SESSION['user_id']);
$site_id = $site['id'];

// Handle Request Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $priority = isset($_POST['emergency']) ? 'emergency' : 'normal';
    ForemanManager::createMaterialRequest($site_id, $_SESSION['user_id'], $_POST['item_name'], $_POST['quantity'], $priority, $_POST['reason']);
    header("Location: index.php?module=foreman/materials&success=1");
    exit;
}

// Fetch Pending Requests
$db = Database::getInstance();
$requests = $db->query("SELECT * FROM material_requests WHERE site_id = $site_id ORDER BY created_at DESC LIMIT 20")->fetchAll();
?>

<div class="main-content mobile-layout">
    <div class="mb-3">
        <a href="index.php?module=foreman/dashboard" class="text-muted"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        <h2 class="text-gold mt-2">Material Requests</h2>
    </div>

    <!-- REQUEST FORM -->
    <div class="card glass-panel mb-4">
        <div class="card-header">
            <h4>New Request</h4>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="form-group">
                    <label>Material Name / Item</label>
                    <input type="text" name="item_name" class="form-control bg-dark text-white" placeholder="e.g. Cement Bags" required>
                </div>
                <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" step="0.01" name="quantity" class="form-control bg-dark text-white" placeholder="0.00" required>
                </div>
                <div class="form-group">
                    <label>Reason / Justification</label>
                    <textarea name="reason" class="form-control bg-dark text-white" rows="2" required></textarea>
                </div>
                
                <div class="form-group custom-control custom-switch my-3">
                    <input type="checkbox" class="custom-control-input" id="emergencySwitch" name="emergency" value="1" onchange="toggleEmergency(this)">
                    <label class="custom-control-label text-white" for="emergencySwitch">🚨 EMERGENCY REQUEST</label>
                    <small class="form-text text-muted" id="emergencyText">Normal priority. Goes to Store Manager.</small>
                </div>

                <button type="submit" class="btn btn-warning btn-block btn-lg" id="submitBtn">Send Request</button>
            </form>
        </div>
    </div>

    <!-- REQUEST HISTORY -->
    <h5 class="text-muted">Recent Requests</h5>
    <?php foreach ($requests as $req): ?>
    <div class="card glass-panel mb-2" style="border-left: 5px solid <?= $req['priority'] === 'emergency' ? '#ff4444' : '#ffbb33' ?>;">
        <div class="card-body p-2">
            <div class="d-flex justify-content-between">
                <strong><?= htmlspecialchars($req['item_name']) ?></strong>
                <span class="badge badge-<?= $req['priority'] === 'emergency' ? 'danger' : 'warning' ?>">
                    <?= strtoupper($req['priority']) ?>
                </span>
            </div>
            <div class="d-flex justify-content-between mt-1">
                <small class="text-white">Qty: <?= $req['quantity'] ?></small>
                <small class="text-<?= $req['status'] === 'approved' ? 'success' : 'muted' ?>">
                    <?= ucwords($req['status']) ?>
                </small>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
function toggleEmergency(checkbox) {
    const btn = document.getElementById('submitBtn');
    const text = document.getElementById('emergencyText');
    if (checkbox.checked) {
        btn.classList.remove('btn-warning');
        btn.classList.add('btn-danger');
        btn.innerText = '🚨 ESCALATE TO GM NOW';
        text.innerText = 'Goes DIRECTLY to GM. Use only for stoppages.';
        text.classList.add('text-danger');
    } else {
        btn.classList.remove('btn-danger');
        btn.classList.add('btn-warning');
        btn.innerText = 'Send Request';
        text.innerText = 'Normal priority. Goes to Store Manager.';
        text.classList.remove('text-danger');
    }
}
</script>
