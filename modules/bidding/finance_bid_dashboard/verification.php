<?php
// modules/bidding/finance_bid_dashboard/verification.php
require_once __DIR__ . '/../../../core/Logger.php';

$db = Database::getInstance();

// Handle Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_bid_id'])) {
    $bidId = $_POST['verify_bid_id'];
    try {
        $stmt = $db->prepare("UPDATE financial_bids SET status = 'internally_verified', updated_at = NOW() WHERE bid_id = ?");
        $stmt->execute([$bidId]);
        
        Logger::info('Bidding', "Bid #$bidId internally verified by Finance", ['bid_id' => $bidId]);
        
        echo "<div style='background: rgba(0, 255, 100, 0.1); border: 1px solid var(--accent-green); padding: 15px; border-radius: 12px; color: var(--accent-green); margin-bottom: 2rem;'>
                <i class='fas fa-shield-check'></i> Integrity Verification Successful. Bid #$bidId is now locked for review.
              </div>";
    } catch (Exception $e) {
        echo "<div class='text-red'>Error: " . $e->getMessage() . "</div>";
    }
}

// List Bids pending verification
$bids = $db->query("SELECT b.tender_no, b.title, b.client_name, fb.* 
                    FROM financial_bids fb 
                    JOIN bids b ON fb.bid_id = b.id 
                    WHERE fb.status IN ('pending_verification', 'draft') 
                    ORDER BY fb.updated_at DESC")->fetchAll();
?>

<div class="premium-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h3 style="margin: 0; font-size: 1.5rem; color: #fff;"><i class="fas fa-shield-alt text-gold"></i> Internal Quality Control</h3>
            <p class="text-secondary" style="margin-top: 5px;">Final fiscal audit before dispatching to General Management Review.</p>
        </div>
        <div class="glass-tag"><i class="fas fa-history"></i> Verification Log</div>
    </div>
    
    <?php if (empty($bids)): ?>
        <div style="text-align: center; padding: 100px 50px;">
            <div style="width: 80px; height: 80px; background: rgba(0, 255, 100, 0.05); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px auto;">
                <i class="fas fa-check-double fa-2x text-green"></i>
            </div>
            <h4 style="color: #fff;">Clear for Dispatch</h4>
            <p class="text-secondary">All initialized bids have passed internal fiscal verification.</p>
        </div>
    <?php else: ?>
        <table class="premium-table">
            <thead>
                <tr>
                    <th>Ref #</th>
                    <th>Project Scope</th>
                    <th>Fiscal Valuation</th>
                    <th>Margin Integrity</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bids as $row): 
                    $meta = json_decode($row['boq_json'] ?? '{}', true);
                    $breakdown = $meta['cost_breakdown'] ?? [];
                ?>
                <tr>
                    <td style="font-family: monospace; color: var(--gold); font-weight: 700;"><?= htmlspecialchars($row['tender_no']) ?></td>
                    <td>
                        <div style="font-weight: 600; color: #fff;"><?= htmlspecialchars($row['title']) ?></div>
                        <div style="font-size: 0.75rem; color: rgba(255,255,255,0.3);"><?= htmlspecialchars($row['client_name']) ?></div>
                    </td>
                    <td>
                        <div style="font-weight: 800; color: #fff; font-family: monospace;">$<?= number_format($row['total_amount'] ?? 0, 2) ?></div>
                        <div style="font-size: 0.7rem; color: var(--accent-green);">Ready for Logic Check</div>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="width: 100px; height: 6px; background: rgba(255,255,255,0.05); border-radius: 10px; overflow: hidden;">
                                <div style="width: <?= $row['profit_margin_percent'] ?>%; height: 100%; background: linear-gradient(90deg, #ffcc00, #00ff64);"></div>
                            </div>
                            <span style="font-size: 0.85rem; font-weight: bold; color: #fff;"><?= $row['profit_margin_percent'] ?>%</span>
                        </div>
                    </td>
                    <td style="text-align: right;">
                        <form method="POST" onsubmit="return confirm('Secure Lock: Are you sure you wish to internally verify this bid? It will be locked for final dispatch.');">
                            <input type="hidden" name="verify_bid_id" value="<?= $row['bid_id'] ?>">
                            <button type="submit" class="btn-primary-sm" style="background: var(--accent-green); color: black;">
                                Verify & Lock <i class="fas fa-lock-alt ml-2" style="font-size: 0.7rem;"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="premium-card" style="margin-top: 24px; border-left: 4px solid var(--gold);">
    <h5 style="color: var(--gold); margin-top: 0;"><i class="fas fa-info-circle"></i> Verification Standards</h5>
    <ul style="font-size: 0.85rem; color: rgba(255,255,255,0.5); padding-left: 20px; line-height: 1.6;">
        <li>Confirm all resource costs match technical coordination agreements.</li>
        <li>Validate profit margin falls within the approved corporate bracket (8%-15%).</li>
        <li>Ensure all mandatory attachments (Master BOQ, Quotations) are linked.</li>
        <li>Locked bids cannot be edited unless released by a GM or System Admin.</li>
    </ul>
</div>

