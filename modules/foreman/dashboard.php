<?php
// modules/foreman/dashboard.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/ForemanManager.php';

// Strict Role Check
AuthManager::requireRole('FORMAN');
$foreman_id = $_SESSION['user_id'];
$site = ForemanManager::getAssignedSite($foreman_id);

// Enforce Site Assignment
if (!$site) {
    echo "<div class='alert alert-danger'>You are not assigned to any site. Please contact HR.</div>";
    exit;
}

$site_id = $site['id'];
$stats = ForemanManager::getSiteStats($site_id);
$activity = ForemanManager::getRecentActivity($site_id);
$today = date('D, d M');

// Handle Daily Report Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['daily_report'])) {
    ForemanManager::submitDailyReport($site_id, $foreman_id, $_POST);
    // Reload to update stats
    header("Location: index.php?module=foreman/dashboard");
    exit;
}
?>

<div class="main-content mobile-layout">
    
    <!-- HEADER -->
    <div class="site-header d-flex justify-content-between align-items-center mb-3">
        <div>
            <h6 class="text-muted text-uppercase mb-0">Assigned Site</h6>
            <h2 class="text-gold mb-0"><?= htmlspecialchars($site['site_name']) ?></h2>
            <small><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($site['location']) ?></small>
        </div>
        <div class="text-right">
             <span class="badge badge-success p-2">ACTIVE</span>
             <div class="date-badge mt-1 text-white"><?= $today ?></div>
        </div>
    </div>

    <!-- MAIN ACTIONS (BIG BUTTONS) -->
    <div class="row mb-3">
        <div class="col-6 pr-1">
             <button class="btn btn-action glass-panel btn-block" data-toggle="modal" data-target="#dailyReportModal">
                <i class="fas fa-clipboard-check icon-lg text-primary"></i>
                <span>Daily Report</span>
                <?php if($stats['report_submitted']): ?>
                    <i class="fas fa-check-circle check-overlay text-success"></i>
                <?php endif; ?>
             </button>
        </div>
        <div class="col-6 pl-1">
             <a href="index.php?module=foreman/materials" class="btn btn-action glass-panel btn-block">
                <i class="fas fa-truck icon-lg text-warning"></i>
                <span>Materials</span>
                <?php if($stats['pending_materials'] > 0): ?>
                    <span class="badge badge-danger badge-counter"><?= $stats['pending_materials'] ?></span>
                <?php endif; ?>
             </a>
        </div>
        <div class="col-6 pr-1 mt-2">
             <a href="index.php?module=foreman/incidents" class="btn btn-action glass-panel btn-block">
                <i class="fas fa-exclamation-triangle icon-lg text-danger"></i>
                <span>Report Incident</span>
             </a>
        </div>
        <div class="col-6 pl-1 mt-2">
             <a href="index.php?module=foreman/plan" class="btn btn-action glass-panel btn-block">
                <i class="fas fa-calendar-alt icon-lg text-info"></i>
                <span>Weekly Plan</span>
             </a>
        </div>
    </div>

    <!-- SITE STATS SUMMARY -->
    <div class="card glass-panel mb-3">
        <div class="card-body py-2 d-flex justify-content-around text-center">
            <div>
                 <h3 class="mb-0 text-white"><?= $stats['pending_labor'] ?></h3>
                 <small class="text-muted">Labor Req</small>
            </div>
            <div class="border-left"></div>
            <div>
                 <h3 class="mb-0 text-white"><?= $site['foreman_id'] ? '1' : '0' ?></h3> <!-- Mock Safety -->
                 <small class="text-muted">Safety Score</small>
            </div>
            <div class="border-left"></div>
            <div>
                 <h3 class="mb-0 text-white">OK</h3>
                 <small class="text-muted">Status</small>
            </div>
        </div>
    </div>

    <!-- RECENT ACTIVITY FEED -->
    <h6 class="text-muted text-uppercase mb-2">Recent Site Activity</h6>
    <div class="activity-feed">
        <?php foreach ($activity as $log): ?>
        <div class="card glass-panel mb-2">
            <div class="card-body p-2 d-flex align-items-center">
                <div class="mr-3 text-center" style="width: 40px;">
                    <i class="fas fa-clipboard-list text-primary"></i>
                </div>
                <div>
                    <strong>Daily Report</strong>
                    <div class="small text-muted"><?= date('M d', strtotime($log['report_date'])) ?> &bull; <?= $log['manpower_count'] ?> Workers</div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- DAILY REPORT MODAL -->
    <div id="dailyReportModal" class="modal fade">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-panel" style="background:#1a1a2e">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Submitting Report: <?= $today ?></h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <input type="hidden" name="daily_report" value="1">
                        
                        <div class="form-group">
                            <label>Manpower Count</label>
                            <input type="number" name="manpower" class="form-control bg-dark text-white" required>
                        </div>
                        <div class="form-group">
                            <label>Weather Condition</label>
                            <select name="weather" class="form-control bg-dark text-white">
                                <option>Sunny</option>
                                <option>Cloudy</option>
                                <option>Rainy</option>
                                <option>Stormy</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Work Completed Today</label>
                            <textarea name="completed" class="form-control bg-dark text-white" rows="3" required></textarea>
                        </div>
                         <div class="form-group">
                            <label>Work Pending / Next Steps</label>
                            <textarea name="pending" class="form-control bg-dark text-white" rows="2"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Delays / Blockers (Optional)</label>
                            <textarea name="blockers" class="form-control bg-dark text-white border-danger" rows="2" placeholder="Any issues stopping work?"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block btn-lg">Submit Report</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
/* Mobile Specific Styles override */
.mobile-layout {
    padding: 10px;
    max-width: 600px;
    margin: 0 auto; 
}
.btn-action {
    height: 120px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    position: relative;
    border: 1px solid rgba(255,255,255,0.1);
}
.icon-lg {
    font-size: 2.5rem;
    margin-bottom: 10px;
}
.badge-counter {
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 0.9rem;
}
.check-overlay {
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 1.2rem;
}
</style>
