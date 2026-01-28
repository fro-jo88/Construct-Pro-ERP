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
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
                    <h4 style="color:var(--gold); margin:0;"><i class="fas fa-list-ol"></i> Sheet 3: Detailed Bill of Quantities</h4>
                </div>
                
                <!-- Category A -->
                <div class="boq-category mb-5">
                    <div style="display:flex; justify-content:space-between; align-items:center;" class="category-title">
                        <span>A. SUB STRUCTURE</span>
                        <button type="button" class="btn-primary-sm" style="font-size:0.7rem; padding:4px 10px;" onclick="addBOQRow('sub')">+ Add New Item</button>
                    </div>
                    <table class="data-table boq-table" id="table-sub" data-category="sub">
                        <thead>
                            <tr>
                                <th style="width:70px;">Item No</th>
                                <th>Description</th>
                                <th style="width:80px;">Unit</th>
                                <th style="width:100px;">Quantity</th>
                                <th style="width:120px;">Rate (Birr)</th>
                                <th style="width:150px;">Amount (Birr)</th>
                                <th style="width:40px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $sub_items = $boq_data['sub'] ?? [
                                ['no' => '1.1', 'desc' => 'Excavation & Earth Work', 'unit' => 'm3', 'qty' => 0, 'rate' => 0],
                                ['no' => '1.2', 'desc' => 'Masonry Work', 'unit' => 'm3', 'qty' => 0, 'rate' => 0],
                                ['no' => '1.3', 'desc' => 'Concrete Work', 'unit' => 'm3', 'qty' => 0, 'rate' => 0]
                            ];
                            foreach ($sub_items as $item): ?>
                            <tr>
                                <td><input type="text" name="sub_no[]" value="<?= $item['no'] ?>"></td>
                                <td><input type="text" name="sub_desc[]" value="<?= $item['desc'] ?>" style="text-align:left;"></td>
                                <td><input type="text" name="sub_unit[]" value="<?= $item['unit'] ?>" style="text-align:center;"></td>
                                <td><input type="number" step="0.01" name="sub_qty[]" class="calc-trigger" value="<?= $item['qty'] ?>"></td>
                                <td><input type="number" step="0.01" name="sub_rate[]" class="calc-trigger" value="<?= $item['rate'] ?>"></td>
                                <td class="amount-cell">0.00</td>
                                <td><button type="button" class="btn-delete-row" onclick="deleteBOQRow(this)"><i class="fas fa-times"></i></button></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" style="text-align:right; font-weight:bold;">TOTAL CARRIED TO SUMMARY (A):</td>
                                <td id="total-sub" class="category-total">0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Category B -->
                <div class="boq-category">
                    <div style="display:flex; justify-content:space-between; align-items:center;" class="category-title">
                        <span>B. SUPER STRUCTURE</span>
                        <button type="button" class="btn-primary-sm" style="font-size:0.7rem; padding:4px 10px;" onclick="addBOQRow('super')">+ Add New Item</button>
                    </div>
                    <table class="data-table boq-table" id="table-super" data-category="super">
                        <thead>
                            <tr>
                                <th style="width:70px;">Item No</th>
                                <th>Description</th>
                                <th style="width:80px;">Unit</th>
                                <th style="width:100px;">Quantity</th>
                                <th style="width:120px;">Rate (Birr)</th>
                                <th style="width:150px;">Amount (Birr)</th>
                                <th style="width:40px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $super_items = $boq_data['super'] ?? [
                                ['no' => '2.1', 'desc' => 'Concrete Work', 'unit' => 'm3', 'qty' => 0, 'rate' => 0],
                                ['no' => '2.2', 'desc' => 'Block Work', 'unit' => 'm2', 'qty' => 0, 'rate' => 0],
                                ['no' => '2.3', 'desc' => 'Carpentry & Joinery', 'unit' => 'pcs', 'qty' => 0, 'rate' => 0],
                                ['no' => '2.4', 'desc' => 'Roofing Work', 'unit' => 'm2', 'qty' => 0, 'rate' => 0],
                                ['no' => '2.5', 'desc' => 'Metal Work', 'unit' => 'kg', 'qty' => 0, 'rate' => 0],
                                ['no' => '2.6', 'desc' => 'Glazing Work', 'unit' => 'm2', 'qty' => 0, 'rate' => 0],
                                ['no' => '2.7', 'desc' => 'Flooring Work', 'unit' => 'm2', 'qty' => 0, 'rate' => 0],
                                ['no' => '2.8', 'desc' => 'Finishing Work', 'unit' => 'm2', 'qty' => 0, 'rate' => 0],
                                ['no' => '2.9', 'desc' => 'Electrical Installation', 'unit' => 'LS', 'qty' => 0, 'rate' => 0]
                            ];
                            foreach ($super_items as $item): ?>
                            <tr>
                                <td><input type="text" name="super_no[]" value="<?= $item['no'] ?>"></td>
                                <td><input type="text" name="super_desc[]" value="<?= $item['desc'] ?>" style="text-align:left;"></td>
                                <td><input type="text" name="super_unit[]" value="<?= $item['unit'] ?>" style="text-align:center;"></td>
                                <td><input type="number" step="0.01" name="super_qty[]" class="calc-trigger" value="<?= $item['qty'] ?>"></td>
                                <td><input type="number" step="0.01" name="super_rate[]" class="calc-trigger" value="<?= $item['rate'] ?>"></td>
                                <td class="amount-cell">0.00</td>
                                <td><button type="button" class="btn-delete-row" onclick="deleteBOQRow(this)"><i class="fas fa-times"></i></button></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" style="text-align:right; font-weight:bold;">TOTAL CARRIED TO SUMMARY (B):</td>
                                <td id="total-super" class="category-total">0.00</td>
                                <td></td>
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
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
                    <h4 style="color:var(--gold); margin:0;"><i class="fas fa-compress-alt"></i> Sheet 2: Summary of Bill of Quantities</h4>
                    <button type="button" class="btn-primary-sm" onclick="addCategory()">+ Add New Category (C, D...)</button>
                </div>
                <table class="data-table" id="summary-table">
                    <thead>
                        <tr>
                            <th style="width:70px;">No</th>
                            <th>Description</th>
                            <th style="width:180px; text-align:right;">Amount (Birr)</th>
                            <th style="width:40px;"></th>
                        </tr>
                    </thead>
                    <tbody id="summary-tbody">
                        <tr data-cat-id="sub">
                            <td>A</td>
                            <td><input type="text" class="cat-title-input" data-cat-id="sub" value="SUB STRUCTURE" oninput="syncCatTitle('sub', this.value)"></td>
                            <td id="summ-sub" style="text-align:right;">0.00</td>
                            <td></td>
                        </tr>
                        <tr data-cat-id="super">
                            <td>B</td>
                            <td><input type="text" class="cat-title-input" data-cat-id="super" value="SUPER STRUCTURE" oninput="syncCatTitle('super', this.value)"></td>
                            <td id="summ-super" style="text-align:right;">0.00</td>
                            <td></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr style="border-top:2px solid var(--gold);">
                            <td colspan="2" style="text-align:right; font-weight:bold; padding-top:1rem;">Total without VAT</td>
                            <td id="summ-subtotal" style="text-align:right; font-weight:bold; padding-top:1rem;">0.00</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="2" style="text-align:right;">VAT (15%)</td>
                            <td id="summ-vat" style="text-align:right;">0.00</td>
                            <td></td>
                        </tr>
                        <tr style="background:rgba(255,204,0,0.1);">
                            <td colspan="2" style="text-align:right; font-weight:bold; color:var(--gold);">TOTAL WITH VAT (15%)</td>
                            <td id="summ-grand" style="text-align:right; font-weight:bold; color:var(--gold);">0.00</td>
                            <td></td>
                        </tr>
                    </tfoot>
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

function addBOQRow(category) {
    const tbody = document.querySelector(`#table-${category} tbody`);
    const newRow = document.createElement('tr');
    newRow.innerHTML = `
        <td><input type="text" name="${category}_no[]" value=""></td>
        <td><input type="text" name="${category}_desc[]" value="" style="text-align:left;"></td>
        <td><input type="text" name="${category}_unit[]" value="" style="text-align:center;"></td>
        <td><input type="number" step="0.01" name="${category}_qty[]" class="calc-trigger" value="0"></td>
        <td><input type="number" step="0.01" name="${category}_rate[]" class="calc-trigger" value="0"></td>
        <td class="amount-cell">0.00</td>
        <td><button type="button" class="btn-delete-row" onclick="deleteBOQRow(this)"><i class="fas fa-times"></i></button></td>
    `;
    tbody.appendChild(newRow);
    
    // Add event listeners to new inputs
    newRow.querySelectorAll('.calc-trigger').forEach(input => {
        input.addEventListener('input', calculateBOQ);
    });
}

function deleteBOQRow(btn) {
    if(confirm('Remove this BOQ item?')) {
        btn.closest('tr').remove();
        calculateBOQ();
    }
}

const categories = ['sub', 'super'];

function syncCatTitle(catId, val) {
    const titleEl = document.querySelector(`.category-title[data-cat-id="${catId}"] span`);
    if(titleEl) titleEl.innerText = val;
    calculateBOQ();
}

function addCategory() {
    const nextCode = String.fromCharCode(65 + categories.length); // C, D, E...
    const catId = 'cat_' + Date.now();
    categories.push(catId);

    // 1. Add to Summary
    const summaryTbody = document.getElementById('summary-tbody');
    const summaryRow = document.createElement('tr');
    summaryRow.setAttribute('data-cat-id', catId);
    summaryRow.innerHTML = `
        <td>${nextCode}</td>
        <td><input type="text" class="cat-title-input" data-cat-id="${catId}" value="NEW CATEGORY" oninput="syncCatTitle('${catId}', this.value)"></td>
        <td id="summ-${catId}" style="text-align:right;">0.00</td>
        <td><button type="button" class="btn-delete-row" onclick="deleteCategory('${catId}')"><i class="fas fa-times"></i></button></td>
    `;
    summaryTbody.appendChild(summaryRow);

    // 2. Add to Detailed BOQ (Sheet 3)
    const detailedContainer = document.querySelector('#detailed-boq .glass-card');
    const nextBtn = document.querySelector('#detailed-boq .mt-4');
    
    const categoryDiv = document.createElement('div');
    categoryDiv.className = 'boq-category mb-5';
    categoryDiv.setAttribute('data-cat-id', catId);
    categoryDiv.innerHTML = `
        <div style="display:flex; justify-content:space-between; align-items:center;" class="category-title" data-cat-id="${catId}">
            <span>NEW CATEGORY</span>
            <button type="button" class="btn-primary-sm" style="font-size:0.7rem; padding:4px 10px;" onclick="addBOQRow('${catId}')">+ Add New Item</button>
        </div>
        <table class="data-table boq-table" id="table-${catId}" data-category="${catId}">
            <thead>
                <tr>
                    <th style="width:70px;">Item No</th>
                    <th>Description</th>
                    <th style="width:80px;">Unit</th>
                    <th style="width:100px;">Quantity</th>
                    <th style="width:120px;">Rate (Birr)</th>
                    <th style="width:150px;">Amount (Birr)</th>
                    <th style="width:40px;"></th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr>
                    <td colspan="5" style="text-align:right; font-weight:bold;">TOTAL CARRIED TO SUMMARY:</td>
                    <td id="total-${catId}" class="category-total">0.00</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    `;
    detailedContainer.appendChild(categoryDiv);
    
    calculateBOQ();
}

function deleteCategory(catId) {
    if(confirm('Delete entire category and all its items?')) {
        document.querySelector(`#summary-tbody tr[data-cat-id="${catId}"]`).remove();
        document.querySelector(`.boq-category[data-cat-id="${catId}"]`).remove();
        const index = categories.indexOf(catId);
        if (index > -1) categories.splice(index, 1);
        calculateBOQ();
    }
}

function calculateBOQ() {
    let grandSubtotal = 0;
    const boqData = { categories: [], totals: {} };

    categories.forEach(catId => {
        let catTotal = 0;
        const items = [];
        const catTitle = document.querySelector(`.cat-title-input[data-cat-id="${catId}"]`).value;

        document.querySelectorAll(`#table-${catId} tbody tr`).forEach(row => {
            const no = row.querySelector(`input[name*="_no[]"]`).value;
            const desc = row.querySelector(`input[name*="_desc[]"]`).value;
            const unit = row.querySelector(`input[name*="_unit[]"]`).value;
            const qty = parseFloat(row.querySelector(`input[name*="_qty[]"]`).value) || 0;
            const rate = parseFloat(row.querySelector(`input[name*="_rate[]"]`).value) || 0;
            const amount = qty * rate;
            
            row.querySelector('.amount-cell').innerText = amount.toLocaleString(undefined, {minimumFractionDigits:2});
            catTotal += amount;
            items.push({ no, desc, unit, qty, rate, amount });
        });

        const totalEl = document.getElementById(`total-${catId}`);
        if(totalEl) totalEl.innerText = catTotal.toLocaleString(undefined, {minimumFractionDigits:2});
        
        const summaryEl = document.getElementById(`summ-${catId}`);
        if(summaryEl) summaryEl.innerText = catTotal.toLocaleString(undefined, {minimumFractionDigits:2});

        grandSubtotal += catTotal;
        boqData.categories.push({ id: catId, title: catTitle, items: items, total: catTotal });
    });

    const vat = grandSubtotal * 0.15;
    const grand = grandSubtotal + vat;

    document.getElementById('summ-subtotal').innerText = grandSubtotal.toLocaleString();
    document.getElementById('summ-vat').innerText = vat.toLocaleString();
    document.getElementById('summ-grand').innerText = grand.toLocaleString();

    document.getElementById('grand-no-vat').innerText = grandSubtotal.toLocaleString();
    document.getElementById('grand-vat-val').innerText = vat.toLocaleString();
    document.getElementById('grand-total-final').innerText = grand.toLocaleString();
    document.getElementById('grand-final-a').innerText = grand.toLocaleString();

    boqData.totals = { subtotal: grandSubtotal, vat, grand };
    document.getElementById('boq_json_input').value = JSON.stringify(boqData);
}

// Global listener for existing inputs
document.addEventListener('input', (e) => {
    if(e.target.classList.contains('calc-trigger')) {
        calculateBOQ();
    }
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
.btn-delete-row {
    background: transparent;
    border: none;
    color: #ff4444;
    cursor: pointer;
    font-size: 1rem;
    opacity: 0.5;
    transition: opacity 0.3s;
}
.btn-delete-row:hover {
    opacity: 1;
}
.cat-title-input {
    background: transparent;
    border: none;
    color: white;
    font-weight: bold;
    width: 100%;
    text-transform: uppercase;
}
.cat-title-input:hover, .cat-title-input:focus {
    background: rgba(255,255,255,0.05);
    outline: none;
    border-radius: 4px;
}
.no-border td { border: none !important; }
</style>
