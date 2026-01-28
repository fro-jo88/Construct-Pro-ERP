<?php
// modules/bidding/gm_review.php
require_once '../../includes/AuthManager.php';
require_once '../../includes/TenderManager.php';
require_once '../../includes/BidManager.php';

AuthManager::requireRole('gm');

$tenders = TenderManager::getAllTenders(); // Should filter for those with bids in progress/ready
$active_tender_id = $_GET['id'] ?? null;

$fin_bid = null;
$tech_bid = null;
$tender = null;
$submission_allowed = false;

if ($active_tender_id) {
    $tender = TenderManager::getTenderWithBids($active_tender_id);
    $fin_bid = BidManager::getFinancialBid($active_tender_id, 'gm');
    $tech_bid = BidManager::getTechnicalBid($active_tender_id);
    $submission_allowed = TenderManager::allowFinalSubmission($active_tender_id);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['approve_all'])) {
            // Final Approval Logic
            // Update Tenders table status
            // Lock bids
             $db = Database::getInstance();
             $db->prepare("UPDATE tenders SET status = 'submitted' WHERE id = ?")->execute([$active_tender_id]);
             $db->prepare("UPDATE financial_bids SET status = 'approved' WHERE id = ?")->execute([$fin_bid['id']]);
             $db->prepare("UPDATE technical_bids SET status = 'approved' WHERE id = ?")->execute([$tech_bid['id']]);
             header("Location: index.php?module=bidding/gm_review&id=$active_tender_id");
             exit;
        }
        if (isset($_POST['send_query'])) {
             $db = Database::getInstance();
             $target = $_POST['target']; // 'financial' or 'technical'
             $comments = $_POST['comments'];
             
             if ($target === 'financial') {
                 $db->prepare("UPDATE financial_bids SET status = 'gm_query', gm_comments = ? WHERE id = ?")->execute([$comments, $fin_bid['id']]);
             } else {
                 $db->prepare("UPDATE technical_bids SET status = 'gm_query', gm_comments = ? WHERE id = ?")->execute([$comments, $tech_bid['id']]);
             }
             header("Location: index.php?module=bidding/gm_review&id=$active_tender_id");
             exit;
        }
    }
}
?>

<div class="main-content">
    <div class="page-header">
        <h1>GM Tender Review</h1>
    </div>

    <!-- TENDER SELECTOR -->
    <div class="card glass-panel mb-4">
        <div class="card-body py-2">
            <label class="mr-2">Select Tender:</label>
            <?php foreach ($tenders as $t): ?>
                <a href="index.php?module=bidding/gm_review&id=<?= $t['id'] ?>" class="btn btn-sm btn-<?= $t['id'] == $active_tender_id ? 'primary' : 'outline-secondary' ?> mr-2">
                    <?= $t['tender_no'] ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if ($active_tender_id && $tender): ?>
    
    <div class="row">
        <!-- FINANCIAL SIDE -->
        <div class="col-md-6">
            <div class="card glass-panel h-100" style="border-top: 4px solid var(--gold);">
                <div class="card-header d-flex justify-content-between">
                    <h4><i class="fas fa-coins mr-2"></i> Financial Bid</h4>
                    <span class="badge badge-<?= $fin_bid['status'] === 'ready' ? 'success' : 'warning' ?>">
                        <?= strtoupper($fin_bid['status']) ?>
                    </span>
                </div>
                <div class="card-body">
                    <h2 class="text-gold display-4"><?= number_format($fin_bid['profit_margin_percent'], 1) ?>%</h2>
                    <p class="text-muted">PROJECTED MARGIN</p>
                    <hr>
                    <h3 class="mb-4">$<?= number_format($fin_bid['total_amount'], 2) ?></h3>
                    
                    <a href="index.php?module=bidding/financial_dashboard&id=<?= $active_tender_id ?>" class="btn btn-outline-light btn-block">Inspect Details</a>
                </div>
            </div>
        </div>

        <!-- TECHNICAL SIDE -->
        <div class="col-md-6">
            <div class="card glass-panel h-100" style="border-top: 4px solid #00ff64;">
                <div class="card-header d-flex justify-content-between">
                    <h4><i class="fas fa-cogs mr-2"></i> Technical Bid</h4>
                    <span class="badge badge-<?= $tech_bid['status'] === 'ready' ? 'success' : 'warning' ?>">
                        <?= strtoupper($tech_bid['status']) ?>
                    </span>
                </div>
                <div class="card-body">
                    <h2 class="text-success display-4"><?= $tech_bid['compliance_score'] ?>/100</h2>
                    <p class="text-muted">COMPLIANCE SCORE</p>
                    <hr>
                    <!-- Planning Status -->
                    <p class="mb-4">Planning Inputs: <span class="badge badge-info">Verified</span></p>

                    <a href="index.php?module=bidding/technical_dashboard&id=<?= $active_tender_id ?>" class="btn btn-outline-light btn-block">Inspect Details</a>
                </div>
            </div>
        </div>
    </div>

    <!-- GM ACTION BAR -->
    <div class="card glass-panel mt-4 bg-dark-gradient">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h4>Final Decision</h4>
                <p class="mb-0 text-muted">Both bids must be marked READY to approve.</p>
            </div>
            <div>
                <?php if ($submission_allowed && $tender['status'] !== 'submitted'): ?>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="approve_all" value="1">
                        <button type="submit" class="btn btn-success btn-lg shadow-glow">
                            <i class="fas fa-check-circle mr-2"></i> Approve & Submit Bid
                        </button>
                    </form>
                    <button class="btn btn-danger btn-lg ml-2" onclick="document.getElementById('queryModal').style.display='block'">Reject / Query</button>
                <?php else: ?>
                    <?php if ($tender['status'] === 'submitted'): ?>
                        <button class="btn btn-secondary btn-lg" disabled>Bid Submitted</button>
                    <?php else: ?>
                        <button class="btn btn-secondary btn-lg" disabled title="Waiting for Teams">Awaiting Readiness</button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- QUERY MODAL -->
    <div id="queryModal" class="modal" style="display:none; background:rgba(0,0,0,0.8); position:fixed; top:0; left:0; width:100%; height:100%; z-index:1000;">
        <div class="modal-dialog" style="margin-top:10%;">
            <div class="modal-content glass-panel" style="background: #1a1a2e; border: 1px solid var(--gold);">
                <div class="modal-header">
                    <h4 class="text-gold">Send Query / Return Bid</h4>
                    <button type="button" class="close text-white" onclick="document.getElementById('queryModal').style.display='none'">&times;</button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <input type="hidden" name="send_query" value="1">
                        <div class="form-group">
                            <label>Target Team</label>
                            <select name="target" class="form-control bg-dark text-white">
                                <option value="financial">Financial Team</option>
                                <option value="technical">Technical Team</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Comments / Reason</label>
                            <textarea name="comments" class="form-control bg-dark text-white" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-warning btn-block">Send Query & Re-Open</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php endif; ?>
</div>

<style>
.bg-dark-gradient {
    background: linear-gradient(45deg, rgba(0,0,0,0.6), rgba(20,20,30,0.8));
}
.shadow-glow {
    box-shadow: 0 0 15px rgba(0, 255, 100, 0.4);
}
</style>
