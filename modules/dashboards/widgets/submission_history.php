<?php
// modules/dashboards/widgets/submission_history.php
require_once __DIR__ . '/../../../includes/Database.php';

$db = Database::getInstance();
$history = [];

try {
    $history = $db->query("SELECT fb.*, t.tender_no 
                           FROM financial_bids fb 
                           JOIN tenders t ON fb.tender_id = t.id 
                           WHERE fb.status != 'draft' 
                           ORDER BY fb.updated_at DESC LIMIT 5")->fetchAll();
} catch (Exception $e) { /* Ignore */ }

?>
<div class="widget glass-card">
    <div class="widget-header">
        <h3><i class="fas fa-history text-gold"></i> Submission History</h3>
    </div>
    <div class="widget-content">
        <div class="timeline">
            <?php if (empty($history)): ?>
                <p class="text-dim small">No submissions yet.</p>
            <?php else: ?>
                <?php foreach ($history as $h): ?>
                    <div class="timeline-item d-flex justify-content-between mb-2 pb-2" style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <div>
                            <div class="small font-weight-bold"><?= $h['tender_no'] ?></div>
                            <div class="small text-dim"><?= date('M d, Y', strtotime($h['updated_at'])) ?></div>
                        </div>
                        <div class="text-right">
                            <span class="status-badge status-<?= strtolower($h['status']) ?>" style="font-size: 0.65rem;"><?= strtoupper($h['status']) ?></span>
                            <?php if ($h['status'] === 'gm_query' || $h['status'] === 'rejected'): ?>
                                <div class="small text-danger" style="font-size: 0.6rem;">Check Comments</div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <button class="btn-secondary-sm w-100 mt-2" onclick="location.href='main.php?module=finance/history'">View Full History</button>
    </div>
</div>
