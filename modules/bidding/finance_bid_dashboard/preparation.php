<?php
// modules/bidding/finance_bid_dashboard/preparation.php

$tender_id = $_GET['id'] ?? null;
if (!$tender_id) {
    echo "<div style='padding: 50px; text-align: center; border: 1px dashed rgba(255,255,255,0.1); border-radius: 20px;'>
            <i class='fas fa-exclamation-triangle fa-2x text-gold mb-3'></i>
            <h4 class='text-white'>No Target Bid Selected</h4>
            <p class='text-secondary'>Please select a bid from the Coordination Board to initialize financial modeling.</p>
          </div>";
    return;
}

$db = Database::getInstance();
$stmt = $db->prepare("SELECT * FROM bids WHERE id = ?");
$stmt->execute([$tender_id]);
$tender = $stmt->fetch();

$stmt = $db->prepare("SELECT * FROM financial_bids WHERE bid_id = ?");
$stmt->execute([$tender_id]);
$fin_bid = $stmt->fetch();

// Decode existing data
$boq_data = [];
if ($fin_bid && !empty($fin_bid['boq_json'])) {
    $json = json_decode($fin_bid['boq_json'], true);
    $boq_data = $json['boq_structure'] ?? []; // New structure key
    $tax_percent = $json['tax_percent'] ?? 15;
}

// Default Structure if empty
if (empty($boq_data)) {
    $boq_data = [
        [
            'type' => 'section',
            'name' => 'A. SUB STRUCTURE',
            'children' => [
                [
                    'type' => 'subsection',
                    'name' => '1. EXCAVATION & EARTH WORK',
                    'children' => [
                         ['type' => 'item', 'no' => '1.1', 'desc' => 'Site clearance and top soil removal', 'unit' => 'm2', 'qty' => 0, 'rate' => 0, 'amount' => 0]
                    ]
                ]
            ]
        ]
    ];
}
?>

<div class="preparation-module">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h2 style="margin: 0; font-size: 1.75rem; color: #fff;"><i class="fas fa-file-invoice-dollar text-gold"></i> Manual BOQ <span style="font-weight: 300;">Engine</span></h2>
            <div style="display: flex; align-items: center; gap: 15px; margin-top: 8px;">
                <span class="badge-premium" style="background: rgba(255,204,0,0.1); color: #ffcc00;"><?= $tender['tender_no'] ?></span>
                <span class="text-secondary" style="font-size: 0.9rem;"><?= htmlspecialchars($tender['title']) ?></span>
            </div>
        </div>
        <div style="text-align: right;">
            <div style="color: rgba(255,255,255,0.4); font-size: 0.8rem; text-transform: uppercase;">Editing Mode</div>
            <div style="color: var(--accent-green); font-weight: bold;"><i class="fas fa-pen"></i> Direct Entry</div>
        </div>
    </div>

    <form action="main.php?module=bidding/finance_bid_dashboard/save_preparation" method="POST" id="boqForm" enctype="multipart/form-data">
        <input type="hidden" name="bid_id" value="<?= $tender_id ?>">
        <input type="hidden" name="fin_bid_id" value="<?= $fin_bid['id'] ?? '' ?>">
        <input type="hidden" name="boq_structure_json" id="boq_structure_json">
        
        <div style="display: grid; grid-template-columns: 1fr 350px; gap: 24px;">
            <!-- BOQ EDITOR TABLE -->
            <div class="premium-card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
                <div style="padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: space-between;">
                    <h4 style="margin: 0; color: #fff;">Bill of Quantities</h4>
                    <div style="font-size: 0.8rem; color: rgba(255,255,255,0.4);">
                        <i class="fas fa-info-circle"></i> Amounts are manual. Warnings shown for logic errors.
                    </div>
                </div>

                <div class="boq-container" style="padding: 2px;">
                    <div class="boq-header-row">
                        <div style="width: 80px;">Ref No</div>
                        <div style="flex: 1;">Description</div>
                        <div style="width: 60px;">Unit</div>
                        <div style="width: 80px;">Qty</div>
                        <div style="width: 100px;">Rate</div>
                        <div style="width: 120px;">Amount</div>
                        <div style="width: 100px; text-align: right; padding-right: 10px;">Actions</div>
                    </div>
                    
                    <div id="boq-rows">
                        <!-- JS GENERATED ROWS -->
                    </div>
                    
                    <div style="padding: 20px; border-top: 1px solid rgba(255,255,255,0.05);">
                        <button type="button" class="btn-secondary-sm" onclick="addRootSection()">+ Add Main Section</button>
                    </div>
                </div>
            </div>

            <!-- SIDEBAR SUMMARY -->
            <div style="position: sticky; top: 20px; height: fit-content;">
                <div class="premium-card">
                    <h3 style="color: var(--gold); margin: 0 0 1.5rem 0; font-size: 1.25rem;">Financial Summary</h3>
                    
                    <div id="summary-container" style="margin-bottom: 1.5rem;">
                        <!-- JS GENERATED SECTION TOTALS -->
                    </div>

                    <div style="border-top: 1px dashed rgba(255,255,255,0.1); margin: 10px 0;"></div>

                    <div class="summary-row">
                        <span>Net Total</span>
                        <span id="disp-net" style="color: #fff;">$0.00</span>
                    </div>
                     <div class="summary-row" style="margin-top: 10px;">
                        <span>VAT / Tax (%)</span>
                        <div style="width: 80px; text-align: right;">
                             <input type="number" id="tax_percent" name="tax_percent" value="<?= $tax_percent ?? 15 ?>" 
                                    style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 2px 5px; border-radius: 4px; text-align: right;" 
                                    onchange="recalcTotals()">
                        </div>
                    </div>
                    <div class="summary-row">
                        <span>VAT Amount</span>
                        <span id="disp-tax" style="color: #ff4444;">$0.00</span>
                    </div>

                    <div style="margin-top: 1.5rem; padding: 15px; background: rgba(255,204,0,0.05); border-radius: 12px; text-align: center;">
                        <div style="font-size: 0.75rem; text-transform: uppercase; color: rgba(255,255,255,0.4);">Grand Total</div>
                        <div id="disp-grand" style="font-size: 1.75rem; font-weight: 800; color: var(--gold);">$0.00</div>
                    </div>
                    
                    <input type="hidden" name="total_amount" id="total_amount_input">

                    <div style="margin-top: 2rem;">
                         <h5 style="font-size: 0.9rem; color: #fff; margin-bottom: 10px;">Attachments</h5>
                         <input type="file" name="bid_doc" style="width: 100%; font-size: 0.8rem; color: rgba(255,255,255,0.5);">
                    </div>

                    <div style="margin-top: 2rem; display: flex; flex-direction: column; gap: 10px;">
                        <button type="button" onclick="saveDraft()" class="btn-secondary-sm">Save Draft</button>
                        <button type="submit" name="action" value="send_verification" class="btn-primary-sm">Submit for Verification</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<template id="tpl-section">
    <div class="boq-row section-row" data-id="{id}">
        <div class="row-content">
            <div data-field="no" class="cell-no" contenteditable="true">A.</div>
            <div data-field="name" class="cell-desc" contenteditable="true" style="font-weight: bold; color: var(--gold);">NEW SECTION</div>
            <div class="cell-empty"></div> <!-- Unit -->
            <div class="cell-empty"></div> <!-- Qty -->
            <div class="cell-empty"></div> <!-- Rate -->
            <div class="cell-amount readonly" data-field="total">$0.00</div>
            <div class="cell-actions">
                <button type="button" onclick="addChild('{id}', 'subsection')" title="Add Subsection"><i class="fas fa-plus"></i></button>
                <button type="button" onclick="deleteRow('{id}')" class="text-red" title="Delete Section"><i class="fas fa-trash"></i></button>
            </div>
        </div>
        <div class="children-container"></div>
    </div>
</template>

<template id="tpl-subsection">
    <div class="boq-row subsection-row" data-id="{id}">
        <div class="row-content">
            <div data-field="no" class="cell-no" contenteditable="true">1.</div>
            <div data-field="name" class="cell-desc" contenteditable="true" style="font-weight: 600; padding-left: 20px;">New Subsection</div>
            <div class="cell-empty"></div>
            <div class="cell-empty"></div>
            <div class="cell-empty"></div>
            <div class="cell-amount readonly" data-field="total">$0.00</div>
            <div class="cell-actions">
                <button type="button" onclick="addChild('{id}', 'item')" title="Add Item"><i class="fas fa-plus"></i></button>
                <button type="button" onclick="deleteRow('{id}')" class="text-red" title="Delete Subsection"><i class="fas fa-trash"></i></button>
            </div>
        </div>
        <div class="children-container"></div>
    </div>
</template>

<template id="tpl-item">
    <div class="boq-row item-row" data-id="{id}">
        <div class="row-content">
            <div data-field="no" class="cell-no" contenteditable="true">1.1</div>
            <div data-field="desc" class="cell-desc" contenteditable="true" style="padding-left: 40px; color: rgba(255,255,255,0.8);">New Item</div>
            <div data-field="unit" class="cell-unit" contenteditable="true">pcs</div>
            <div data-field="qty" class="cell-num input-cell" contenteditable="true" oninput="autoCalc('{id}')">0</div>
            <div data-field="rate" class="cell-num input-cell" contenteditable="true" oninput="autoCalc('{id}')">0</div>
            <div data-field="amount" class="cell-num input-cell manual-amount" contenteditable="true" oninput="recalcTotals()" style="color: var(--accent-green); font-weight: bold;">0</div>
            <div class="cell-actions">
                <span class="math-warning" title="Qty * Rate != Amount mismatch"><i class="fas fa-exclamation-circle"></i></span>
                <button type="button" onclick="deleteRow('{id}')" class="text-red" title="Delete Item"><i class="fas fa-times"></i></button>
            </div>
        </div>
    </div>
</template>

<style>
    .boq-header-row {
        display: flex;
        background: rgba(255,255,255,0.05);
        border-radius: 8px;
        padding: 10px;
        font-size: 0.75rem;
        font-weight: bold;
        text-transform: uppercase;
        color: rgba(255,255,255,0.5);
        margin-bottom: 5px;
    }
    .boq-row { display: flex; flex-direction: column; }
    .row-content {
        display: flex;
        padding: 8px 10px;
        border-bottom: 1px solid rgba(255,255,255,0.02);
        align-items: center;
        transition: background 0.2s;
    }
    .row-content:hover { background: rgba(255,255,255,0.02); }
    
    .section-row > .row-content { background: rgba(255,204,0,0.05); border-left: 3px solid var(--gold); margin-top: 15px; border-radius: 4px; }
    .subsection-row > .row-content { background: rgba(255,255,255,0.01); border-left: 3px solid rgba(255,255,255,0.1); margin-top: 5px; }

    .cell-no { width: 80px; font-family: monospace; color: rgba(255,255,255,0.5); outline: none; }
    .cell-desc { flex: 1; outline: none; }
    .cell-unit { width: 60px; text-align: center; color: rgba(255,255,255,0.5); font-size: 0.85rem; outline: none; }
    .cell-num { width: 80px; text-align: right; font-family: monospace; outline: none; border-bottom: 1px dotted rgba(255,255,255,0.1); }
    .cell-num:focus { border-bottom: 1px solid var(--gold); background: rgba(0,0,0,0.2); }
    .cell-amount { width: 120px; text-align: right; font-family: monospace; }
    .cell-actions { width: 100px; display: flex; justify-content: flex-end; gap: 8px; padding-right: 5px; }
    .cell-actions button { 
        width: 30px; 
        height: 30px; 
        border-radius: 8px; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        background: rgba(255,255,255,0.08); 
        border: 1px solid rgba(255,255,255,0.1); 
        color: rgba(255,255,255,0.6); 
        cursor: pointer; 
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); 
        font-size: 0.8rem;
    }
    .cell-actions button:hover { 
        background: var(--gold); 
        color: #000; 
        border-color: var(--gold); 
        transform: scale(1.1) translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 204, 0, 0.3);
    }
    .cell-actions button.text-red:hover { 
        background: #ff4444; 
        color: #fff; 
        border-color: #ff4444; 
        box-shadow: 0 4px 12px rgba(255, 68, 68, 0.3);
    }

    .math-warning { color: #ff9800; font-size: 0.8rem; margin-right: 5px; opacity: 0; }
    .math-warning.visible { opacity: 1; }
    
    .summary-row { display: flex; justify-content: space-between; font-size: 0.9rem; color: rgba(255,255,255,0.6); padding: 4px 0; }
</style>

<script>
    let boqData = <?= json_encode($boq_data) ?>;
    const container = document.getElementById('boq-rows');

    // 1. RENDER LOGIC
    function render() {
        container.innerHTML = '';
        boqData.forEach((section, idx) => renderSection(section, container, [idx]));
        recalcTotals();
    }

    function renderSection(data, parentEl, path) {
        const tpl = document.getElementById('tpl-section').innerHTML;
        const div = document.createElement('div');
        div.innerHTML = tpl.replace(/{id}/g, path.join('-'));
        const el = div.firstElementChild;
        
        // Populate inputs
        el.querySelector('[data-field="no"]').innerText = data.name.split(' ')[0]; // Approx
        el.querySelector('[data-field="name"]').innerText = data.name;

        // Append children
        const childCont = el.querySelector('.children-container');
        if(data.children) {
            data.children.forEach((child, idx) => {
                if(child.type === 'subsection') renderSubsection(child, childCont, [...path, idx]);
            });
        }
        
        parentEl.appendChild(el);
    }

    function renderSubsection(data, parentEl, path) {
        const tpl = document.getElementById('tpl-subsection').innerHTML;
        const div = document.createElement('div');
        div.innerHTML = tpl.replace(/{id}/g, path.join('-'));
        const el = div.firstElementChild;

        el.querySelector('[data-field="no"]').innerText = data.name.split(' ')[0];
        el.querySelector('[data-field="name"]').innerText = data.name;

        const childCont = el.querySelector('.children-container');
        if(data.children) {
            data.children.forEach((child, idx) => {
                if(child.type === 'item') renderItem(child, childCont, [...path, idx]);
            });
        }
        parentEl.appendChild(el);
    }

    function renderItem(data, parentEl, path) {
        const tpl = document.getElementById('tpl-item').innerHTML;
        const div = document.createElement('div');
        div.innerHTML = tpl.replace(/{id}/g, path.join('-'));
        const el = div.firstElementChild;

        el.querySelector('[data-field="no"]').innerText = data.no;
        el.querySelector('[data-field="desc"]').innerText = data.desc;
        el.querySelector('[data-field="unit"]').innerText = data.unit;
        el.querySelector('[data-field="qty"]').innerText = data.qty;
        el.querySelector('[data-field="rate"]').innerText = data.rate;
        el.querySelector('[data-field="amount"]').innerText = data.amount;

        parentEl.appendChild(el);
    }

    // 2. INTERACTION LOGIC
    // Since contenteditable is messy for syncing back to JSON on every keypress, 
    // we will "scrape" the DOM to rebuild JSON on save/calc.
    
    function scrapeData() {
        const newData = [];
        document.querySelectorAll('#boq-rows > .section-row').forEach(secEl => {
            const sec = {
                type: 'section',
                name: secEl.querySelector('.row-content [data-field="name"]').innerText,
                children: []
            };
            
            secEl.querySelectorAll(':scope > .children-container > .subsection-row').forEach(subEl => {
                const sub = {
                    type: 'subsection',
                    name: subEl.querySelector('.row-content [data-field="name"]').innerText,
                    children: []
                };

                subEl.querySelectorAll(':scope > .children-container > .item-row').forEach(itemEl => {
                    sub.children.push({
                        type: 'item',
                        no: itemEl.querySelector('[data-field="no"]').innerText,
                        desc: itemEl.querySelector('[data-field="desc"]').innerText,
                        unit: itemEl.querySelector('[data-field="unit"]').innerText,
                        qty: parseFloat(itemEl.querySelector('[data-field="qty"]').innerText) || 0,
                        rate: parseFloat(itemEl.querySelector('[data-field="rate"]').innerText) || 0,
                        amount: parseFloat(itemEl.querySelector('[data-field="amount"]').innerText) || 0
                    });
                });
                sec.children.push(sub);
            });
            newData.push(sec);
        });
        boqData = newData;
        return newData;
    }

    function addRootSection() {
        boqData.push({ type: 'section', name: 'NEW SECTION', children: [] });
        render();
    }

    window.addChild = function(idPath, type) {
        scrapeData(); // Sync current state
        const indices = idPath.split('-');
        let ptr = boqData[indices[0]];
        if(indices.length > 1) ptr = ptr.children[indices[1]]; // subsection
        
        if(type === 'subsection') {
            ptr.children.push({ type: 'subsection', name: 'New Subsection', children: [] });
        } else {
            ptr.children.push({ type: 'item', no: 'x.x', desc: 'Item description...', unit: 'ls', qty: 1, rate: 0, amount: 0 });
        }
        render(); // Re-render all to keep it simple
    };

    window.deleteRow = function(idPath) {
        scrapeData();
        const indices = idPath.split('-');
        // Logic to remove
        if(indices.length === 1) boqData.splice(indices[0], 1);
        if(indices.length === 2) boqData[indices[0]].children.splice(indices[1], 1);
        if(indices.length === 3) boqData[indices[0]].children[indices[1]].children.splice(indices[2], 1);
        render();
    };

    window.autoCalc = function(id) {
        const el = document.querySelector(`.item-row[data-id="${id}"]`);
        if(!el) return;
        const qty = parseFloat(el.querySelector('[data-field="qty"]').innerText) || 0;
        const rate = parseFloat(el.querySelector('[data-field="rate"]').innerText) || 0;
        
        const calc = qty * rate;
        const amtField = el.querySelector('[data-field="amount"]');
        
        // Update the amount field live with 2 decimal precision
        amtField.innerText = calc.toFixed(2);
        
        recalcTotals();
    }

    window.recalcTotals = function() {
        // Don't fully scrape, just sum DOM to avoid caret jumping (if we re-rendered)
        // But scraping is standard. For this demo, let's scrape on Calc to update the Summary Sidebar
        // Note: we only re-render on structure change.
        
        const summaryCont = document.getElementById('summary-container');
        summaryCont.innerHTML = '';
        
        let netTotal = 0;

        document.querySelectorAll('#boq-rows > .section-row').forEach(secEl => {
            let secTotal = 0;
            const secName = secEl.querySelector('.row-content [data-field="name"]').innerText;

            secEl.querySelectorAll('.item-row').forEach(item => {
                const val = parseFloat(item.querySelector('[data-field="amount"]').innerText) || 0;
                secTotal += val;
                netTotal += val;
            });

            // Update section total text in UI
            secEl.querySelector('.row-content [data-field="total"]').innerText = '$' + secTotal.toLocaleString();

            // Add to Sidebar
            const div = document.createElement('div');
            div.className = 'summary-row';
            div.innerHTML = `<span>${secName}</span><span>$${secTotal.toLocaleString(undefined, {minimumFractionDigits:2})}</span>`;
            summaryCont.appendChild(div);
        });

        // Totals
        const taxPct = parseFloat(document.getElementById('tax_percent').value) || 0;
        const taxAmt = netTotal * (taxPct / 100);
        const grand = netTotal + taxAmt;

        document.getElementById('disp-net').innerText = '$' + netTotal.toLocaleString(undefined, {minimumFractionDigits:2});
        document.getElementById('disp-tax').innerText = '$' + taxAmt.toLocaleString(undefined, {minimumFractionDigits:2});
        document.getElementById('disp-grand').innerText = '$' + grand.toLocaleString(undefined, {minimumFractionDigits:2});
        
        document.getElementById('total_amount_input').value = grand;
    };

    window.saveDraft = function() {
        scrapeData();
        document.getElementById('boq_structure_json').value = JSON.stringify(boqData);
        // Change action to prevent validation
        const btn = document.createElement('input');
        btn.type = 'hidden';
        btn.name = 'action';
        btn.value = 'save_draft';
        document.getElementById('boqForm').appendChild(btn);
        document.getElementById('boqForm').submit();
    }
    
    // Intercept form submit
    document.getElementById('boqForm').addEventListener('submit', function(e) {
        scrapeData();
        document.getElementById('boq_structure_json').value = JSON.stringify(boqData);
    });

    // Init
    render();

</script>


