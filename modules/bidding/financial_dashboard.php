<?php
// modules/finance/dashboard.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/BidManager.php';
require_once __DIR__ . '/../../includes/GMManager.php'; // For GM Actions

AuthManager::requireRole(['FINANCE_HEAD', 'FINANCE_TEAM', 'GM']);

$user_role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];
$db = Database::getInstance();

$msg = '';
$err = '';

// --- ACTION HANDLERS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action']) && $_POST['action'] === 'submit_financial') {
            // Finance Team: Submit Breakdown
            if (!in_array($user_role, ['FINANCE_HEAD', 'FINANCE_TEAM'])) throw new Exception("Unauthorized.");
            
            $data = [
                'labor_cost' => $_POST['labor_cost'],
                'material_cost' => $_POST['material_cost'],
                'equipment_cost' => $_POST['equipment_cost'],
                'overhead_cost' => $_POST['overhead_cost'],
                'tax' => $_POST['tax'],
                'profit_margin' => $_POST['profit_margin'],
                'total_amount' => $_POST['total_amount'],
                'document_path' => null // Handle upload below
            ];

            // File Upload
            if (isset($_FILES['fin_doc']) && $_FILES['fin_doc']['error'] === 0) {
                $ext = pathinfo($_FILES['fin_doc']['name'], PATHINFO_EXTENSION);
                if (!in_array(strtolower($ext), ['pdf', 'xlsx', 'xls'])) throw new Exception("Invalid file type.");
                $path = 'uploads/finance/BID_' . $_POST['bid_id'] . '_' . time() . '.' . $ext;
                if (!is_dir('uploads/finance')) mkdir('uploads/finance', 0777, true);
                move_uploaded_file($_FILES['fin_doc']['tmp_name'], $path);
                $data['document_path'] = $path;
            } else {
                throw new Exception("Financial Document is mandatory.");
            }

            BidManager::submitFinancialBreakdown($_POST['bid_id'], $data, $user_id);
            $msg = "Financial bid submitted successfully.";
        
        } elseif (isset($_POST['action']) && ($_POST['action'] === 'won' || $_POST['action'] === 'loss')) {
            // GM: Final Decision
            if ($user_role !== 'GM') throw new Exception("Unauthorized.");
            GMManager::processApproval('BIDS', $_POST['bid_id'], $_POST['action'], $_POST['reason'] ?? '', $user_id);
            $msg = "Bid marked as " . strtoupper($_POST['action']);
        }
    } catch (Exception $e) {
        $err = $e->getMessage();
    }
}

// --- DATA FETCHING ---
// 1. Incoming Bids (Approved by GM, waiting for Finance)
$incoming_bids = $db->query("SELECT * FROM bids WHERE status = 'GM_PRE_APPROVED'")->fetchAll();

// 2. Pending Final Review (Submitted by Finance, waiting for GM)
$submitted_bids = $db->query("
    SELECT b.*, f.total_amount, f.updated_at as submitted_at 
    FROM bids b 
    JOIN financial_bids f ON b.id = f.bid_id 
    WHERE b.status = 'FINANCE_FINAL_REVIEW'
")->fetchAll();

// 3. History (Won/Lost)
$history_bids = $db->query("SELECT * FROM bids WHERE status IN ('WON', 'LOSS') ORDER BY id DESC LIMIT 20")->fetchAll();

?>

<div class="finance-dashboard">
    <div class="section-header mb-4">
        <h2><i class="fas fa-coins text-gold"></i> Financial Proposal Center</h2>
    </div>

    <?php if ($msg): ?><div class="alert alert-success glass-card"><?= $msg ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-danger glass-card"><?= $err ?></div><?php endif; ?>

    <!-- KPI Cards -->
    <div class="widget-grid mb-4">
        <div class="widget glass-card widget-blue">
            <div class="widget-header"><h3>Active Pipeline</h3></div>
            <div class="widget-content"><div class="value"><?= count($incoming_bids) ?></div><small>Pending Pricing</small></div>
        </div>
        <div class="widget glass-card widget-gold">
            <div class="widget-header"><h3>Under Review</h3></div>
            <div class="widget-content"><div class="value"><?= count($submitted_bids) ?></div><small>With GM</small></div>
        </div>
        <div class="widget glass-card widget-green">
            <div class="widget-header"><h3>Won Bids</h3></div>
            <div class="widget-content"><div class="value">$<?= number_format($db->query("SELECT SUM(estimated_value) FROM bids WHERE status='WON'")->fetchColumn() ?: 0, 2) ?></div></div>
        </div>
    </div>

    <!-- TABS -->
    <div class="tabs mb-4">
        <button class="tab-btn active btn-secondary-sm" onclick="showTab('incoming')">Incoming Queue (<?= count($incoming_bids) ?>)</button>
        <button class="tab-btn btn-secondary-sm" onclick="showTab('submitted')">Awaiting Decision (<?= count($submitted_bids) ?>)</button>
        <button class="tab-btn btn-secondary-sm" onclick="showTab('history')">History</button>
    </div>

    <!-- TAB 1: INCOMING -->
    <div id="tab-incoming" class="tab-pane active glass-card p-4">
        <h3 class="mb-3 text-gold">Pending Financial Analysis</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Ref #</th>
                    <th>Project</th>
                    <th>Client</th>
                    <th>Est. Value</th>
                    <th>Deadline</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($incoming_bids)): ?><tr><td colspan="6" class="text-center text-dim">No incoming bids.</td></tr><?php endif; ?>
                <?php foreach ($incoming_bids as $b): ?>
                <tr>
                    <td class="text-gold mono"><?= $b['tender_no'] ?></td>
                    <td><strong><?= $b['title'] ?></strong></td>
                    <td><?= $b['client_name'] ?></td>
                    <td>$<?= number_format($b['estimated_value'], 2) ?></td>
                    <td><?= $b['deadline'] ?></td>
                    <td>
                        <?php if (in_array($user_role, ['FINANCE_HEAD', 'FINANCE_TEAM'])): ?>
                        <button class="btn-primary-sm" onclick='openFinanceModal(<?= json_encode($b) ?>)'>Prepare Breakdown</button>
                        <?php else: ?>
                        <span class="text-dim">Awaiting Finance</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- TAB 2: SUBMITTED (GM VIEW) -->
    <div id="tab-submitted" class="tab-pane glass-card p-4" style="display:none;">
        <h3 class="mb-3 text-gold">Final Review Queue</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Ref #</th>
                    <th>Project</th>
                    <th>Final Bid Amount</th>
                    <th>Submitted</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($submitted_bids)): ?><tr><td colspan="6" class="text-center text-dim">No bids under review.</td></tr><?php endif; ?>
                <?php foreach ($submitted_bids as $b): ?>
                <tr>
                    <td class="text-gold mono"><?= $b['tender_no'] ?></td>
                    <td><?= $b['title'] ?></td>
                    <td class="text-success text-bold">$<?= number_format($b['total_amount'], 2) ?></td>
                    <td><?= $b['submitted_at'] ?></td>
                    <td><span class="status-badge FINANCE_FINAL_REVIEW">REVIEWING</span></td>
                    <td>
                        <?php if ($user_role === 'GM'): ?>
                            <button onclick="submitDecision('won', <?= $b['id'] ?>)" class="btn-primary-sm bg-green text-black mr-2">WON</button>
                            <button onclick="submitDecision('loss', <?= $b['id'] ?>)" class="btn-primary-sm bg-red text-white">LOST</button>
                        <?php else: ?>
                            <span class="text-dim">Locked</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- TAB 3: HISTORY -->
    <div id="tab-history" class="tab-pane glass-card p-4" style="display:none;">
        <h3 class="mb-3 text-gold">Archive</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Ref #</th>
                    <th>Project</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($history_bids as $b): ?>
                <tr>
                    <td class="mono"><?= $b['tender_no'] ?></td>
                    <td><?= $b['title'] ?></td>
                    <td><span class="status-badge <?= $b['status'] ?>"><?= $b['status'] ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- FINANCE MODAL -->
<div id="finModal" class="modal-overlay" style="display:none;">
    <div class="glass-card modal-content" style="max-width: 800px;">
        <h3 class="text-gold mb-4"><i class="fas fa-calculator"></i> Financial Breakdown: <span id="modalTenderNo"></span></h3>
        <form method="POST" enctype="multipart/form-data" id="calcForm">
            <input type="hidden" name="action" value="submit_financial">
            <input type="hidden" name="bid_id" id="modalBidId">

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <!-- Costs -->
                <div>
                    <h4 class="mb-3 border-bottom-dim">Direct Costs</h4>
                    <div class="form-group"><label>Labor Cost</label><input type="number" step="0.01" name="labor_cost" class="calc-input" required></div>
                    <div class="form-group"><label>Material Cost</label><input type="number" step="0.01" name="material_cost" class="calc-input" required></div>
                    <div class="form-group"><label>Equipment Cost</label><input type="number" step="0.01" name="equipment_cost" class="calc-input" required></div>
                    <div class="form-group"><label>Overhead / Indirect</label><input type="number" step="0.01" name="overhead_cost" class="calc-input" required></div>
                </div>
                <!-- Totals -->
                <div>
                    <h4 class="mb-3 border-bottom-dim">Profit & Final</h4>
                    <div class="form-group"><label>Tax / VAT amount</label><input type="number" step="0.01" name="tax" class="calc-input" required></div>
                    <div class="form-group"><label>Profit Margin (%)</label><input type="number" step="0.1" name="profit_margin" id="margin" value="15.0" required></div>
                    
                    <div class="p-3 bg-dark-glass mt-4 text-center">
                        <label>TOTAL BID AMOUNT</label>
                        <div class="text-gold text-xl" id="totalDisplay">$0.00</div>
                        <input type="hidden" name="total_amount" id="grandTotal">
                    </div>

                    <div class="form-group mt-4">
                        <label>Detailed Budget File (PDF/Excel)</label>
                        <input type="file" name="fin_doc" required>
                    </div>
                </div>
            </div>

            <div class="modal-actions mt-4 border-top-dim pt-3">
                <button type="button" class="btn-secondary-sm" onclick="closeFinModal()">Cancel</button>
                <button type="submit" class="btn-primary-sm">Finalize & Submit to GM</button>
            </div>
        </form>
    </div>
</div>

<!-- GM Decision Form (Hidden) -->
<form method="POST" id="gmForm" style="display:none;">
    <input type="hidden" name="action" id="gmAction">
    <input type="hidden" name="bid_id" id="gmBidId">
    <input type="hidden" name="reason" value="Executive Decision">
</form>

<style>
.bg-green { background: #00ff64 !important; }
.bg-red { background: #ff4444 !important; }
.bg-dark-glass { background: rgba(0,0,0,0.3); border-radius: 8px; }
.text-xl { font-size: 2rem; font-weight: bold; }
.mono { font-family: monospace; }
.border-bottom-dim { border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 0.5rem; }
.border-top-dim { border-top: 1px solid rgba(255,255,255,0.1); }
</style>

<script>
function showTab(t) {
    document.querySelectorAll('.tab-pane').forEach(e => e.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(e => e.classList.remove('active'));
    document.getElementById('tab-'+t).style.display = 'block';
    event.currentTarget.classList.add('active');
}

function openFinanceModal(bid) {
    document.getElementById('finModal').style.display = 'flex';
    document.getElementById('modalTenderNo').innerText = bid.tender_no;
    document.getElementById('modalBidId').value = bid.id;
}
function closeFinModal() {
    document.getElementById('finModal').style.display = 'none';
}
function submitDecision(action, id) {
    if(!confirm('Are you sure you want to mark this bid as ' + action.toUpperCase() + '? This cannot be undone.')) return;
    document.getElementById('gmAction').value = action;
    document.getElementById('gmBidId').value = id;
    document.getElementById('gmForm').submit();
}

// Auto Calc
const inputs = document.querySelectorAll('.calc-input');
const marginInput = document.getElementById('margin');

function calculate() {
    let subtotal = 0;
    inputs.forEach(i => subtotal += parseFloat(i.value || 0));
    
    let margin = parseFloat(marginInput.value || 0);
    let total = subtotal + (subtotal * (margin / 100)); // Simple Markup Logic
    
    document.getElementById('totalDisplay').innerText = '$' + total.toFixed(2);
    document.getElementById('grandTotal').value = total.toFixed(2);
}

inputs.forEach(i => i.addEventListener('input', calculate));
marginInput.addEventListener('input', calculate);
</script>
