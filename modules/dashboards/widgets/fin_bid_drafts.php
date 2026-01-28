<?php
// modules/dashboards/widgets/fin_bid_drafts.php
require_once __DIR__ . '/../../../includes/Database.php';

$db = Database::getInstance();
$drafts = [];

try {
    $drafts = $db->query("SELECT fb.*, t.tender_no, t.title 
                          FROM financial_bids fb 
                          JOIN tenders t ON fb.tender_id = t.id 
                          WHERE fb.status = 'draft' 
                          ORDER BY fb.updated_at DESC LIMIT 5")->fetchAll();
} catch (Exception $e) { /* Ignore */ }

?>
<div class="widget glass-card">
    <div class="widget-header">
        <h3><i class="fas fa-file-contract text-gold"></i> Financial Bid Drafts</h3>
        <button class="btn-primary-sm" onclick="location.href='main.php?module=finance/fin_bid_drafts&action=new'">+ New Draft</button>
    </div>
    <div class="widget-content">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Tender</th>
                    <th>Subtotal</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($drafts)): ?>
                    <tr><td colspan="3" class="text-center text-dim">No drafts in progress.</td></tr>
                <?php else: ?>
                    <?php foreach ($drafts as $d): ?>
                        <tr>
                            <td><?= $d['tender_no'] ?></td>
                            <td class="text-gold"><?= number_format($d['total_amount'], 2) ?></td>
                            <td>
                                <button class="btn-secondary-sm" onclick="location.href='main.php?module=finance/fin_bid_drafts&id=<?= $d['id'] ?>'">Edit</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
