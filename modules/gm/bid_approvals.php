<?php
// modules/gm/bid_approvals.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/BidManager.php';
require_once __DIR__ . '/../../core/RoleGuard.php';

RoleGuard::requireAccess('gm/*');

$bids = BidManager::getBidsForGM();
?>

<div class="gm-approvals">
    <div class="section-header mb-4">
        <h2><i class="fas fa-check-double text-gold"></i> Final Bid Approvals</h2>
        <p class="text-dim">Only bids with completed Technical & Financial reviews appear here.</p>
    </div>

    <div class="glass-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Tender #</th>
                    <th>Bid Title</th>
                    <th>Client</th>
                    <th>Technical Score</th>
                    <th>Financial Amount</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($bids)): ?>
                    <tr><td colspan="6" style="text-align:center; padding:3rem;" class="text-dim">No bids currently ready for final approval.</td></tr>
                <?php else: ?>
                    <?php foreach ($bids as $b): 
                        $db = Database::getInstance();
                        $tech = $db->query("SELECT compliance_score FROM technical_bids WHERE bid_id = {$b['id']}")->fetch();
                        $fin = $db->query("SELECT total_amount FROM financial_bids WHERE bid_id = {$b['id']}")->fetch();
                    ?>
                        <tr>
                            <td class="text-gold" style="font-family:monospace;"><?= $b['tender_no'] ?></td>
                            <td><strong><?= htmlspecialchars($b['title']) ?></strong></td>
                            <td class="text-dim"><?= htmlspecialchars($b['client_name']) ?></td>
                            <td><span style="color:#00ff64;"><?= $tech['compliance_score'] ?>%</span></td>
                            <td><span style="color:var(--gold);">$<?= number_format($fin['total_amount'], 2) ?></span></td>
                            <td style="text-align:right;">
                                <button class="btn-primary-sm" style="background:#00ff64; color:black;">Approve Bid</button>
                                <button class="btn-secondary-sm" style="color:#ff4444; border-color:#ff4444;">Reject</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
