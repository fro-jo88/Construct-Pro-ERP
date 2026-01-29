<?php
// modules/bidding/finance_bid_dashboard/submission.php
require_once __DIR__ . '/../../../includes/BidManager.php';

$db = Database::getInstance();

// Handle Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_bid_id'])) {
    $bidId = $_POST['submit_bid_id'];
    try {
        BidManager::submitFinancial($bidId, [], $_SESSION['user_id']);
        $msg = "Bid #$bidId dispatched successfully. Workflow synchronization active.";
        
        echo "<div style='background: rgba(0, 150, 255, 0.1); border: 1px solid var(--accent-blue); padding: 15px; border-radius: 12px; color: var(--accent-blue); margin-bottom: 2rem;'>
                <i class='fas fa-paper-plane'></i> <strong>Official Dispatch:</strong> $msg
              </div>";
    } catch (Exception $e) {
        echo "<div style='color: #ff4444; margin-bottom: 1rem;'>Transmission Error: " . $e->getMessage() . "</div>";
    }
}

// List Verified Bids
$bids = $db->query("SELECT b.tender_no, b.title, b.client_name, fb.* 
                    FROM financial_bids fb 
                    JOIN bids b ON fb.bid_id = b.id 
                    WHERE fb.status = 'internally_verified' 
                    ORDER BY fb.updated_at DESC")->fetchAll();
?>

<div class="premium-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h3 style="margin: 0; font-size: 1.5rem; color: #fff;"><i class="fas fa-paper-plane text-blue"></i> Official Portfolio Dispatch</h3>
            <p class="text-secondary" style="margin-top: 5px;">Synchronize and release verified financial valuations to the Executive Review board.</p>
        </div>
        <div class="glass-tag"><i class="fas fa-check-circle"></i> ISO/IEC 27001 Compliant</div>
    </div>
    
    <?php if (empty($bids)): ?>
        <div style="text-align: center; padding: 100px 50px;">
            <div style="width: 80px; height: 80px; background: rgba(0, 150, 255, 0.05); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px auto;">
                <i class="fas fa-inbox fa-2x text-blue"></i>
            </div>
            <h4 style="color: #fff;">Dispatch Queue Clear</h4>
            <p class="text-secondary">Verify bids in the Quality Control module to populate this queue.</p>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 20px;">
            <?php foreach ($bids as $row): ?>
            <div class="premium-card" style="border: 1px solid rgba(255,255,255,0.05); background: rgba(255,255,255,0.02); margin: 0;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
                    <div>
                        <div style="font-size: 0.75rem; color: var(--gold); font-weight: 700; margin-bottom: 4px;"><?= htmlspecialchars($row['tender_no']) ?></div>
                        <h4 style="margin: 0; color: #fff; line-height: 1.2;"><?= htmlspecialchars($row['title']) ?></h4>
                        <div style="font-size: 0.8rem; color: rgba(255,255,255,0.4); margin-top: 4px;"><i class="fas fa-user-tag"></i> <?= htmlspecialchars($row['client_name']) ?></div>
                    </div>
                    <span class="badge-premium" style="background: rgba(0, 255, 100, 0.1); color: var(--accent-green); border: 1px solid rgba(0, 255, 100, 0.2);">VERIFIED</span>
                </div>

                <div style="background: rgba(0,0,0,0.2); padding: 15px; border-radius: 12px; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 0.65rem; color: rgba(255,255,255,0.3); text-transform: uppercase; letter-spacing: 1px;">Projected Total</div>
                        <div style="font-size: 1.25rem; font-weight: 800; color: #fff; font-family: monospace;">$<?= number_format($row['total_amount'], 2) ?></div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 0.65rem; color: rgba(255,255,255,0.3); text-transform: uppercase;">Est. Efficiency</div>
                        <div style="font-weight: bold; color: var(--accent-green);"><?= $row['profit_margin_percent'] ?>%</div>
                    </div>
                </div>

                <form method="POST" onsubmit="return confirm('Official Dispatch: Are you sure you wish to release this bid to the GM Board? This action will generate an immutable record.');">
                    <input type="hidden" name="submit_bid_id" value="<?= $row['bid_id'] ?>">
                    <div style="display: flex; gap: 10px;">
                        <textarea placeholder="Optional: Note for GM..." style="flex: 1; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff; font-size: 0.8rem; padding: 10px; height: 42px; resize: none;"></textarea>
                        <button type="submit" class="btn-primary-sm" style="padding: 0 20px;">
                            Dispatch <i class="fas fa-chevron-right ml-2"></i>
                        </button>
                    </div>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="premium-card" style="margin-top: 24px; border-left: 4px solid var(--accent-blue);">
    <h5 style="color: var(--accent-blue); margin-top: 0;"><i class="fas fa-layer-group"></i> Parallel Workflow Intelligence</h5>
    <div style="font-size: 0.85rem; color: rgba(255,255,255,0.5); line-height: 1.6;">
        Selecting <strong>Dispatch</strong> will immediately update the <code>FINANCIAL</code> assignment phase. If the <code>TECHNICAL</code> department has already concluded their evaluation, the bid will automatically graduate to the General Manager's <strong>Review Board</strong>. Otherwise, it will remain in <code>COORDINATION_PENDING</code> until both vectors are complete.
    </div>
</div>

