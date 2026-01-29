<?php
// modules/audit/construction_dashboard/new_audit.php

$db = Database::getInstance();
$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_finding') {
        $stmt = $db->prepare("INSERT INTO audit_findings 
                              (auditor_id, project_id, site_id, audit_date, finding_category, severity, description, planned_value, actual_value, variance, status)
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft')");
        
        $variance = ($_POST['actual_value'] ?? 0) - ($_POST['planned_value'] ?? 0);
        
        $stmt->execute([
            $user_id,
            $_POST['project_id'] ?? null,
            $_POST['site_id'] ?? null,
            $_POST['audit_date'],
            $_POST['finding_category'],
            $_POST['severity'],
            $_POST['description'],
            $_POST['planned_value'] ?? null,
            $_POST['actual_value'] ?? null,
            $variance
        ]);
        
        $msg = "Finding recorded successfully.";
    } elseif ($_POST['action'] === 'submit_report') {
        // Create audit report
        $stmt = $db->prepare("INSERT INTO audit_reports 
                              (auditor_id, project_id, site_id, report_period_start, report_period_end, work_category, summary, recommendations, status, submitted_at)
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'submitted', NOW())");
        
        $stmt->execute([
            $user_id,
            $_POST['project_id'],
            $_POST['site_id'],
            $_POST['period_start'],
            $_POST['period_end'],
            $_POST['work_category'],
            $_POST['summary'],
            $_POST['recommendations']
        ]);
        
        $report_id = $db->lastInsertId();
        
        // Update all draft findings to submitted
        $db->query("UPDATE audit_findings SET status = 'submitted' WHERE auditor_id = ? AND status = 'draft'", [$user_id]);
        
        $msg = "Audit report #AR-" . str_pad($report_id, 4, '0', STR_PAD_LEFT) . " submitted successfully to GM and Finance Head.";
    }
}

// Fetch projects and sites
$projects = $db->query("SELECT * FROM projects WHERE status = 'active' ORDER BY project_name")->fetchAll();
$sites = $db->query("SELECT * FROM sites ORDER BY site_name")->fetchAll();

// Fetch draft findings for current auditor
$draftFindings = $db->query("SELECT af.*, s.site_name, p.project_name 
                             FROM audit_findings af
                             LEFT JOIN sites s ON af.site_id = s.id
                             LEFT JOIN projects p ON af.project_id = p.id
                             WHERE af.auditor_id = ? AND af.status = 'draft'
                             ORDER BY af.created_at DESC",
                            [$user_id])->fetchAll();

?>

<div class="glass-panel">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="fas fa-plus-circle text-success me-2"></i> New Audit Session</h4>
        <div class="text-secondary text-sm">Record Findings & Submit Report</div>
    </div>

    <?php if (isset($msg)): ?>
        <div class="alert alert-success border-0 bg-success bg-opacity-10 text-success mb-4">
            <i class="fas fa-check-circle me-2"></i> <?= $msg ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- LEFT: ADD FINDING FORM -->
        <div class="col-lg-6">
            <div class="p-4 bg-dark bg-opacity-30 rounded-3">
                <h5 class="fw-bold text-warning mb-4">
                    <i class="fas fa-flag me-2"></i> Record Audit Finding
                </h5>
                
                <form method="POST">
                    <input type="hidden" name="action" value="add_finding">
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary text-xs fw-bold">PROJECT</label>
                        <select name="project_id" class="form-select bg-dark text-white border-secondary">
                            <option value="">-- Select Project --</option>
                            <?php foreach ($projects as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['project_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary text-xs fw-bold">SITE *</label>
                        <select name="site_id" class="form-select bg-dark text-white border-secondary" required>
                            <option value="">-- Select Site --</option>
                            <?php foreach ($sites as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['site_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary text-xs fw-bold">AUDIT DATE *</label>
                        <input type="date" name="audit_date" class="form-control bg-dark text-white border-secondary" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary text-xs fw-bold">FINDING CATEGORY *</label>
                        <select name="finding_category" class="form-select bg-dark text-white border-secondary" required>
                            <option value="planning_mismatch">Planning Mismatch</option>
                            <option value="material_variance">Material Variance</option>
                            <option value="reporting_inconsistency">Reporting Inconsistency</option>
                            <option value="work_quality">Work Quality Issue</option>
                            <option value="safety_issue">Safety Issue</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary text-xs fw-bold">SEVERITY *</label>
                        <select name="severity" class="form-select bg-dark text-white border-secondary" required>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
                    
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label text-secondary text-xs fw-bold">PLANNED VALUE</label>
                            <input type="number" step="0.01" name="planned_value" class="form-control bg-dark text-white border-secondary" placeholder="0.00">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-secondary text-xs fw-bold">ACTUAL VALUE</label>
                            <input type="number" step="0.01" name="actual_value" class="form-control bg-dark text-white border-secondary" placeholder="0.00">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label text-secondary text-xs fw-bold">DESCRIPTION / NOTES *</label>
                        <textarea name="description" class="form-control bg-dark text-white border-secondary" rows="4" required placeholder="Detailed description of the finding..."></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-warning w-100 fw-bold">
                        <i class="fas fa-flag me-2"></i> Add Finding to Draft
                    </button>
                </form>
            </div>
        </div>

        <!-- RIGHT: DRAFT FINDINGS LIST -->
        <div class="col-lg-6">
            <div class="p-4 bg-dark bg-opacity-30 rounded-3">
                <h5 class="fw-bold text-info mb-4">
                    <i class="fas fa-clipboard-list me-2"></i> Draft Findings (<?= count($draftFindings) ?>)
                </h5>
                
                <div style="max-height: 500px; overflow-y: auto;">
                    <?php foreach ($draftFindings as $finding): ?>
                    <div class="mb-3 p-3 bg-dark bg-opacity-50 rounded-3 border border-secondary border-opacity-20">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="status-badge <?= $finding['severity'] === 'critical' ? 'flag-critical' : ($finding['severity'] === 'high' ? 'flag-high' : 'flag-normal') ?>">
                                <?= strtoupper($finding['severity']) ?>
                            </span>
                            <span class="text-xs text-secondary"><?= date('M d, Y', strtotime($finding['audit_date'])) ?></span>
                        </div>
                        <div class="fw-bold text-warning mb-1"><?= ucwords(str_replace('_', ' ', $finding['finding_category'])) ?></div>
                        <div class="text-sm text-white mb-2"><?= htmlspecialchars($finding['description']) ?></div>
                        <div class="text-xs text-secondary">
                            Site: <?= htmlspecialchars($finding['site_name'] ?? 'N/A') ?>
                            <?php if ($finding['variance']): ?>
                                | Variance: <span class="text-danger fw-bold"><?= number_format($finding['variance'], 2) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($draftFindings)): ?>
                        <div class="text-center py-5 text-secondary">
                            <i class="fas fa-inbox fa-3x mb-3 opacity-20"></i>
                            <p>No draft findings yet. Add findings using the form.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- SUBMIT AUDIT REPORT -->
    <?php if (!empty($draftFindings)): ?>
    <div class="mt-4 p-4 bg-danger bg-opacity-10 border border-danger border-opacity-20 rounded-3">
        <h5 class="fw-bold text-danger mb-4">
            <i class="fas fa-paper-plane me-2"></i> Submit Audit Report
        </h5>
        
        <form method="POST">
            <input type="hidden" name="action" value="submit_report">
            
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label text-secondary text-xs fw-bold">PROJECT *</label>
                    <select name="project_id" class="form-select bg-dark text-white border-secondary" required>
                        <option value="">-- Select --</option>
                        <?php foreach ($projects as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['project_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-secondary text-xs fw-bold">SITE *</label>
                    <select name="site_id" class="form-select bg-dark text-white border-secondary" required>
                        <option value="">-- Select --</option>
                        <?php foreach ($sites as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['site_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-secondary text-xs fw-bold">PERIOD START *</label>
                    <input type="date" name="period_start" class="form-control bg-dark text-white border-secondary" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-secondary text-xs fw-bold">PERIOD END *</label>
                    <input type="date" name="period_end" class="form-control bg-dark text-white border-secondary" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-secondary text-xs fw-bold">WORK CATEGORY</label>
                    <input type="text" name="work_category" class="form-control bg-dark text-white border-secondary" placeholder="e.g. Structure">
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label text-secondary text-xs fw-bold">EXECUTIVE SUMMARY *</label>
                <textarea name="summary" class="form-control bg-dark text-white border-secondary" rows="3" required placeholder="High-level summary of audit findings and overall assessment..."></textarea>
            </div>
            
            <div class="mb-4">
                <label class="form-label text-secondary text-xs fw-bold">RECOMMENDATIONS *</label>
                <textarea name="recommendations" class="form-control bg-dark text-white border-secondary" rows="3" required placeholder="Corrective actions and recommendations for management..."></textarea>
            </div>
            
            <div class="alert alert-warning border-0 bg-warning bg-opacity-10 text-warning mb-3">
                <i class="fas fa-lock me-2"></i> <strong>WARNING:</strong> Once submitted, this report cannot be edited. It will be sent to GM and Finance Head for review.
            </div>
            
            <button type="submit" class="btn btn-danger btn-lg w-100 fw-bold">
                <i class="fas fa-paper-plane me-2"></i> Submit Audit Report (<?= count($draftFindings) ?> Findings)
            </button>
        </form>
    </div>
    <?php endif; ?>
</div>
