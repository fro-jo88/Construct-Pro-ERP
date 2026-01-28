<?php
// modules/dashboards/widgets/planning_interface.php
require_once __DIR__ . '/../../../includes/Database.php';

$db = Database::getInstance();
$requests = [];

try {
    $requests = $db->query("SELECT pr.*, t.tender_no 
                           FROM planning_requests pr 
                           JOIN tenders t ON pr.tender_id = t.id 
                           ORDER BY pr.created_at DESC LIMIT 5")->fetchAll();
} catch (Exception $e) { /* Ignore */ }

?>
<div class="widget glass-card">
    <div class="widget-header">
        <h3><i class="fas fa-project-diagram text-gold"></i> Planning Integration</h3>
        <button class="btn-primary-sm" onclick="location.href='main.php?module=technical/planning_request'">+ Send New Request</button>
    </div>
    <div class="widget-content">
        <h4 class="small text-dim mb-2">Schedule Tracking</h4>
        <div class="list-group">
            <?php if (empty($requests)): ?>
                <p class="text-dim small p-2">No active planning requests.</p>
            <?php else: ?>
                <?php foreach ($requests as $r): ?>
                    <div class="list-item mb-3 pb-2 border-bottom border-light-subtle">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="font-weight-bold" style="font-size:0.85rem;"><?= $r['tender_no'] ?></span>
                            <span class="status-badge status-<?= strtolower($r['status']) ?>" style="font-size: 0.65rem;"><?= strtoupper($r['status']) ?></span>
                        </div>
                        <div class="d-flex gap-2">
                            <span class="badge badge-secondary small">MS</span>
                            <span class="badge badge-secondary small">Manpower</span>
                            <span class="badge badge-secondary small">Equipment</span>
                        </div>
                        <?php if ($r['status'] === 'completed'): ?>
                            <button class="btn-secondary-sm w-100 mt-2" onclick="location.href='main.php?module=technical/planning_review&id=<?= $r['id'] ?>'">
                                <i class="fas fa-search-plus mr-1"></i> Review Outputs
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
