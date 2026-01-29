<?php
// modules/site/forman_dashboard/plans.php

$db = Database::getInstance();
$site_id = $site['id'];

// Fetch the 4 most recent approved weekly plans
$plans = $db->query("SELECT * FROM weekly_plans WHERE site_id = ? AND status = 'approved' ORDER BY week_start_date DESC LIMIT 4", [$site_id])->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">Execution Roadmap</h3>
    <span class="text-secondary text-sm">Read-only view from Planning Team</span>
</div>

<?php foreach ($plans as $p): 
    $details = json_decode($p['details'] ?? '{}', true);
    $activities = $details['activities'] ?? [];
?>
    <div class="glass-panel p-4 mb-4 border-start border-4 border-primary">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <span class="text-xs text-secondary font-monospace">WEEK PERIOD</span>
                <h5 class="fw-bold text-white mb-0">
                    <?= date('M d', strtotime($p['week_start_date'])) ?> — <?= date('M d', strtotime($p['week_end_date'])) ?>
                </h5>
            </div>
            <span class="badge bg-primary px-3 py-1">APPROVED</span>
        </div>

        <div class="row g-4">
            <div class="col-md-7">
                <h6 class="text-gold fw-bold mb-3"><i class="fas fa-bullseye me-2"></i> Planned Activities</h6>
                <div class="list-group list-group-flush bg-transparent">
                    <?php if (empty($activities)): ?>
                         <div class="list-group-item bg-transparent border-secondary py-2 text-secondary text-sm italic">
                            No detailed activity breakdown available. Refer to Goals.
                         </div>
                    <?php else: ?>
                        <?php foreach ($activities as $act): ?>
                            <div class="list-group-item bg-transparent border-secondary py-2 d-flex justify-content-between align-items-center">
                                <span class="text-sm text-white"><?= htmlspecialchars($act['desc']) ?></span>
                                <span class="badge bg-dark border border-secondary"><?= $act['target'] ?>% Target</span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="mt-4 bg-dark p-3 rounded-3 border border-secondary">
                    <h6 class="text-xs text-secondary text-uppercase mb-2">Manager's Goals</h6>
                    <p class="text-sm mb-0"><?= nl2br(htmlspecialchars($p['goals'])) ?></p>
                </div>
            </div>

            <div class="col-md-5">
                <div class="bg-dark p-3 rounded-3 h-100 border border-secondary">
                    <h6 class="text-gold fw-bold mb-3"><i class="fas fa-users-cog me-2"></i> Resource Allocation</h6>
                    <div class="mb-3">
                        <label class="text-xs text-secondary d-block">Approved Manpower</label>
                        <span class="h4 fw-bold text-white"><?= $p['planned_labor_count'] ?></span>
                        <span class="text-secondary ms-1">Workers</span>
                    </div>
                    
                    <div class="mb-3">
                        <label class="text-xs text-secondary d-block">Materials Validated</label>
                        <p class="text-xs text-secondary mb-0"><?= nl2br(htmlspecialchars($details['materials'] ?? 'No specific material list attached.')) ?></p>
                    </div>

                    <div>
                        <label class="text-xs text-secondary d-block">Equipment Authorized</label>
                        <p class="text-xs text-secondary mb-0"><?= nl2br(htmlspecialchars($details['equipment'] ?? 'Standard site equipment.')) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?php if (empty($plans)): ?>
    <div class="glass-panel text-center py-5">
        <i class="fas fa-calendar-times fa-3x text-secondary mb-3"></i>
        <h4 class="text-secondary">No Approved Weekly Plans</h4>
        <p class="text-muted">Wait for the Planning Team to publish the execution roadmap for this site.</p>
    </div>
<?php endif; ?>
