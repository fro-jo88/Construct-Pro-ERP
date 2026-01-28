<?php
// modules/tender/technical.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/BidManager.php';

AuthManager::requireRole(['TENDER_TECHNICAL', 'GM']);

$db = Database::getInstance();
$msg = '';

// Handle Technical Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_tech') {
    try {
        $bid_id = $_POST['bid_id'];
        $score = $_POST['compliance_score'];
        $details = $_POST['technical_details'];
        
        $stmt = $db->prepare("UPDATE technical_bids SET compliance_score = ?, technical_notes = ?, status = 'completed', updated_at = NOW() WHERE bid_id = ?");
        $stmt->execute([$score, $details, $bid_id]);
        
        // Check if we should advance the main bid status? 
        // For now, let's just mark it as technical-ready in the main bids table if needed
        // but BidManager logic might already handle this.
        
        $msg = "Technical evaluation submitted for Bid #$bid_id";
    } catch (Exception $e) {
        $msg = "Error: " . $e->getMessage();
    }
}

// Fetch bids awaiting technical input
// Usually these are bids in 'DRAFT' or 'NEW' status where technical hasn't been done
$pending_bids = $db->query("
    SELECT b.*, tb.compliance_score, tb.status as tech_status 
    FROM bids b 
    LEFT JOIN technical_bids tb ON b.id = tb.bid_id 
    WHERE b.status = 'DRAFT' OR b.status = 'TECHNICAL_REVIEW'
    ORDER BY b.deadline ASC
")->fetchAll();

?>

<div class="tender-technical">
    <div class="section-header mb-4">
        <h2><i class="fas fa-microchip text-gold"></i> Technical Bid Evaluation</h2>
        <p class="text-dim">Review project specifications and prepare technical compliance scores.</p>
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
                    <th>Tech Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pending_bids)): ?>
                    <tr><td colspan="5" class="text-center py-4">No bids currently awaiting technical evaluation.</td></tr>
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
                        <span class="status-badge <?= $b['tech_status'] ?? 'pending' ?>">
                            <?= strtoupper($b['tech_status'] ?? 'pending') ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn-primary-sm" onclick='openTechModal(<?= json_encode($b) ?>)'>Evaluate</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Technical Modal -->
<div id="techModal" class="modal-overlay" style="display:none;">
    <div class="glass-card modal-content" style="max-width: 600px;">
        <h3 id="modalTitle" class="text-gold">Technical Evaluation</h3>
        <form method="POST" id="techForm">
            <input type="hidden" name="action" value="submit_tech">
            <input type="hidden" name="bid_id" id="modalBidId">
            
            <div class="form-group mb-4">
                <label>Compliance Score (0-100)</label>
                <input type="number" name="compliance_score" id="modalScore" min="0" max="100" required>
            </div>

            <div class="form-group mb-4">
                <label>Technical Details / Methodology Notes</label>
                <textarea name="technical_details" id="modalDetails" rows="5" required></textarea>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary-sm" onclick="closeTechModal()">Cancel</button>
                <button type="submit" class="btn-primary-sm">Submit Evaluation</button>
            </div>
        </form>
    </div>
</div>

<script>
function openTechModal(bid) {
    document.getElementById('modalBidId').value = bid.id;
    document.getElementById('modalTitle').innerText = "Technical Eval: " + bid.tender_no;
    document.getElementById('modalScore').value = bid.compliance_score || 0;
    document.getElementById('techModal').style.display = 'flex';
}
function closeTechModal() {
    document.getElementById('techModal').style.display = 'none';
}
</script>

<style>
.mono { font-family: monospace; }
.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 2000; display: flex; justify-content: center; align-items: center; }
.modal-content { width: 90%; padding: 2rem; }
.form-group label { display: block; margin-bottom: 0.5rem; color: var(--text-dim); text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px; }
input[type="number"], textarea { width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: white; padding: 0.75rem; }
.modal-actions { display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem; }
</style>
