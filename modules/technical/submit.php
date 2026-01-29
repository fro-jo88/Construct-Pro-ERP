<?php
// modules/technical/submit.php
// Technical Bid Submission Module for TECH_BID_MANAGER
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/BidManager.php';

AuthManager::requireRole(['TECH_BID_MANAGER', 'GM', 'SUPER_ADMIN']);
$db = Database::getInstance();

// Get bids ready for submission
$readyBids = $db->query("
    SELECT b.*, tb.compliance_score, tb.status as tech_status, tb.id as tech_bid_id
    FROM bids b 
    JOIN technical_bids tb ON tb.bid_id = b.id
    WHERE tb.compliance_score >= 70
    AND tb.status IN ('draft', 'ready')
    ORDER BY b.deadline ASC
")->fetchAll();

// Get already submitted bids
$submittedBids = $db->query("
    SELECT b.*, tb.compliance_score, tb.status as tech_status
    FROM bids b 
    JOIN technical_bids tb ON tb.bid_id = b.id
    WHERE tb.status = 'submitted'
    ORDER BY tb.updated_at DESC
    LIMIT 10
")->fetchAll();

// Handle submissions
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'submit_technical') {
        $tech_bid_id = $_POST['tech_bid_id'];
        $bid_id = $_POST['bid_id'];
        
        // Fetch compliance score for the manager call
        $stmt = $db->prepare("SELECT compliance_score FROM technical_bids WHERE id = ?");
        $stmt->execute([$tech_bid_id]);
        $score = $stmt->fetchColumn();

        try {
            BidManager::submitTechnical($bid_id, ['compliance_score' => $score], $_SESSION['user_id']);
            $msg = "Technical bid submitted successfully! Workflow sync initiated.";
        } catch (Exception $e) {
            $msg = "Error: " . $e->getMessage();
        }
    }
}
?>

<div class="tech-submit-module">
    <div class="section-header mb-4">
        <h2><i class="fas fa-paper-plane text-gold"></i> Technical Bid Submission</h2>
        <p class="text-dim">Submit completed technical bids for GM review and Financial processing</p>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-success glass-card mb-4" style="color: #00ff64; border-left: 5px solid #00ff64;">
            <i class="fas fa-check-circle"></i> <?= $msg ?>
        </div>
    <?php endif; ?>

    <!-- Ready for Submission -->
    <div class="glass-card mb-4">
        <h3 class="mb-3"><i class="fas fa-check-double text-gold"></i> Ready for Submission</h3>
        <p class="text-dim mb-3">Bids with compliance score ≥ 70% are eligible for submission</p>
        
        <?php if (empty($readyBids)): ?>
            <div style="text-align: center; padding: 2rem; color: var(--text-dim);">
                <i class="fas fa-inbox fa-2x mb-2"></i>
                <p>No bids currently meet the submission threshold.</p>
                <a href="main.php?module=technical/eval" class="btn-secondary-sm" style="text-decoration: none;">
                    <i class="fas fa-drafting-compass"></i> Go to Evaluation
                </a>
            </div>
        <?php else: ?>
            <div style="display: grid; gap: 1rem;">
                <?php foreach ($readyBids as $bid): ?>
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 1.25rem; background: rgba(255,255,255,0.03); border-radius: 12px; border-left: 4px solid #00ff64;">
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <span style="color: var(--gold); font-family: monospace; font-weight: bold;"><?= htmlspecialchars($bid['tender_no']) ?></span>
                                <span style="background: rgba(0,255,100,0.15); color: #00ff64; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem;">
                                    <?= $bid['compliance_score'] ?>% Compliant
                                </span>
                            </div>
                            <div style="font-weight: bold; margin: 0.5rem 0;"><?= htmlspecialchars($bid['title']) ?></div>
                            <div class="text-dim" style="font-size: 0.85rem;">
                                <i class="fas fa-building"></i> <?= htmlspecialchars($bid['client_name']) ?> &nbsp;|&nbsp;
                                <i class="fas fa-clock"></i> Due: <?= date('M d, Y', strtotime($bid['deadline'])) ?>
                            </div>
                        </div>
                        <form method="POST" onsubmit="return confirm('Submit this technical bid for GM review?');">
                            <input type="hidden" name="action" value="submit_technical">
                            <input type="hidden" name="tech_bid_id" value="<?= $bid['tech_bid_id'] ?>">
                            <input type="hidden" name="bid_id" value="<?= $bid['id'] ?>">
                            <button type="submit" class="btn-primary-sm" style="white-space: nowrap;">
                                <i class="fas fa-paper-plane"></i> Submit to GM
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Recently Submitted -->
    <div class="glass-card">
        <h3 class="mb-3"><i class="fas fa-history text-gold"></i> Recently Submitted</h3>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>Tender #</th>
                    <th>Project</th>
                    <th>Compliance</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($submittedBids)): ?>
                    <tr><td colspan="4" style="text-align: center; padding: 2rem;" class="text-dim">No submissions yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($submittedBids as $bid): ?>
                        <tr>
                            <td style="font-family: monospace; color: var(--gold);"><?= htmlspecialchars($bid['tender_no']) ?></td>
                            <td><?= htmlspecialchars($bid['title']) ?></td>
                            <td>
                                <span style="color: #00ff64; font-weight: bold;"><?= $bid['compliance_score'] ?>%</span>
                            </td>
                            <td>
                                <span class="status-badge" style="background: rgba(0,255,100,0.15); color: #00ff64;">
                                    <i class="fas fa-check"></i> SUBMITTED
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
