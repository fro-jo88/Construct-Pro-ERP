<?php
// modules/planning/engineer_dashboard/schedules.php

$site_id = $_GET['site_id'] ?? null;
$db = Database::getInstance();
$user_id = $_SESSION['user_id'];

if (!$site_id) {
    // List assigned sites to choose from
    $assignments = $db->query("SELECT ssa.site_id, s.site_name, p.project_name 
                               FROM site_staff_assignments ssa
                               JOIN sites s ON ssa.site_id = s.id
                               JOIN projects p ON s.project_id = p.id
                               WHERE ssa.user_id = ? AND ssa.status = 'active'", [$user_id])->fetchAll();
    ?>
    <div class="glass-panel">
        <h4 class="fw-bold mb-4">Select Site to Manage Schedules</h4>
        <div class="row g-4">
            <?php foreach ($assignments as $a): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="glass-card p-4 transition-transform hover-scale" style="cursor: pointer; border-bottom: 3px solid #3b82f6;" 
                         onclick="location.href='?module=planning/engineer_dashboard/index&view=schedules&site_id=<?= $a['site_id'] ?>'">
                        <div class="text-xs text-secondary mb-1"><?= htmlspecialchars($a['project_name']) ?></div>
                        <h5 class="fw-bold text-white mb-3"><?= htmlspecialchars($a['site_name']) ?></h5>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-xs"><i class="fas fa-calendar-alt me-2"></i> 4 Schedules</span>
                            <i class="fas fa-chevron-right text-primary"></i>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($assignments)): ?>
                <div class="col-12 text-center py-5">
                    <p class="text-secondary">No active site assignments found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
} else {
    // Site Specific Schedule Center
    $site = $db->query("SELECT s.*, p.project_name FROM sites s JOIN projects p ON s.project_id = p.id WHERE s.id = ?", [$site_id])->fetch();
    $schedules = $db->query("SELECT * FROM schedules WHERE site_id = ? ORDER BY uploaded_at DESC", [$site_id])->fetchAll();
    ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="?module=planning/engineer_dashboard/index&view=schedules" class="text-secondary">Schedules</a></li>
                    <li class="breadcrumb-item active text-white"><?= htmlspecialchars($site['site_name']) ?></li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-0">Schedule Creation Center</h3>
        </div>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#newScheduleModal">
            <i class="fas fa-plus me-2"></i> New Schedule
        </button>
    </div>

    <div class="row g-4">
        <!-- TYPES GRID -->
        <?php 
        $types = [
            'ms_project' => ['label' => 'MS Schedule', 'icon' => 'fa-project-diagram', 'color' => '#3b82f6'],
            'manpower' => ['label' => 'Manpower Schedule', 'icon' => 'fa-users', 'color' => '#f59e0b'],
            'equipment' => ['label' => 'Equipment Schedule', 'icon' => 'fa-truck-pickup', 'color' => '#ef4444'],
            'material' => ['label' => 'Material Schedule', 'icon' => 'fa-boxes', 'color' => '#10b981']
        ];
        
        foreach ($types as $key => $t): 
            $existing = array_filter($schedules, function($s) use ($key) { return $s['schedule_type'] === $key; });
            $latest = !empty($existing) ? reset($existing) : null;
        ?>
            <div class="col-md-6">
                <div class="glass-panel p-4 h-100 border-start border-4" style="border-start-color: <?= $t['color'] ?> !important;">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                 style="width: 48px; height: 48px; background: <?= $t['color'] ?>20; color: <?= $t['color'] ?>;">
                                <i class="fas <?= $t['icon'] ?> fa-lg"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0"><?= $t['label'] ?></h5>
                                <span class="text-xs text-secondary">Site execution timeline & resources</span>
                            </div>
                        </div>
                        <?php if ($latest): ?>
                            <span class="status-badge status-approved">v<?= $latest['version'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="p-3 rounded-3 mb-3" style="background: rgba(255,255,255,0.03);">
                        <?php if ($latest): ?>
                            <div class="d-flex justify-content-between text-sm mb-1">
                                <span class="text-secondary">Last Upload</span>
                                <span class="text-white"><?= date('M d, Y', strtotime($latest['uploaded_at'])) ?></span>
                            </div>
                            <div class="d-flex justify-content-between text-sm">
                                <span class="text-secondary">File Path</span>
                                <span class="text-info"><?= basename($latest['file_path']) ?></span>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-2 text-secondary fst-italic text-sm">No schedule uploaded yet.</div>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary flex-grow-1" onclick="uploadSchedule('<?= $key ?>')">
                            <i class="fas fa-upload me-1"></i> Update
                        </button>
                        <button class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-history"></i>
                        </button>
                        <?php if ($latest): ?>
                             <a href="<?= $latest['file_path'] ?>" class="btn btn-sm btn-outline-success" download>
                                <i class="fas fa-download"></i>
                             </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Upload Modal -->
    <div class="modal fade" id="newScheduleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-dark-eval border-secondary">
                <form id="scheduleForm" method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Create New Site Schedule</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-secondary">Schedule Type</label>
                            <select class="form-select bg-dark text-white border-secondary" name="schedule_type" required>
                                <?php foreach($types as $k => $t): ?>
                                    <option value="<?= $k ?>"><?= $t['label'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary">Upload File (Excel/Project/PDF)</label>
                            <input type="file" class="form-control bg-dark text-white border-secondary" name="schedule_file" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary">Notes for Manager</label>
                            <textarea class="form-control bg-dark text-white border-secondary" rows="3" name="notes"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold">Submit for Validation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
}
?>
