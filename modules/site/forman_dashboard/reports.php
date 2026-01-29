<?php
// modules/site/forman_dashboard/reports.php

$db = Database::getInstance();
$user_id = $_SESSION['user_id'];
$site_id = $site['id']; // From index.php
$action = $_GET['action'] ?? 'list';

if ($action === 'new' || $action === 'edit') {
    $report_id = $_GET['id'] ?? null;
    $report = $report_id ? $db->query("SELECT * FROM daily_site_reports WHERE id = ?", [$report_id])->fetch() : null;
    $is_submitted = ($report && $report['status'] !== 'draft');

    // Rule: One report per site per day (Only check if creating new)
    if (!$report_id) {
        $existingCount = $db->query("SELECT COUNT(*) FROM daily_site_reports WHERE site_id = ? AND report_date = ?", [$site_id, date('Y-m-d')])->fetchColumn();
        if ($existingCount > 0) {
            echo '<div class="alert alert-warning shadow-lg border-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i> Report already exists for today. 
                    <a href="?module=site/forman_dashboard/index&view=reports" class="alert-link">Return to list</a>
                  </div>';
            return;
        }
    }
    ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0"><?= $report_id ? 'Review' : 'New' ?> Daily Progress Report</h3>
        <a href="?module=site/forman_dashboard/index&view=reports" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-times me-2"></i> Cancel
        </a>
    </div>

    <form method="POST" action="modules/site/forman_dashboard/save_report.php" enctype="multipart/form-data">
        <input type="hidden" name="report_id" value="<?= $report_id ?>">
        <input type="hidden" name="site_id" value="<?= $site_id ?>">

        <div class="row g-4">
            <div class="col-md-8">
                <div class="glass-panel p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Date</label>
                            <input type="date" class="form-control bg-dark text-white border-secondary" name="report_date" value="<?= $report['report_date'] ?? date('Y-m-d') ?>" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Labor Count</label>
                            <input type="number" class="form-control bg-dark text-white border-secondary" name="labor_count" value="<?= $report['labor_count'] ?? '' ?>" <?= $is_submitted ? 'readonly' : '' ?> required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Actual Progress %</label>
                            <div class="input-group">
                                <input type="number" class="form-control bg-dark text-white border-secondary" name="progress_percent" min="0" max="100" value="<?= $report['progress_percent'] ?? '' ?>" <?= $is_submitted ? 'readonly' : '' ?> required>
                                <span class="input-group-text bg-dark border-secondary text-secondary">%</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-secondary">Actual Work Completed Today</label>
                        <textarea class="form-control bg-dark text-white border-secondary" name="actual_work" rows="4" placeholder="Detail the specific activities completed..." <?= $is_submitted ? 'readonly' : '' ?> required><?= htmlspecialchars($report['actual_work'] ?? '') ?></textarea>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Equipment Used</label>
                            <textarea class="form-control bg-dark text-white border-secondary" name="equipment_used" rows="2" placeholder="Cranes, Trucks, etc." <?= $is_submitted ? 'readonly' : '' ?>><?= htmlspecialchars($report['equipment_used'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Materials Consumed Today</label>
                            <textarea class="form-control bg-dark text-white border-secondary" name="material_used" rows="2" placeholder="Cement (bags), Rebar (tons)..." <?= $is_submitted ? 'readonly' : '' ?>><?= htmlspecialchars($report['material_used'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label text-secondary">Blockers / Delays / Safety Notes</label>
                        <textarea class="form-control bg-dark text-white border-secondary border-danger" name="blockers" rows="3" placeholder="Weather, material shortages, incidents..." <?= $is_submitted ? 'readonly' : '' ?>><?= htmlspecialchars($report['blockers'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="glass-panel p-4 h-100">
                    <h6 class="fw-bold mb-3 text-gold"><i class="fas fa-camera me-2"></i> Site Photos</h6>
                    <div class="mb-4" style="background: rgba(0,0,0,0.2); border-radius: 12px; height: 150px; display: flex; align-items: center; justify-content: center; border: 2px dashed rgba(255,255,255,0.1);">
                        <div class="text-center">
                            <i class="fas fa-image fa-2x text-secondary mb-2"></i>
                            <p class="text-xs text-secondary">Upload actual work photos</p>
                        </div>
                    </div>
                    <?php if (!$is_submitted): ?>
                        <input type="file" class="form-control bg-dark text-white border-secondary text-xs" name="site_photos[]" multiple>
                    <?php endif; ?>

                    <div class="mt-auto pt-4 border-top border-secondary">
                        <div class="d-grid gap-2">
                            <?php if (!$is_submitted): ?>
                                <button type="submit" name="status" value="submitted_to_gm" class="btn btn-warning fw-bold text-dark">
                                    <i class="fas fa-paper-plane me-2"></i> Submit to GM
                                </button>
                                <button type="submit" name="status" value="draft" class="btn btn-outline-secondary btn-sm">
                                    Save as Draft
                                </button>
                            <?php else: ?>
                                <div class="alert alert-success py-2 text-center text-xs fw-bold">
                                    <i class="fas fa-lock"></i> SUBMITTED & LOCKED
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <?php
} else {
    // List View
    $reports = $db->query("SELECT * FROM daily_site_reports WHERE site_id = ? ORDER BY report_date DESC LIMIT 30", [$site_id])->fetchAll();
    ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">Daily Site Reports</h3>
        <a href="?module=site/forman_dashboard/index&view=reports&action=new" class="btn btn-warning fw-bold text-dark">
            <i class="fas fa-plus-circle me-1"></i> New Daily Report
        </a>
    </div>

    <div class="glass-panel">
        <table class="table table-hover align-middle custom-dark-table">
            <thead class="text-secondary text-xs">
                <tr>
                    <th>DATE</th>
                    <th>PROGRESS</th>
                    <th>LABOR</th>
                    <th>STATUS</th>
                    <th class="text-end">ACTION</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reports as $r): ?>
                <tr>
                    <td><div class="fw-bold text-white"><?= date('M d, Y', strtotime($r['report_date'])) ?></div></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                             <div class="progress flex-grow-1" style="height: 6px; background: rgba(255,255,255,0.05); max-width: 100px;">
                                <div class="progress-bar bg-warning" style="width: <?= $r['progress_percent'] ?>%"></div>
                             </div>
                             <span class="text-xs"><?= $r['progress_percent'] ?>%</span>
                        </div>
                    </td>
                    <td><?= $r['labor_count'] ?> Workers</td>
                    <td>
                        <?php 
                        $cls = 'status-draft';
                        if ($r['status'] == 'approved') $cls = 'status-approved';
                        if ($r['status'] == 'submitted_to_gm') $cls = 'status-submitted';
                        ?>
                        <span class="status-badge <?= $cls ?>"><?= str_replace('_', ' ', $r['status']) ?></span>
                    </td>
                    <td class="text-end">
                        <a href="?module=site/forman_dashboard/index&view=reports&action=edit&id=<?= $r['id'] ?>" class="btn btn-xs btn-icon"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($reports)): ?>
                    <tr><td colspan="5" class="text-center py-5 text-secondary">No reports submitted yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php } ?>
