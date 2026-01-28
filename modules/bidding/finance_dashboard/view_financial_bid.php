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
$boq = !empty($fb['boq_json']) ? json_decode($fb['boq_json'], true) : null;
$is_gm = ($_SESSION['role_code'] ?? strtoupper($_SESSION['role'])) === 'GM';

?>

<div class="view-financial-bid-wizard">
    <div class="section-header mb-4" style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h2 style="color:var(--gold);"><i class="fas fa-eye"></i> Commercial Bid Review (Professional)</h2>
            <p class="text-dim"><?= htmlspecialchars($tender['title']) ?> (<?= htmlspecialchars($tender['tender_no']) ?>)</p>
        </div>
        <div style="display:flex; gap:1rem;">
             <a href="modules/bidding/finance_dashboard/download_boq.php?id=<?= $bid_id ?>" class="btn-primary-sm" style="background:var(--gold); color:black; font-weight:bold;">
                <i class="fas fa-file-excel mr-2"></i> Download Signed Excel
            </a>
            <a href="main.php?module=bidding/finance_dashboard" class="btn-secondary-sm">Back to Dashboard</a>
        </div>
    </div>

    <div class="wizard-tabs mb-4">
        <button class="wizard-tab active" onclick="switchView('grand-view')">1. Grand Summary</button>
        <button class="wizard-tab" onclick="switchView('summary-view')">2. BOQ Summary</button>
        <button class="wizard-tab" onclick="switchView('detailed-view')">3. Detailed BOQ</button>
    </div>

    <div style="display:grid; grid-template-columns: 2fr 1fr; gap:1.5rem;">
        <!-- Left: BOQ Content -->
        <div class="content-area">
            
            <!-- SHEET 1: GRAND SUMMARY -->
            <div id="grand-view" class="view-pane active glass-card">
                <h4 style="color:var(--gold); margin-bottom:2rem; text-align:center;">GRAND SUMMARY FOR <?= strtoupper($tender['title']) ?></h4>
                <table class="data-table no-border">
                    <tbody>
                        <tr style="border-bottom: 2px solid rgba(255,255,255,0.1);">
                            <td style="font-weight:bold; font-size:1.1rem; padding:1.5rem 0;">1. Unity Park Project (Build Contract)</td>
                            <td style="text-align:right; font-weight:bold; font-size:1.1rem;"><?= number_format($boq['totals']['grand'] ?? $fb['total_amount'], 2) ?> Birr</td>
                        </tr>
                        <tr><td colspan="2" style="height:30px;"></td></tr>
                        <tr>
                            <td style="text-align:right; color:var(--text-dim);">Total without VAT (Birr)</td>
                            <td style="text-align:right;"><?= number_format($boq['totals']['subtotal'] ?? ($fb['total_amount'] - $fb['tax']), 2) ?></td>
                        </tr>
                        <tr>
                            <td style="text-align:right; color:var(--text-dim);">VAT (15%)</td>
                            <td style="text-align:right;"><?= number_format($boq['totals']['vat'] ?? $fb['tax'], 2) ?></td>
                        </tr>
                        <tr style="font-size:1.4rem;">
                            <td style="text-align:right; font-weight:bold; color:var(--gold);">TOTAL WITH VAT (15%)</td>
                            <td style="text-align:right; font-weight:bold; color:var(--gold);"><?= number_format($boq['totals']['grand'] ?? $fb['total_amount'], 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- SHEET 2: BOQ SUMMARY -->
            <div id="summary-view" class="view-pane glass-card">
                <h4 style="color:var(--gold); margin-bottom:1.5rem;">SUMMARY OF BILL OF QUANTITIES</h4>
                <table class="data-table">
                    <thead><tr><th>No</th><th>Description</th><th style="text-align:right;">Amount (Birr)</th></tr></thead>
                    <tbody>
                        <tr><td>A</td><td>SUB STRUCTURE</td><td style="text-align:right;"><?= number_format($boq['totals']['a'] ?? 0, 2) ?></td></tr>
                        <tr><td>B</td><td>SUPER STRUCTURE</td><td style="text-align:right;"><?= number_format($boq['totals']['b'] ?? 0, 2) ?></td></tr>
                        <tr style="background:rgba(255,255,255,0.05);">
                            <td colspan="2" style="text-align:right; font-weight:bold;">Sub Total</td>
                            <td style="text-align:right; font-weight:bold;"><?= number_format($boq['totals']['subtotal'] ?? 0, 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- SHEET 3: DETAILED VIEW -->
            <div id="detailed-view" class="view-pane glass-card">
                <h4 style="color:var(--gold); margin-bottom:1.5rem;">DETAILED BREAKDOWN</h4>
                <p class="text-dim italic">Detailed audit of all quantities and rates submitted by estimator.</p>
                <div style="background:rgba(0,0,0,0.2); padding:1rem; border-radius:12px; height:500px; overflow-y:auto; border:1px solid rgba(255,255,255,0.05);">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Description</th>
                                <th>Unit</th>
                                <th style="text-align:right;">Qty</th>
                                <th style="text-align:right;">Rate</th>
                                <th style="text-align:right;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Sub Structure -->
                            <tr style="background:rgba(255,204,0,0.1); font-weight:bold;"><td colspan="6">A. SUB STRUCTURE</td></tr>
                            <?php if(!empty($boq['sub'])): foreach($boq['sub'] as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['no']) ?></td>
                                <td><?= htmlspecialchars($item['desc']) ?></td>
                                <td><?= htmlspecialchars($item['unit']) ?></td>
                                <td style="text-align:right;"><?= number_format($item['qty'], 2) ?></td>
                                <td style="text-align:right;"><?= number_format($item['rate'], 2) ?> Birr</td>
                                <td style="text-align:right; font-weight:bold; color:var(--gold);"><?= number_format($item['amount'], 2) ?></td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="6" class="text-center">No items recorded.</td></tr>
                            <?php endif; ?>

                            <!-- Super Structure -->
                            <tr style="background:rgba(255,204,0,0.1); font-weight:bold;"><td colspan="6">B. SUPER STRUCTURE</td></tr>
                            <?php if(!empty($boq['super'])): foreach($boq['super'] as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['no'] ?? '') ?></td>
                                <td><?= htmlspecialchars($item['desc'] ?? '') ?></td>
                                <td><?= htmlspecialchars($item['unit'] ?? '') ?></td>
                                <td style="text-align:right;"><?= number_format($item['qty'] ?? 0, 2) ?></td>
                                <td style="text-align:right;"><?= number_format($item['rate'] ?? 0, 2) ?> Birr</td>
                                <td style="text-align:right; font-weight:bold; color:var(--gold);"><?= number_format($item['amount'] ?? 0, 2) ?></td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="6" class="text-center">No items recorded.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Right: Control Panel -->
        <div class="control-panel">
            <div class="glass-card mb-4" style="background:rgba(0,212,255,0.05);">
                <h4>Workflow Insight</h4>
                <div style="font-size:0.8rem; color:var(--text-dim);">ESTIMATED BY</div>
                <div style="font-weight:bold; margin-bottom:1rem;">Financial Estimator #12</div>
                <div style="font-size:0.8rem; color:var(--text-dim);">STATUS</div>
                <div class="status-badge" style="background:var(--gold); color:black;"><?= $tender['status'] ?></div>
            </div>

            <?php if ($is_gm && $tender['status'] === 'FINANCE_FINAL_REVIEW'): ?>
                <div class="glass-card" style="border: 2px solid var(--gold);">
                    <h4 style="color:var(--gold); margin-bottom:1rem;">GM Executive Decision</h4>
                    <form method="POST" action="main.php?module=bidding/finance_dashboard/gm_decision">
                        <input type="hidden" name="bid_id" value="<?= $bid_id ?>">
                        <textarea name="reason" style="width:100%; height:80px; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); border-radius:8px; color:white; padding:0.5rem; margin-bottom:1rem;" placeholder="Decision remarks..."></textarea>
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                            <button type="submit" name="decision" value="LOSS" class="btn-primary-sm" style="background:#ff4444; color:white;">🔴 REJECT / LOST</button>
                            <button type="submit" name="decision" value="WON" class="btn-primary-sm" style="background:#00ff64; color:black;">🟢 APPROVE / WON</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function switchView(paneId) {
    document.querySelectorAll('.view-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.wizard-tab').forEach(t => t.classList.remove('active'));
    
    document.getElementById(paneId).classList.add('active');
    document.querySelector(`.wizard-tab[onclick*="${paneId}"]`).classList.add('active');
}
</script>

<style>
.wizard-tabs { display: flex; gap: 0.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 1rem; }
.wizard-tab { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: var(--text-dim); padding: 0.75rem 1.5rem; border-radius: 8px; cursor: pointer; }
.wizard-tab.active { background: var(--gold); color: black; font-weight: bold; }
.view-pane { display: none; }
.view-pane.active { display: block; }
.no-border td { border: none !important; }
</style>
