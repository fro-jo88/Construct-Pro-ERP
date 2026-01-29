<?php
// modules/technical/eval.php
// Technical Bid Evaluation Module for TECH_BID_MANAGER
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/BidManager.php';

AuthManager::requireRole(['TECH_BID_MANAGER', 'GM', 'SUPER_ADMIN']);
$db = Database::getInstance();

// Get all bids that need technical evaluation
$bids = $db->query("
    SELECT b.*, tb.status as tech_status, tb.compliance_score, tb.id as tech_bid_id
    FROM bids b 
    LEFT JOIN technical_bids tb ON tb.bid_id = b.id
    WHERE b.status IN ('DRAFT', 'TECHNICAL_COMPLETED')
    ORDER BY b.deadline ASC
")->fetchAll();

// Handle form submissions
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_compliance') {
        $tech_bid_id = $_POST['tech_bid_id'];
        $score = (int)$_POST['compliance_score'];
        
        $stmt = $db->prepare("UPDATE technical_bids SET compliance_score = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$score, $tech_bid_id]);
        
        $msg = "Compliance score updated successfully.";
    }
}
?>

<div class="tech-eval-module">
    <div class="section-header mb-4" style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h2><i class="fas fa-drafting-compass text-gold"></i> Bid Evaluation Center</h2>
            <p class="text-dim">Assess technical compliance and readiness for each active bid</p>
        </div>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-success glass-card mb-4" style="color: #00ff64; border-left: 5px solid #00ff64;">
            <i class="fas fa-check-circle"></i> <?= $msg ?>
        </div>
    <?php endif; ?>

    <!-- Evaluation Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 1.5rem;">
        <?php if (empty($bids)): ?>
            <div class="glass-card" style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                <i class="fas fa-clipboard-check fa-3x text-dim mb-3"></i>
                <p class="text-dim">No bids currently require technical evaluation.</p>
            </div>
        <?php else: ?>
            <?php foreach ($bids as $bid): ?>
                <div class="glass-card" style="border-left: 4px solid <?= $bid['tech_status'] === 'ready' ? '#00ff64' : 'var(--gold)' ?>;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                        <div>
                            <h4 style="margin: 0; color: var(--gold);"><?= htmlspecialchars($bid['tender_no']) ?></h4>
                            <p style="margin: 0.25rem 0; font-weight: bold;"><?= htmlspecialchars($bid['title']) ?></p>
                            <small class="text-dim"><?= htmlspecialchars($bid['client_name']) ?></small>
                        </div>
                        <span class="status-badge <?= $bid['status'] ?>"><?= $bid['status'] ?></span>
                    </div>

                    <!-- Deadline Warning -->
                    <?php 
                    $deadline = new DateTime($bid['deadline']);
                    $now = new DateTime();
                    $diff = $now->diff($deadline);
                    $daysLeft = $diff->invert ? 0 : $diff->days;
                    ?>
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; color: <?= $daysLeft < 7 ? '#ff4444' : 'var(--text-dim)' ?>;">
                        <i class="fas fa-clock"></i>
                        <span><?= $daysLeft ?> days until deadline (<?= $deadline->format('M d, Y') ?>)</span>
                    </div>

                    <!-- Compliance Score -->
                    <div style="background: rgba(255,255,255,0.03); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                        <label class="text-dim" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Compliance Score</label>
                        <div style="display: flex; align-items: center; gap: 1rem; margin-top: 0.5rem;">
                            <div style="flex: 1; background: rgba(255,255,255,0.1); height: 10px; border-radius: 5px; overflow: hidden;">
                                <div style="width: <?= $bid['compliance_score'] ?? 0 ?>%; height: 100%; background: linear-gradient(90deg, #ff4444, #ffcc00, #00ff64); transition: width 0.3s;"></div>
                            </div>
                            <span style="font-size: 1.25rem; font-weight: bold; color: <?= ($bid['compliance_score'] ?? 0) >= 80 ? '#00ff64' : (($bid['compliance_score'] ?? 0) >= 50 ? 'var(--gold)' : '#ff4444') ?>;">
                                <?= $bid['compliance_score'] ?? 0 ?>%
                            </span>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <?php if ($bid['tech_bid_id']): ?>
                    <form method="POST" style="display: flex; gap: 0.5rem; align-items: center;">
                        <input type="hidden" name="action" value="update_compliance">
                        <input type="hidden" name="tech_bid_id" value="<?= $bid['tech_bid_id'] ?>">
                        <input type="number" name="compliance_score" min="0" max="100" value="<?= $bid['compliance_score'] ?? 0 ?>" 
                               style="width: 80px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; padding: 0.5rem; color: white; text-align: center;">
                        <button type="submit" class="btn-secondary-sm" style="flex: 1;">Update Score</button>
                        <a href="main.php?module=bidding/technical_dashboard&bid_id=<?= $bid['id'] ?>" class="btn-primary-sm" style="text-decoration: none;">
                            <i class="fas fa-eye"></i> Details
                        </a>
                    </form>
                    <?php else: ?>
                    <div class="text-dim" style="font-size: 0.85rem;">
                        <i class="fas fa-exclamation-triangle"></i> Technical bid record not initialized
                    </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.status-badge { padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.7rem; font-weight: bold; text-transform: uppercase; }
.status-badge.DRAFT { background: rgba(255,255,255,0.1); color: #ccc; }
.status-badge.TECHNICAL_COMPLETED { background: rgba(255,204,0,0.2); color: var(--gold); }
</style>
