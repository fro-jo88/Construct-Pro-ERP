<?php
// modules/bidding/finance_dashboard/view_financial_bid.php
require_once __DIR__ . '/../../../includes/AuthManager.php';
require_once __DIR__ . '/../../../includes/BidManager.php';
require_once __DIR__ . '/../../../includes/TenderManager.php';

AuthManager::requireRole(['TENDER_FINANCE', 'FINANCE_BID_MANAGER', 'FINANCE_HEAD', 'GM']);

$bid_id = $_GET['id'] ?? null;
if (!$bid_id) die("Bid ID required.");

$tender = TenderManager::getTenderWithBids($bid_id);
$fb = $tender['financial_bid'];
$is_gm = ($_SESSION['role_code'] ?? strtoupper($_SESSION['role'])) === 'GM';

?>

<div class="view-financial-bid">
    <div class="section-header mb-4" style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h2 style="color:var(--gold);"><i class="fas fa-eye"></i> Commercial Bid Review</h2>
            <p class="text-dim"><?= htmlspecialchars($tender['title']) ?> (<?= htmlspecialchars($tender['tender_no']) ?>)</p>
        </div>
        <a href="main.php?module=bidding/finance_dashboard" class="btn-secondary-sm">Back to Dashboard</a>
    </div>

    <div style="display:grid; grid-template-columns: 2fr 1fr; gap:1.5rem;">
        <!-- Left: Costs -->
        <div class="glass-card">
            <h4 style="color:var(--gold); margin-bottom:1.5rem; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:0.5rem;">Breakdown of Costs</h4>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.5rem;">
                <div class="cost-item">
                    <label>Labor</label>
                    <div class="value">$ <?= number_format($fb['labor_cost'], 2) ?></div>
                </div>
                <div class="cost-item">
                    <label>Materials</label>
                    <div class="value">$ <?= number_format($fb['material_cost'], 2) ?></div>
                </div>
                <div class="cost-item">
                    <label>Equipment</label>
                    <div class="value">$ <?= number_format($fb['equipment_cost'], 2) ?></div>
                </div>
                <div class="cost-item">
                    <label>Overhead</label>
                    <div class="value">$ <?= number_format($fb['overhead_cost'], 2) ?></div>
                </div>
                <div class="cost-item">
                    <label>Tax</label>
                    <div class="value">$ <?= number_format($fb['tax'], 2) ?></div>
                </div>
                <div class="cost-item">
                    <label>Margin</label>
                    <div class="value"><?= $fb['profit_margin_percent'] ?> %</div>
                </div>
            </div>

            <div style="margin-top:2rem; padding:1.5rem; background:rgba(0,212,255,0.05); border:1px solid rgba(0,212,255,0.2); border-radius:12px; display:flex; justify-content:space-between; align-items:center;">
                <h3 style="margin:0; color:#00d4ff;">Total Commercial Offer</h3>
                <div style="font-size:2rem; font-weight:bold; color:#00d4ff;">$ <?= number_format($fb['total_amount'], 2) ?></div>
            </div>
        </div>

        <!-- Right: Actions/Decision -->
        <div style="display:flex; flex-direction:column; gap:1.5rem;">
            <!-- Status Card -->
            <div class="glass-card">
                <h4 style="margin-bottom:1rem;">Workflow State</h4>
                <div class="status-badge" style="display:block; text-align:center; padding:0.75rem; font-size:1rem; background:rgba(255,255,255,0.1);"><?= $tender['status'] ?></div>
            </div>

            <!-- GM Decision Panel (ONLY GM) -->
            <?php if ($is_gm && $tender['status'] === 'FINANCE_FINAL_REVIEW'): ?>
                <div class="glass-card" style="border: 1px solid var(--gold);">
                    <h4 style="color:var(--gold); margin-bottom:1rem;">GM Executive Decision</h4>
                    <p class="text-dim" style="font-size:0.8rem;">Select the final commercial outcome for this tender journey.</p>
                    
                    <form method="POST" action="main.php?module=bidding/finance_dashboard/gm_decision">
                        <input type="hidden" name="bid_id" value="<?= $bid_id ?>">
                        <div class="form-group mb-3">
                            <label style="font-size:0.7rem; color:var(--text-dim);">Internal Decision Notes</label>
                            <textarea name="reason" style="width:100%; height:80px; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); border-radius:8px; color:white; padding:0.5rem;"></textarea>
                        </div>
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                            <button type="submit" name="decision" value="LOSS" class="btn-primary-sm" style="background:#ff4444; color:white;">🔴 LOSS</button>
                            <button type="submit" name="decision" value="WON" class="btn-primary-sm" style="background:#00ff64; color:black;">🟢 WON</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.cost-item label { font-size: 0.7rem; color: var(--text-dim); text-transform: uppercase; }
.cost-item .value { font-size: 1.25rem; font-weight: bold; margin-top: 0.25rem; }
</style>
