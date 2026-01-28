<?php
// modules/dashboards/widgets/tech_bid_evaluator.php
require_once __DIR__ . '/../../../includes/Database.php';

$db = Database::getInstance();
$bids = [];

try {
    $bids = $db->query("SELECT tb.*, t.tender_no, t.title 
                        FROM technical_bids tb 
                        JOIN tenders t ON tb.tender_id = t.id 
                        WHERE tb.status = 'draft' 
                        ORDER BY tb.created_at DESC LIMIT 5")->fetchAll();
} catch (Exception $e) { /* Ignore */ }

?>
<div class="widget glass-card">
    <div class="widget-header">
        <h3><i class="fas fa-drafting-compass text-gold"></i> Technical Bid Evaluation</h3>
        <span class="badge badge-info"><?= count($bids) ?> To Evaluate</span>
    </div>
    <div class="widget-content">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Tender #</th>
                    <th>Project Description</th>
                    <th>Specs/Drawings</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($bids)): ?>
                    <tr><td colspan="4" class="text-center text-dim">No tech bids awaiting evaluation.</td></tr>
                <?php else: ?>
                    <?php foreach ($bids as $b): ?>
                        <tr>
                            <td><?= $b['tender_no'] ?></td>
                            <td><?= htmlspecialchars($b['title']) ?></td>
                            <td>
                                <span class="badge badge-outline-secondary small">PDF Specs</span>
                                <span class="badge badge-outline-secondary small">DXF Drawings</span>
                            </td>
                            <td>
                                <button class="btn-primary-sm" onclick="location.href='main.php?module=technical/eval&id=<?= $b['id'] ?>'">
                                    Inspect Specs
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
