<?php
// modules/bidding/finance_dashboard/create_financial_bid.php
require_once __DIR__ . '/../../../includes/AuthManager.php';
require_once __DIR__ . '/../../../includes/BidManager.php';
require_once __DIR__ . '/../../../includes/TenderManager.php';

AuthManager::requireRole(['TENDER_FINANCE', 'FINANCE_HEAD', 'GM']);

$bid_id = $_GET['id'] ?? null;
if (!$bid_id) die("Bid ID required.");

$tender = TenderManager::getTenderWithBids($bid_id);
if (!$tender) die("Bid not found.");

$fb = $tender['financial_bid'];

?>

<div class="create-financial-bid">
    <div class="section-header mb-4" style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h2 style="color:var(--gold);"><i class="fas fa-file-invoice-dollar"></i> Financial Evaluation</h2>
            <p class="text-dim"><?= htmlspecialchars($tender['title']) ?> (<?= htmlspecialchars($tender['tender_no']) ?>)</p>
        </div>
        <a href="main.php?module=bidding/finance_dashboard" class="btn-secondary-sm">Back to Dashboard</a>
    </div>

    <div class="glass-card">
        <form method="POST" action="main.php?module=bidding/finance_dashboard/save_financial_bid">
            <input type="hidden" name="bid_id" value="<?= $bid_id ?>">
            
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:2rem;">
                <!-- Column 1 -->
                <div>
                    <div class="form-group mb-4">
                        <label>Labor Cost ($)</label>
                        <input type="number" step="0.01" name="labor_cost" value="<?= $fb['labor_cost'] ?? 0 ?>" required oninput="calculateTotal()">
                    </div>
                    <div class="form-group mb-4">
                        <label>Material Cost ($)</label>
                        <input type="number" step="0.01" name="material_cost" value="<?= $fb['material_cost'] ?? 0 ?>" required oninput="calculateTotal()">
                    </div>
                    <div class="form-group mb-4">
                        <label>Equipment & Logistics Cost ($)</label>
                        <input type="number" step="0.01" name="equipment_cost" value="<?= $fb['equipment_cost'] ?? 0 ?>" required oninput="calculateTotal()">
                    </div>
                </div>

                <!-- Column 2 -->
                <div>
                    <div class="form-group mb-4">
                        <label>Overhead & Admin Cost ($)</label>
                        <input type="number" step="0.01" name="overhead_cost" value="<?= $fb['overhead_cost'] ?? 0 ?>" required oninput="calculateTotal()">
                    </div>
                    <div class="form-group mb-4">
                        <label>Applied Tax ($)</label>
                        <input type="number" step="0.01" name="tax" value="<?= $fb['tax'] ?? 0 ?>" required oninput="calculateTotal()">
                    </div>
                    <div class="form-group mb-4">
                        <label>Profit Margin (%)</label>
                        <input type="number" step="0.01" name="profit_margin" value="<?= $fb['profit_margin_percent'] ?? 15 ?>" required oninput="calculateTotal()">
                    </div>
                </div>
            </div>

            <!-- Total Bar -->
            <div style="margin-top:2rem; padding:2rem; background:rgba(255,204,0,0.05); border-radius:12px; border:1px solid rgba(255,204,0,0.2); display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h3 style="margin:0; text-transform:uppercase; font-size:0.8rem; color:var(--text-dim);">Estimated Final Bid Value</h3>
                    <div id="total_display" style="font-size:2.5rem; font-weight:bold; color:var(--gold);">$ 0.00</div>
                    <input type="hidden" name="total_amount" id="total_amount_input">
                </div>
                <div style="display:flex; gap:1rem;">
                    <button type="submit" name="action" value="save_draft" class="btn-secondary-sm">Save Draft</button>
                    <button type="submit" name="action" value="submit_gm" class="btn-primary-sm" style="background:#00ff64; color:black; font-weight:bold;">Submit to GM for Final Approval</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function calculateTotal() {
    const fields = ['labor_cost', 'material_cost', 'equipment_cost', 'overhead_cost', 'tax'];
    let subtotal = 0;
    fields.forEach(f => {
        subtotal += parseFloat(document.querySelector(`input[name="${f}"]`).value) || 0;
    });

    const marginPercent = parseFloat(document.querySelector('input[name="profit_margin"]').value) || 0;
    const total = subtotal * (1 + (marginPercent / 100));

    document.getElementById('total_display').innerText = '$ ' + total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('total_amount_input').value = total.toFixed(2);
}

// Initial Calc
calculateTotal();
</script>

<style>
.create-financial-bid input {
    width: 100%;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px;
    color: white;
    padding: 0.8rem;
    font-size: 1rem;
}
.create-financial-bid label {
    display: block;
    margin-bottom: 0.5rem;
    font-size: 0.8rem;
    color: var(--text-dim);
    text-transform: uppercase;
}
</style>
