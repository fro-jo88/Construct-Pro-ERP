<?php
// modules/tender/finance.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/BidManager.php';

AuthManager::requireRole(['FINANCE_BID_MANAGER', 'GM']);

$db = Database::getInstance();
$msg = '';

// Handle Financial Submission (Simplified for this role)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_fin') {
    try {
        $bid_id = $_POST['bid_id'];
        $amount = $_POST['total_amount'];
        
        $stmt = $db->prepare("UPDATE financial_bids SET total_amount = ?, status = 'completed', updated_at = NOW() WHERE bid_id = ?");
        $stmt->execute([$amount, $bid_id]);
        
        $msg = "Financial figures updated for Bid #$bid_id";
    } catch (Exception $e) {
        $msg = "Error: " . $e->getMessage();
    }
}

// Fetch bids awaiting financial input
$pending_bids = $db->query("
    SELECT b.*, fb.total_amount, fb.status as fin_status 
    FROM bids b 
    LEFT JOIN financial_bids fb ON b.id = fb.bid_id 
    WHERE b.status = 'DRAFT' OR b.status = 'FINANCIAL_REVIEW'
    ORDER BY b.deadline ASC
")->fetchAll();

?>

<div class="tender-finance">
    <div class="section-header mb-4">
        <h2><i class="fas fa-file-invoice-dollar text-gold"></i> Financial Bid Estimating</h2>
        <p class="text-dim">Prepare cost estimations and final offer amounts for active tenders.</p>
    </div>

    <?php if ($msg): ?>
        <div class="alert glass-card mb-4"><?= $msg ?></div>
    <?php endif; ?>

    <div class="glass-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Tender #</th>
                    <th>Project / Client</th>
                    <th>Deadline</th>
                    <th>Fin Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pending_bids)): ?>
                    <tr><td colspan="5" class="text-center py-4">No bids currently awaiting financial estimation.</td></tr>
                <?php endif; ?>
                <?php foreach ($pending_bids as $b): ?>
                <tr>
                    <td class="text-gold mono"><?= $b['tender_no'] ?></td>
                    <td>
                        <strong><?= htmlspecialchars($b['title']) ?></strong><br>
                        <small><?= htmlspecialchars($b['client_name']) ?></small>
                    </td>
                    <td><?= date('M d, Y', strtotime($b['deadline'])) ?></td>
                    <td>
                        <span class="status-badge <?= $b['fin_status'] ?? 'pending' ?>">
                            <?= strtoupper($b['fin_status'] ?? 'pending') ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn-primary-sm" onclick='openFinModal(<?= json_encode($b) ?>)'>Estimate Costs</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Finance Modal -->
<div id="finModal" class="modal-overlay" style="display:none;">
    <div class="glass-card modal-content" style="max-width: 600px;">
        <h3 id="modalTitle" class="text-gold">Financial Estimation</h3>
        <form method="POST" id="finForm">
            <input type="hidden" name="action" value="submit_fin">
            <input type="hidden" name="bid_id" id="modalBidId">
            
            <div class="form-group mb-4">
                <label>Total Bid Amount ($)</label>
                <input type="number" name="total_amount" id="modalAmount" step="0.01" required>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary-sm" onclick="closeFinModal()">Cancel</button>
                <button type="submit" class="btn-primary-sm">Save Estimation</button>
            </div>
        </form>
    </div>
</div>

<script>
function openFinModal(bid) {
    document.getElementById('modalBidId').value = bid.id;
    document.getElementById('modalTitle').innerText = "Financial: " + bid.tender_no;
    document.getElementById('modalAmount').value = bid.total_amount || 0;
    document.getElementById('finModal').style.display = 'flex';
}
function closeFinModal() {
    document.getElementById('finModal').style.display = 'none';
}
</script>

<style>
.mono { font-family: monospace; }
.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 2000; display: flex; justify-content: center; align-items: center; }
.modal-content { width: 90%; padding: 2rem; }
.form-group label { display: block; margin-bottom: 0.5rem; color: var(--text-dim); text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px; }
input[type="number"] { width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: white; padding: 0.75rem; }
.modal-actions { display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem; }
</style>
