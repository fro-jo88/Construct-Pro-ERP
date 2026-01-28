<?php
// modules/bidding/finance_dashboard/create_financial_bid.php
require_once __DIR__ . '/../../../includes/AuthManager.php';
require_once __DIR__ . '/../../../includes/BidManager.php';
require_once __DIR__ . '/../../../includes/TenderManager.php';

AuthManager::requireRole(['TENDER_FINANCE', 'FINANCE_BID_MANAGER', 'FINANCE_HEAD', 'GM']);

$bid_id = $_GET['id'] ?? null;
if (!$bid_id) die("Bid ID required.");

$tender = TenderManager::getTenderWithBids($bid_id);
if (!$tender) die("Bid not found.");

$fb = $tender['financial_bid'];
// Decode existing BOQ if any
$boq_data = !empty($fb['boq_json']) ? json_decode($fb['boq_json'], true) : null;

?>

<div class="create-financial-bid-wizard">
    <div class="section-header mb-4" style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h2 style="color:var(--gold);"><i class="fas fa-file-invoice-dollar"></i> Professional Financial Submission</h2>
            <p class="text-dim"><?= htmlspecialchars($tender['title']) ?> (<?= htmlspecialchars($tender['tender_no']) ?>)</p>
        </div>
        <div style="display:flex; gap:1rem;">
             <a href="modules/bidding/finance_dashboard/download_boq.php?id=<?= $bid_id ?>" class="btn-primary-sm" style="background:var(--gold); color:black; font-weight:bold;">
                <i class="fas fa-file-excel mr-2"></i> Download BOQ Template
            </a>
            <a href="main.php?module=bidding/finance_dashboard" class="btn-secondary-sm">Back to Dashboard</a>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="wizard-tabs mb-4">
        <button class="wizard-tab active" onclick="switchTab('detailed-boq')">1. Detailed BOQ</button>
        <button class="wizard-tab" onclick="switchTab('boq-summary')">2. BOQ Summary</button>
        <button class="wizard-tab" onclick="switchTab('grand-summary')">3. Grand Summary</button>
    </div>

    <form id="boqForm" method="POST" action="main.php?module=bidding/finance_dashboard/save_financial_bid">
        <input type="hidden" name="bid_id" value="<?= $bid_id ?>">
        <input type="hidden" name="boq_json" id="boq_json_input">

        <!-- SHEET 3: DETAILED BOQ -->
        <div id="detailed-boq" class="wizard-content active">
            <div class="glass-card">
                <h4 style="color:var(--gold); margin-bottom:1.5rem;"><i class="fas fa-list-ol"></i> Sheet 3: Detailed Bill of Quantities</h4>
                
                <!-- Category A -->
                <div class="boq-category mb-5">
                    <h5 class="category-title">A. SUB STRUCTURE</h5>
                    <table class="data-table boq-table" data-category="sub">
                        <thead>
                            <tr>
                                <th style="width:80px;">Item No</th>
                                <th>Description</th>
                                <th style="width:100px;">Unit</th>
                                <th style="width:120px;">Quantity</th>
                                <th style="width:150px;">Rate (Birr)</th>
                                <th style="width:180px;">Amount (Birr)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1.1</td>
                                <td>Excavation & Earth Work</td>
                                <td><input type="text" name="sub_unit[]" value="m3" readonly></td>
                                <td><input type="number" step="0.01" name="sub_qty[]" class="calc-trigger" value="<?= $boq_data['sub'][0]['qty'] ?? 0 ?>"></td>
                                <td><input type="number" step="0.01" name="sub_rate[]" class="calc-trigger" value="<?= $boq_data['sub'][0]['rate'] ?? 0 ?>"></td>
                                <td class="amount-cell">0.00</td>
                            </tr>
                            <tr>
                                <td>1.2</td>
                                <td>Masonry Work</td>
                                <td><input type="text" name="sub_unit[]" value="m3" readonly></td>
                                <td><input type="number" step="0.01" name="sub_qty[]" class="calc-trigger" value="<?= $boq_data['sub'][1]['qty'] ?? 0 ?>"></td>
                                <td><input type="number" step="0.01" name="sub_rate[]" class="calc-trigger" value="<?= $boq_data['sub'][1]['rate'] ?? 0 ?>"></td>
                                <td class="amount-cell">0.00</td>
                            </tr>
                            <tr>
                                <td>1.3</td>
                                <td>Concrete Work</td>
                                <td><input type="text" name="sub_unit[]" value="m3" readonly></td>
                                <td><input type="number" step="0.01" name="sub_qty[]" class="calc-trigger" value="<?= $boq_data['sub'][2]['qty'] ?? 0 ?>"></td>
                                <td><input type="number" step="0.01" name="sub_rate[]" class="calc-trigger" value="<?= $boq_data['sub'][2]['rate'] ?? 0 ?>"></td>
                                <td class="amount-cell">0.00</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" style="text-align:right; font-weight:bold;">TOTAL CARRIED TO SUMMARY (A):</td>
                                <td id="total-sub" class="category-total">0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Category B -->
                <div class="boq-category">
                    <h5 class="category-title">B. SUPER STRUCTURE</h5>
                    <table class="data-table boq-table" data-category="super">
                        <thead>
                            <tr>
                                <th style="width:80px;">Item No</th>
                                <th>Description</th>
                                <th style="width:100px;">Unit</th>
                                <th style="width:120px;">Quantity</th>
                                <th style="width:150px;">Rate (Birr)</th>
                                <th style="width:180px;">Amount (Birr)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $super_items = [
                                '2.1' => 'Concrete Work', '2.2' => 'Block Work', '2.3' => 'Carpentry & Joinery',
                                '2.4' => 'Roofing Work', '2.5' => 'Metal Work', '2.6' => 'Glazing Work',
                                '2.7' => 'Flooring Work', '2.8' => 'Finishing Work', '2.9' => 'Electrical Installation'
                            ];
                            $idx = 0;
                            foreach ($super_items as $no => $label): ?>
                            <tr>
                                <td><?= $no ?></td>
                                <td><?= $label ?><input type="hidden" name="super_desc[]" value="<?= $label ?>"></td>
                                <td><input type="text" name="super_unit[]" value="Unit" class="text-center"></td>
                                <td><input type="number" step="0.01" name="super_qty[]" class="calc-trigger" value="<?= $boq_data['super'][$idx]['qty'] ?? 0 ?>"></td>
                                <td><input type="number" step="0.01" name="super_rate[]" class="calc-trigger" value="<?= $boq_data['super'][$idx]['rate'] ?? 0 ?>"></td>
                                <td class="amount-cell">0.00</td>
                            </tr>
                            <?php $idx++; endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" style="text-align:right; font-weight:bold;">TOTAL CARRIED TO SUMMARY (B):</td>
                                <td id="total-super" class="category-total">0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="mt-4" style="text-align:right;">
                <button type="button" class="btn-primary-sm" onclick="switchTab('boq-summary')">Next: BOQ Summary <i class="fas fa-arrow-right"></i></button>
            </div>
        </div>

        <!-- SHEET 2: BOQ SUMMARY -->
        <div id="boq-summary" class="wizard-content">
            <div class="glass-card">
                <h4 style="color:var(--gold); margin-bottom:1.5rem;"><i class="fas fa-compress-alt"></i> Sheet 2: Summary of Bill of Quantities</h4>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Item No</th>
                            <th>Description</th>
                            <th style="text-align:right;">Amount (Birr)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>A</td><td>SUB STRUCTURE</td><td id="summ-a" style="text-align:right;">0.00</td></tr>
                        <tr><td>B</td><td>SUPER STRUCTURE</td><td id="summ-b" style="text-align:right;">0.00</td></tr>
                        <tr style="border-top:2px solid var(--gold);">
                            <td colspan="2" style="text-align:right; font-weight:bold;">Total A + B</td>
                            <td id="summ-subtotal" style="text-align:right; font-weight:bold;">0.00</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="text-align:right;">VAT (15%)</td>
                            <td id="summ-vat" style="text-align:right;">0.00</td>
                        </tr>
                        <tr style="background:rgba(255,204,0,0.1);">
                            <td colspan="2" style="text-align:right; font-weight:bold; color:var(--gold);">TOTAL WITH VAT (15%)</td>
                            <td id="summ-grand" style="text-align:right; font-weight:bold; color:var(--gold);">0.00</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-4" style="display:flex; justify-content:space-between;">
                <button type="button" class="btn-secondary-sm" onclick="switchTab('detailed-boq')"><i class="fas fa-arrow-left"></i> Back</button>
                <button type="button" class="btn-primary-sm" onclick="switchTab('grand-summary')">Next: Grand Summary <i class="fas fa-arrow-right"></i></button>
            </div>
        </div>

        <!-- SHEET 1: GRAND SUMMARY -->
        <div id="grand-summary" class="wizard-content">
            <div class="glass-card" style="border: 2px solid var(--gold); background: linear-gradient(135deg, rgba(255,204,0,0.05), transparent);">
                <h4 style="color:var(--gold); margin-bottom:2rem; text-align:center;">GRAND SUMMARY FOR <?= strtoupper($tender['title']) ?></h4>
                <table class="data-table no-border">
                    <tbody>
                        <tr>
                            <td style="font-weight:bold; font-size:1.1rem;">1. <?= htmlspecialchars($tender['title']) ?> Project</td>
                            <td id="grand-final-a" style="text-align:right; font-weight:bold; font-size:1.1rem;">0.00</td>
                        </tr>
                        <tr><td colspan="2" style="height:20px;"></td></tr>
                        <tr>
                            <td style="text-align:right; color:var(--text-dim);">TOTAL WITHOUT VAT</td>
                            <td id="grand-no-vat" style="text-align:right;">0.00</td>
                        </tr>
                        <tr>
                            <td style="text-align:right; color:var(--text-dim);">VAT (15%)</td>
                            <td id="grand-vat-val" style="text-align:right;">0.00</td>
                        </tr>
                        <tr style="font-size:1.4rem;">
                            <td style="text-align:right; font-weight:bold; color:var(--gold);">TOTAL WITH VAT (15%)</td>
                            <td id="grand-total-final" style="text-align:right; font-weight:bold; color:var(--gold);">0.00</td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="mt-5" style="border-top:1px solid rgba(255,255,255,0.1); padding-top:2rem;">
                    <label>Internal Submission Note</label>
                    <textarea name="submission_note" style="width:100%; height:100px; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); border-radius:12px; color:white; padding:1rem;" placeholder="Add technical comments for GM review..."></textarea>
                </div>
            </div>

            <div class="mt-4" style="display:flex; justify-content:space-between; align-items:center;">
                <button type="button" class="btn-secondary-sm" onclick="switchTab('boq-summary')"><i class="fas fa-arrow-left"></i> Back</button>
                <div style="display:flex; gap:1rem;">
                    <button type="submit" name="action" value="save_draft" class="btn-secondary-sm">Save as Local Draft</button>
                    <button type="submit" name="action" value="submit_gm" class="btn-primary-sm" style="background:#00ff64; color:black; font-weight:bold;">SUBMIT FINANCIAL BID TO GM</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function switchTab(tabId) {
    document.querySelectorAll('.wizard-content').forEach(c => c.classList.remove('active'));
    document.querySelectorAll('.wizard-tab').forEach(t => t.classList.remove('active'));
    
    document.getElementById(tabId).classList.add('active');
    document.querySelector(`.wizard-tab[onclick*="${tabId}"]`).classList.add('active');
    
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function calculateBOQ() {
    let totalA = 0;
    let totalB = 0;

    // Calculate Sub Structure
    document.querySelectorAll('[data-category="sub"] tbody tr').forEach(row => {
        const qty = parseFloat(row.querySelector('input[name="sub_qty[]"]').value) || 0;
        const rate = parseFloat(row.querySelector('input[name="sub_rate[]"]').value) || 0;
        const amount = qty * rate;
        row.querySelector('.amount-cell').innerText = amount.toLocaleString(undefined, {minimumFractionDigits:2});
        totalA += amount;
    });
    document.getElementById('total-sub').innerText = totalA.toLocaleString(undefined, {minimumFractionDigits:2});

    // Calculate Super Structure
    document.querySelectorAll('[data-category="super"] tbody tr').forEach(row => {
        const qty = parseFloat(row.querySelector('input[name="super_qty[]"]').value) || 0;
        const rate = parseFloat(row.querySelector('input[name="super_rate[]"]').value) || 0;
        const amount = qty * rate;
        row.querySelector('.amount-cell').innerText = amount.toLocaleString(undefined, {minimumFractionDigits:2});
        totalB += amount;
    });
    document.getElementById('total-super').innerText = totalB.toLocaleString(undefined, {minimumFractionDigits:2});

    // Update Summary
    const subtotal = totalA + totalB;
    const vat = subtotal * 0.15;
    const grand = subtotal + vat;

    document.getElementById('summ-a').innerText = totalA.toLocaleString();
    document.getElementById('summ-b').innerText = totalB.toLocaleString();
    document.getElementById('summ-subtotal').innerText = subtotal.toLocaleString();
    document.getElementById('summ-vat').innerText = vat.toLocaleString();
    document.getElementById('summ-grand').innerText = grand.toLocaleString();

    // Update Grand Summary
    document.getElementById('grand-final-a').innerText = grand.toLocaleString();
    document.getElementById('grand-no-vat').innerText = subtotal.toLocaleString();
    document.getElementById('grand-vat-val').innerText = vat.toLocaleString();
    document.getElementById('grand-total-final').innerText = grand.toLocaleString();

    // Prepare JSON for save
    const boqData = {
        sub: [],
        super: [],
        totals: { a: totalA, b: totalB, subtotal, vat, grand }
    };
    // ... logic to push items to arrays ...
    document.getElementById('boq_json_input').value = JSON.stringify(boqData);
}

document.querySelectorAll('.calc-trigger').forEach(input => {
    input.addEventListener('input', calculateBOQ);
});

// Initial Calc
calculateBOQ();
</script>

<style>
.wizard-tabs {
    display: flex;
    gap: 0.5rem;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    padding-bottom: 1rem;
}
.wizard-tab {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    color: var(--text-dim);
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
}
.wizard-tab.active {
    background: var(--gold);
    color: black;
    font-weight: bold;
}
.wizard-content {
    display: none;
}
.wizard-content.active {
    display: block;
}
.category-title {
    background: rgba(255,204,0,0.1);
    color: var(--gold);
    padding: 0.75rem 1.5rem;
    margin-bottom: 0;
    border-radius: 8px 8px 0 0;
    border: 1px solid rgba(255,204,0,0.2);
}
.boq-table {
    border: 1px solid rgba(255,255,255,0.05);
}
.boq-table input {
    width: 100%;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 4px;
    color: white;
    padding: 4px 8px;
    text-align: right;
}
.boq-table input[readonly] {
    border: none;
    background: transparent;
    text-align: left;
}
.amount-cell, .category-total {
    text-align: right;
    font-weight: bold;
    color: var(--gold);
}
.no-border td { border: none !important; }
</style>
