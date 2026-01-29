<?php
// modules/audit/construction_dashboard/site_audits.php

$db = Database::getInstance();

// Get filter parameters
$project_id = $_GET['project_id'] ?? null;
$site_id = $_GET['site_id'] ?? null;
$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to = $_GET['date_to'] ?? date('Y-m-d');
$work_category = $_GET['work_category'] ?? '';

// Fetch projects and sites for filters
$projects = $db->query("SELECT * FROM projects WHERE status = 'active' ORDER BY project_name")->fetchAll();
$sites = $db->query("SELECT * FROM sites ORDER BY site_name")->fetchAll();

// Fetch work progress data (Planning vs Actual)
$progressData = [];
if ($site_id) {
    // This would pull from master_schedules, weekly_plans, and daily_reports
    // Simplified for demonstration
    $progressData = $db->query("SELECT 
                                    'Foundation Work' as work_item,
                                    75.00 as planned_progress,
                                    68.00 as actual_progress,
                                    -7.00 as variance,
                                    'delayed' as flag
                                UNION ALL
                                SELECT 
                                    'Column Casting',
                                    50.00,
                                    52.00,
                                    2.00,
                                    'on_track'
                                UNION ALL
                                SELECT 
                                    'Beam Installation',
                                    30.00,
                                    15.00,
                                    -15.00,
                                    'delayed'")->fetchAll();
}

// Fetch Forman reports for the period
$formanReports = [];
if ($site_id) {
    $formanReports = $db->query("SELECT dr.*, u.username as forman_name 
                                 FROM daily_reports dr
                                 JOIN users u ON dr.forman_id = u.id
                                 WHERE dr.site_id = ? 
                                 AND dr.report_date BETWEEN ? AND ?
                                 ORDER BY dr.report_date DESC",
                                [$site_id, $date_from, $date_to])->fetchAll();
}

?>

<div class="glass-panel">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="fas fa-hard-hat text-danger me-2"></i> Site Execution Audit</h4>
        <div class="text-secondary text-sm">Planning vs Actual Verification</div>
    </div>

    <!-- FILTERS -->
    <form method="GET" class="row g-3 mb-4 p-4 bg-dark bg-opacity-20 rounded-3">
        <input type="hidden" name="module" value="audit/construction_dashboard/index">
        <input type="hidden" name="view" value="site_audits">
        
        <div class="col-md-3">
            <label class="form-label text-secondary text-xs fw-bold">PROJECT</label>
            <select name="project_id" class="form-select bg-dark text-white border-secondary">
                <option value="">-- Select Project --</option>
                <?php foreach ($projects as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $project_id == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['project_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="col-md-3">
            <label class="form-label text-secondary text-xs fw-bold">SITE</label>
            <select name="site_id" class="form-select bg-dark text-white border-secondary" required>
                <option value="">-- Select Site --</option>
                <?php foreach ($sites as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= $site_id == $s['id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['site_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="col-md-2">
            <label class="form-label text-secondary text-xs fw-bold">FROM DATE</label>
            <input type="date" name="date_from" class="form-control bg-dark text-white border-secondary" value="<?= $date_from ?>">
        </div>
        
        <div class="col-md-2">
            <label class="form-label text-secondary text-xs fw-bold">TO DATE</label>
            <input type="date" name="date_to" class="form-control bg-dark text-white border-secondary" value="<?= $date_to ?>">
        </div>
        
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-danger w-100">
                <i class="fas fa-search me-2"></i> Audit
            </button>
        </div>
    </form>

    <?php if ($site_id && !empty($progressData)): ?>
        <!-- PLANNING VS ACTUAL COMPARISON -->
        <div class="mb-5">
            <h5 class="fw-bold text-warning mb-3">
                <i class="fas fa-chart-line me-2"></i> Work Progress Verification
            </h5>
            <div class="table-responsive">
                <table class="table table-custom text-white">
                    <thead class="text-secondary text-xs text-uppercase fw-bold">
                        <tr>
                            <th>WORK ITEM</th>
                            <th class="text-center">PLANNED %</th>
                            <th class="text-center">ACTUAL %</th>
                            <th class="text-center">VARIANCE</th>
                            <th class="text-center">FLAG</th>
                            <th class="text-end">AUDIT ACTION</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        <?php foreach ($progressData as $item): ?>
                        <tr class="align-middle">
                            <td class="fw-medium"><?= htmlspecialchars($item['work_item']) ?></td>
                            <td class="text-center">
                                <span class="badge bg-primary bg-opacity-20 text-primary"><?= number_format($item['planned_progress'], 1) ?>%</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info bg-opacity-20 text-info"><?= number_format($item['actual_progress'], 1) ?>%</span>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold <?= $item['variance'] < 0 ? 'text-danger' : 'text-success' ?>">
                                    <?= $item['variance'] > 0 ? '+' : '' ?><?= number_format($item['variance'], 1) ?>%
                                </span>
                            </td>
                            <td class="text-center">
                                <?php
                                $flagClass = 'flag-normal';
                                $flagIcon = 'check-circle';
                                $flagText = 'ON TRACK';
                                
                                if ($item['flag'] === 'delayed') {
                                    $flagClass = 'flag-critical';
                                    $flagIcon = 'exclamation-circle';
                                    $flagText = 'DELAYED';
                                } elseif ($item['flag'] === 'partial') {
                                    $flagClass = 'flag-high';
                                    $flagIcon = 'clock';
                                    $flagText = 'PARTIAL';
                                }
                                ?>
                                <span class="status-badge <?= $flagClass ?>">
                                    <i class="fas fa-<?= $flagIcon ?> me-1"></i> <?= $flagText ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-warning rounded-pill px-3" onclick="addFinding('<?= htmlspecialchars($item['work_item']) ?>', 'planning_mismatch')">
                                    <i class="fas fa-flag me-1"></i> Flag
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- FORMAN REPORT AUDIT -->
        <div>
            <h5 class="fw-bold text-info mb-3">
                <i class="fas fa-clipboard-list me-2"></i> Forman Reports Review
            </h5>
            <div class="table-responsive">
                <table class="table table-custom text-white">
                    <thead class="text-secondary text-xs text-uppercase fw-bold">
                        <tr>
                            <th>DATE</th>
                            <th>FORMAN</th>
                            <th>WORK SUMMARY</th>
                            <th>MANPOWER</th>
                            <th class="text-center">AUDIT MARKER</th>
                            <th class="text-end">ACTION</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        <?php foreach ($formanReports as $report): ?>
                        <tr class="align-middle">
                            <td class="text-xs"><?= date('M d, Y', strtotime($report['report_date'])) ?></td>
                            <td class="fw-medium"><?= htmlspecialchars($report['forman_name']) ?></td>
                            <td class="text-xs"><?= htmlspecialchars(substr($report['work_summary'] ?? 'N/A', 0, 60)) ?>...</td>
                            <td><?= $report['manpower_count'] ?? 0 ?> workers</td>
                            <td class="text-center">
                                <span class="status-badge flag-normal">
                                    <i class="fas fa-check-circle me-1"></i> MATCHES PLAN
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="#" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                    <i class="fas fa-eye me-1"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($formanReports)): ?>
                            <tr><td colspan="6" class="text-center py-4 text-secondary">No Forman reports found for this period.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php elseif ($site_id): ?>
        <div class="alert alert-info border-0 bg-info bg-opacity-10 text-info">
            <i class="fas fa-info-circle me-2"></i> No audit data available for the selected filters.
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-filter fa-3x text-secondary mb-3 opacity-20"></i>
            <h5 class="text-secondary">Select Site and Date Range</h5>
            <p class="text-muted">Use the filters above to begin site execution audit.</p>
        </div>
    <?php endif; ?>
</div>

<script>
function addFinding(workItem, category) {
    alert('Add Finding: ' + workItem + '\nCategory: ' + category + '\n\nThis will open a modal to record audit finding.');
}
</script>
