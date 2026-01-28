<?php
// modules/gm/site_reports.php
require_once '../../includes/AuthManager.php';
require_once '../../includes/ForemanManager.php';

// Allow GM or Audit
if (!isset($_SESSION['role_code']) || (!in_array($_SESSION['role_code'], ['GM', 'AUDIT', 'ADMIN']))) {
    header("Location: unauthorized.php");
    exit;
}

$reports = ForemanManager::getAllReports();
?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-gold">Site Daily Reports</h2>
        <div>
            <button class="btn btn-outline-light btn-sm"><i class="fas fa-filter"></i> Filter Date</button>
            <button class="btn btn-outline-light btn-sm"><i class="fas fa-download"></i> Export CSV</button>
        </div>
    </div>

    <div class="row">
        <?php foreach ($reports as $r): ?>
        <div class="col-md-6 mb-4">
            <div class="card glass-panel h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 text-white"><?= htmlspecialchars($r['site_name']) ?></h5>
                        <small class="text-muted">Foreman: <?= htmlspecialchars($r['foreman_name']) ?></small>
                    </div>
                    <div class="text-right">
                        <span class="badge badge-info"><?= date('M d', strtotime($r['report_date'])) ?></span>
                        <div class="small mt-1 text-gold"><?= $r['progress_percent'] ?>% Progress</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong class="text-muted text-uppercase small">Actual Work:</strong>
                        <p class="mb-1 text-white"><?= htmlspecialchars(substr($r['actual_work'], 0, 100)) ?>...</p>
                    </div>

                    <?php if ($r['blockers']): ?>
                    <div class="alert alert-danger p-2 mb-2">
                        <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($r['blockers']) ?>
                    </div>
                    <?php endif; ?>

                    <?php 
                        $mats = json_decode($r['material_used'], true);
                        if (!empty($mats)): 
                    ?>
                    <div class="mt-2">
                        <strong class="text-muted text-uppercase small">Materials:</strong>
                        <div class="d-flex flex-wrap">
                            <?php foreach($mats as $m): ?>
                                <span class="badge badge-secondary mr-1 mb-1"><?= $m['qty'] ?>x <?= $m['item'] ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-transparent border-top border-secondary">
                    <div class="d-flex justify-content-between text-muted small">
                        <span><i class="fas fa-users"></i> <?= $r['labor_count'] ?> Workers</span>
                        <span>Entry: <?= date('H:i', strtotime($r['created_at'])) ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if (empty($reports)): ?>
        <div class="col-12 text-center text-muted py-5">
            <i class="fas fa-clipboard-list fa-3x mb-3"></i>
            <h4>No reports submitted yet.</h4>
        </div>
        <?php endif; ?>
    </div>
</div>
