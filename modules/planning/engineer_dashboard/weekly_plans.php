<?php
// modules/planning/engineer_dashboard/weekly_plans.php

$db = Database::getInstance();
$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? 'list';

if ($action === 'create' || $action === 'edit') {
    $plan_id = $_GET['id'] ?? null;
    $plan = $plan_id ? $db->query("SELECT * FROM weekly_plans WHERE id = ?", [$plan_id])->fetch() : null;
    $sites = $db->query("SELECT s.id, s.site_name FROM sites s 
                         JOIN site_staff_assignments ssa ON s.id = ssa.site_id 
                         WHERE ssa.user_id = ? AND ssa.status = 'active'", [$user_id])->fetchAll();
    
    // Decode JSON parts if editing
    $details = $plan ? json_decode($plan['details'] ?? '{}', true) : [];
    ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0"><?= $plan_id ? 'Edit' : 'Create' ?> Weekly Execution Plan</h3>
        <a href="?module=planning/engineer_dashboard/index&view=weekly_plans" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-times me-2"></i> Cancel
        </a>
    </div>

    <form method="POST" action="modules/planning/engineer_dashboard/save_plan.php">
        <input type="hidden" name="plan_id" value="<?= $plan_id ?>">
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="glass-panel p-4 h-100">
                    <h5 class="fw-bold mb-3 text-primary">General Info</h5>
                    <div class="mb-3">
                        <label class="form-label text-secondary">Project Site</label>
                        <select class="form-select bg-dark text-white border-secondary" name="site_id" required>
                            <?php foreach ($sites as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= ($plan && $plan['site_id'] == $s['id']) ? 'selected' : '' ?>><?= htmlspecialchars($s['site_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label text-secondary">Start Date</label>
                            <input type="date" class="form-control bg-dark text-white border-secondary" name="week_start_date" value="<?= $plan['week_start_date'] ?? date('Y-m-d', strtotime('next Monday')) ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-secondary">End Date</label>
                            <input type="date" class="form-control bg-dark text-white border-secondary" name="week_end_date" value="<?= $plan['week_end_date'] ?? date('Y-m-d', strtotime('next Sunday')) ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary">Main Goals for the Week</label>
                        <textarea class="form-control bg-dark text-white border-secondary" name="goals" rows="4" placeholder="Outline primary objectives..."><?= htmlspecialchars($plan['goals'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="glass-panel p-4">
                    <ul class="nav nav-pills mb-4" id="planTab" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" id="activities-tab" data-bs-toggle="pill" data-bs-target="#activities">Activities</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="resources-tab" data-bs-toggle="pill" data-bs-target="#resources">Resources</button>
                        </li>
                    </ul>

                    <div class="tab-content" id="planTabContent">
                        <!-- Activities -->
                        <div class="tab-pane fade show active" id="activities">
                            <h6 class="text-secondary mb-3">Planned Activities & Milestone Integration</h6>
                            <div id="activity-rows">
                                <?php 
                                $actv = $details['activities'] ?? [['desc' => '', 'target' => '']];
                                foreach ($actv as $i => $a): ?>
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control bg-dark text-white border-secondary" name="act_desc[]" placeholder="Activity description" value="<?= htmlspecialchars($a['desc']) ?>">
                                        <input type="text" class="form-control bg-dark text-white border-secondary" name="act_target[]" placeholder="Target %" style="max-width: 100px;" value="<?= htmlspecialchars($a['target']) ?>">
                                        <button class="btn btn-outline-danger" type="button" onclick="this.parentElement.remove()"><i class="fas fa-trash"></i></button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-xs btn-outline-primary mt-2" onclick="addActivity()">+ Add Activity</button>
                        </div>

                        <!-- Resources -->
                        <div class="tab-pane fade" id="resources">
                             <div class="row g-3">
                                <div class="col-md-6">
                                    <h6 class="text-secondary mb-2">Labor Distribution</h6>
                                    <input type="number" class="form-control bg-dark text-white border-secondary mb-2" name="planned_labor_count" placeholder="Total manpower" value="<?= $plan['planned_labor_count'] ?? '' ?>">
                                    <textarea class="form-control bg-dark text-white border-secondary text-sm" name="labor_notes" rows="2" placeholder="Skilled/Unskilled breakdown..."><?= htmlspecialchars($details['labor_notes'] ?? '') ?></textarea>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-secondary mb-2">Required Materials</h6>
                                    <textarea class="form-control bg-dark text-white border-secondary text-sm" name="material_reqs" rows="4" placeholder="Cement: 100 bags, Sand: 5 loads..."><?= htmlspecialchars($details['materials'] ?? '') ?></textarea>
                                </div>
                                <div class="col-md-12">
                                    <h6 class="text-secondary mb-2">Equipment Usage</h6>
                                    <textarea class="form-control bg-dark text-white border-secondary text-sm" name="equipment_usage" rows="2" placeholder="Excavator (2 days), Tower Crane (Full week)..."><?= htmlspecialchars($details['equipment'] ?? '') ?></textarea>
                                </div>
                             </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-top border-secondary text-end">
                        <button type="submit" name="status" value="draft" class="btn btn-secondary me-2">Save as Draft</button>
                        <button type="submit" name="status" value="submitted_to_manager" class="btn btn-primary fw-bold">Submit to Planning Manager</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
    function addActivity() {
        const container = document.getElementById('activity-rows');
        const div = document.createElement('div');
        div.className = 'input-group mb-2';
        div.innerHTML = `
            <input type="text" class="form-control bg-dark text-white border-secondary" name="act_desc[]" placeholder="Activity description">
            <input type="text" class="form-control bg-dark text-white border-secondary" name="act_target[]" placeholder="Target %" style="max-width: 100px;">
            <button class="btn btn-outline-danger" type="button" onclick="this.parentElement.remove()"><i class="fas fa-trash"></i></button>
        `;
        container.appendChild(div);
    }
    </script>

<?php 
} else { 
    // List View
    $plans = $db->query("SELECT wp.*, s.site_name FROM weekly_plans wp
                         JOIN sites s ON wp.site_id = s.id
                         JOIN site_staff_assignments ssa ON s.id = ssa.site_id
                         WHERE ssa.user_id = ? ORDER BY wp.week_start_date DESC", [$user_id])->fetchAll();
?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">Weekly Execution Plans</h3>
        <a href="?module=planning/engineer_dashboard/index&view=weekly_plans&action=create" class="btn btn-primary">
            <i class="fas fa-calendar-plus me-2"></i> Create Weekly Plan
        </a>
    </div>

    <div class="glass-panel">
        <table class="table table-hover align-middle custom-dark-table">
            <thead class="text-secondary text-xs">
                <tr>
                    <th>SITE</th>
                    <th>PERIOD</th>
                    <th>LABOR</th>
                    <th>STATUS</th>
                    <th class="text-end">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($plans as $p): ?>
                <tr>
                    <td><div class="fw-bold"><?= htmlspecialchars($p['site_name']) ?></div></td>
                    <td>
                        <div class="text-sm"><?= date('M d', strtotime($p['week_start_date'])) ?> — <?= date('M d', strtotime($p['week_end_date'])) ?></div>
                    </td>
                    <td><?= $p['planned_labor_count'] ?> Workers</td>
                    <td>
                        <?php 
                        $cls = 'status-draft';
                        if ($p['status'] == 'approved') $cls = 'status-approved';
                        if ($p['status'] == 'submitted_to_manager') $cls = 'status-submitted';
                        ?>
                        <span class="status-badge <?= $cls ?>"><?= str_replace('_', ' ', $p['status']) ?></span>
                    </td>
                    <td class="text-end">
                        <a href="?module=planning/engineer_dashboard/index&view=weekly_plans&action=edit&id=<?= $p['id'] ?>" class="btn btn-xs btn-icon text-primary"><i class="fas fa-edit"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($plans)): ?>
                    <tr><td colspan="5" class="text-center py-5 text-secondary">No weekly plans found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php } ?>
