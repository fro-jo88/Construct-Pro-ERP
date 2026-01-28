<?php
// modules/dashboards/widgets/tech_bid_submission.php
require_once __DIR__ . '/../../../includes/Database.php';

$db = Database::getInstance();
$ready_bids = [];

try {
    // A bid is ready for GM if planning is complete or it's been marked as 'ready'
    $ready_bids = $db->query("SELECT tb.*, t.tender_no 
                             FROM technical_bids tb 
                             JOIN tenders t ON tb.tender_id = t.id 
                             WHERE tb.status = 'ready' 
                             ORDER BY tb.created_at DESC")->fetchAll();
} catch (Exception $e) { /* Ignore */ }

?>
<div class="widget glass-card">
    <div class="widget-header">
        <h3><i class="fas fa-paper-plane text-gold"></i> Ready for GM Submission</h3>
    </div>
    <div class="widget-content">
        <?php if (empty($ready_bids)): ?>
            <div class="p-3 text-center border rounded" style="background: rgba(255,255,255,0.02); border-style: dashed !important;">
                <i class="fas fa-hourglass-half text-dim mb-2 d-block"></i>
                <p class="small text-dim">No bids are currently ready for final submission.</p>
            </div>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($ready_bids as $rb): ?>
                    <div class="list-item d-flex justify-content-between align-items-center mb-2 p-2 rounded" style="background: rgba(0, 255, 100, 0.03);">
                        <div>
                            <span class="font-weight-bold"><?= $rb['tender_no'] ?></span>
                            <div class="small text-dim">Schedules Attached</div>
                        </div>
                        <button class="btn-primary-sm" onclick="location.href='main.php?module=technical/submit_gm&id=<?= $rb['id'] ?>'">
                            Submit to GM
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="mt-4">
            <h4 class="small text-dim mb-2 font-weight-bold">Recent History</h4>
            <div style="font-size: 0.75rem;">
                <div class="d-flex justify-content-between mb-1 opacity-75">
                    <span>T-2025-042 Submitted</span>
                    <span class="text-success"><i class="fas fa-check"></i></span>
                </div>
                <div class="d-flex justify-content-between opacity-50">
                    <span>T-2025-015 Reviewed</span>
                    <span class="text-dim">Pending GM</span>
                </div>
            </div>
        </div>
    </div>
</div>
