<?php
// modules/dashboards/widgets/finance_bid_review.php
require_once __DIR__ . '/../../../includes/Database.php';

$db = Database::getInstance();
$bids = [];

try {
    // Join with tenders to get project info
    $bids = $db->query("SELECT fb.*, t.tender_no, t.title 
                        FROM financial_bids fb 
                        JOIN tenders t ON fb.tender_id = t.id 
                        WHERE fb.status = 'ready' 
                        ORDER BY fb.created_at DESC LIMIT 5")->fetchAll();
} catch (Exception $e) { /* Ignore */ }

?>
<div class="widget glass-card">
    <div class="widget-header">
        <h3><i class="fas fa-file-invoice-dollar text-gold"></i> Financial Bid Review</h3>
        <span class="badge badge-warning"><?= count($bids) ?> Pending Head Review</span>
    </div>
    <div class="widget-content">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Tender</th>
                    <th>Total (ETB)</th>
                    <th>Margin</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($bids)): ?>
                    <tr><td colspan="4" class="text-center text-dim">No bids awaiting your review.</td></tr>
                <?php else: ?>
                    <?php foreach ($bids as $b): ?>
                        <tr>
                            <td>
                                <div style="font-weight:bold;"><?= $b['tender_no'] ?></div>
                                <div style="font-size:0.75rem; color:var(--text-dim);"><?= htmlspecialchars($b['title']) ?></div>
                            </td>
                            <td class="text-gold"><?= number_format($b['total_amount'], 2) ?></td>
                            <td><?= $b['profit_margin_percent'] ?>%</td>
                            <td>
                                <button class="btn-primary-sm" onclick="location.href='main.php?module=finance/bid_review&id=<?= $b['id'] ?>'">
                                    Inspect & Approve
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
