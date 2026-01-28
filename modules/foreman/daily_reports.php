<?php
// modules/foreman/daily_reports.php
require_once '../../includes/AuthManager.php';
require_once '../../includes/ForemanManager.php';

AuthManager::requireRole('FORMAN');
$site = ForemanManager::getAssignedSite($_SESSION['user_id']);
$site_id = $site['id'];
$planned_work = ForemanManager::getPlannedWorkSnapshot($site_id);
$today = date('D, d M Y');

// Handle Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ForemanManager::submitDailyReport($site_id, $_SESSION['user_id'], $_POST);
    header("Location: index.php?module=foreman/dashboard&success=report_submitted");
    exit;
}
?>

<div class="main-content mobile-layout">
    <div class="site-header mb-3">
        <h6 class="text-muted text-uppercase mb-0">Daily Site Report</h6>
        <h2 class="text-gold mb-0"><?= htmlspecialchars($site['site_name']) ?></h2>
        <div class="text-white small"><?= $today ?> &bull; Foreman: <?= $_SESSION['username'] ?></div>
    </div>

    <form method="POST" id="dailyReportForm">
        
        <!-- SECTION 1: PLANNING (Read-Only) -->
        <div class="card glass-panel mb-3">
            <div class="card-header text-info text-uppercase font-weight-bold">
                <i class="fas fa-bullseye"></i> Planned for Today
            </div>
            <div class="card-body bg-dark rounded mx-2 mb-2 p-2 text-white-50">
                <?= nl2br(htmlspecialchars($planned_work)) ?>
            </div>
        </div>

        <!-- SECTION 2: WORK PROGRESS -->
        <div class="card glass-panel mb-3">
             <div class="card-header text-warning text-uppercase font-weight-bold">
                <i class="fas fa-person-digging"></i> Actual Progress
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Actual Work Done</label>
                    <textarea name="actual_work" class="form-control bg-dark text-white" rows="3" required placeholder="Describe what was actually built..."></textarea>
                </div>
                <div class="form-group">
                    <label>Progress % (Estimate)</label>
                    <input type="range" class="custom-range" min="0" max="100" step="5" id="progressRange" name="progress_percent" oninput="document.getElementById('progVal').innerText = this.value + '%'">
                    <div class="text-right text-gold font-weight-bold" id="progVal">50%</div>
                </div>
            </div>
        </div>

        <!-- SECTION 3: RESOURCES -->
        <div class="card glass-panel mb-3">
             <div class="card-header text-success text-uppercase font-weight-bold">
                <i class="fas fa-tools"></i> Resources Used
            </div>
            <div class="card-body">
                 <div class="form-group row">
                    <div class="col-6">
                        <label>Labor Count</label>
                        <input type="number" name="labor_count" class="form-control bg-dark text-white" placeholder="0" required>
                    </div>
                    <div class="col-6">
                        <label>Weather</label>
                        <select name="weather" class="form-control bg-dark text-white">
                            <option>Sunny</option>
                            <option>Cloudy</option>
                            <option>Rainy</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Equipment Used</label>
                    <input type="text" name="equipment_used" class="form-control bg-dark text-white" placeholder="e.g. Excavator, Crane">
                </div>

                <div class="form-group">
                    <label>Material Usage</label>
                    <div id="materialList">
                        <!-- Dynamic Rows -->
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-success mt-2" onclick="addMaterialRow()">
                        <i class="fas fa-plus"></i> Add Material
                    </button>
                </div>
            </div>
        </div>

        <!-- SECTION 4: ISSUES -->
        <div class="card glass-panel mb-4">
             <div class="card-header text-danger text-uppercase font-weight-bold">
                <i class="fas fa-exclamation-circle"></i> Issues & Safety
            </div>
            <div class="card-body">
                 <div class="form-group">
                    <label>Blockers / Delays</label>
                    <textarea name="blockers" class="form-control bg-dark text-white" rows="2" placeholder="None"></textarea>
                </div>
                <div class="form-group">
                    <label>Safety Notes</label>
                    <textarea name="safety_notes" class="form-control bg-dark text-white" rows="2" placeholder="Describe any safety incidents or checks."></textarea>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-gold btn-block btn-lg mb-5">Submit Final Report</button>
    </form>
</div>

<script>
function addMaterialRow() {
    const div = document.createElement('div');
    div.className = 'd-flex mb-2 material-row';
    div.innerHTML = `
        <input type="text" name="materials[item][]" class="form-control bg-dark text-white mr-1" placeholder="Item Name" required>
        <input type="number" name="materials[qty][]" class="form-control bg-dark text-white mr-1" placeholder="Qty" style="width: 80px;" required>
        <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">X</button>
    `;
    document.getElementById('materialList').appendChild(div);
}

// Intercept form to structure JSON nicely if needed, 
// OR just handle array inputs in PHP (current approach uses arrays materials[item][] which is easier)
document.getElementById('dailyReportForm').onsubmit = function(e) {
    // Optional: Validate that at least one field is filled
    return true;
}
</script>

<style>
.btn-gold {
    background: var(--gold);
    color: #000;
    font-weight: bold;
    border: none;
}
.custom-range {
    accent-color: var(--gold);
}
</style>
