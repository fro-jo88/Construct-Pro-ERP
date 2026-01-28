<?php
// modules/gm/approvals.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/GMManager.php';

AuthManager::requireRole('GM');

$db = Database::getInstance();
$user_id = $_SESSION['user_id'];
$msg = '';

// Handle Unified Approval Post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        $module = $_POST['module'];
        $ref_id = $_POST['ref_id'];
        $decision = $_POST['action']; // 'approved', 'rejected', 'queried'
        $reason = $_POST['reason'] ?? '';

        GMManager::processApproval($module, $ref_id, $decision, $reason, $user_id);
        $msg = "Decision recorded successfully.";
    } catch (Exception $e) {
        $msg = "Error: " . $e->getMessage();
    }
}

// Fetch Pending data for all relevant modules
$pending_hr = $db->query("SELECT e.*, u.username, r.role_name FROM employees e JOIN users u ON e.user_id = u.id JOIN roles r ON u.role_id = r.id WHERE e.gm_approval_status = 'pending'")->fetchAll();
// Bids for Pre-Approval
$pre_approval_bids = $db->query("SELECT b.*, u.username as creator FROM bids b JOIN users u ON b.created_by = u.id WHERE b.status = 'FINANCIAL_COMPLETED'")->fetchAll();
// Bids for Final WON/LOSS Decision
$final_decision_bids = $db->query("SELECT b.*, u.username as creator FROM bids b JOIN users u ON b.created_by = u.id WHERE b.status = 'FINANCE_FINAL_REVIEW'")->fetchAll();
$pending_bid_count = count($pre_approval_bids) + count($final_decision_bids);

$pending_budgets = [];
try {
    $pending_budgets = $db->query("SELECT b.*, p.project_name FROM budgets b JOIN projects p ON b.project_id = p.id WHERE b.status = 'pending'")->fetchAll();
} catch (Exception $e) {
    // Column missing
}

$pending_procurement = $db->query("SELECT pr.*, p.project_name, s.site_name, u.username as requester 
                                   FROM purchase_requests pr 
                                   JOIN projects p ON pr.project_id = p.id 
                                   LEFT JOIN sites s ON pr.site_id = s.id 
                                   JOIN users u ON pr.requested_by = u.id 
                                   WHERE pr.status IN ('pending', 'finance_approved')")->fetchAll();

$pending_releases = $db->query("SELECT mr.*, s.site_name, u.username as requester 
                                FROM material_requests mr 
                                JOIN sites s ON mr.site_id = s.id 
                                JOIN users u ON mr.requested_by = u.id 
                                WHERE mr.gm_approval_status = 'pending'")->fetchAll();
?>

<div class="gm-approvals">
    <div class="section-header mb-4">
        <h2><i class="fas fa-stamp"></i> Executive Approval Command Center</h2>
        <p class="text-dim">Unified queue for all system-wide authorization requests.</p>
    </div>

    <?php if ($msg): ?>
        <div class="alert glass-card mb-4" style="color:var(--gold); border-left: 5px solid var(--gold);"><?= $msg ?></div>
    <?php endif; ?>

    <div class="approval-tabs">
        <div class="tab-header mb-4">
            <button class="tab-btn active" onclick="showTab('hr')">HR & Workforce (<?= count($pending_hr) ?>)</button>
            <button class="tab-btn" onclick="showTab('tenders')">Tenders & Bids (<?= $pending_bid_count ?>)</button>
            <button class="tab-btn" onclick="showTab('finance')">Finance & Budgets (<?= count($pending_budgets) ?>)</button>
            <button class="tab-btn" onclick="showTab('procurement')">Procurement (<?= count($pending_procurement) ?>)</button>
            <button class="tab-btn" onclick="showTab('inventory')">Inventory Releases (<?= count($pending_releases) ?>)</button>
        </div>

        <div id="tab-hr" class="tab-pane active">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Position</th>
                        <th>Salary</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pending_hr)): ?><tr><td colspan="4" class="text-center py-4">No pending personnel registrations.</td></tr><?php endif; ?>
                    <?php foreach ($pending_hr as $e): ?>
                    <tr>
                        <td style="font-family: monospace; color: var(--gold);"><?= $e['employee_code'] ?></td>
                        <td><strong><?= htmlspecialchars($e['full_name'] ?? '') ?></strong><br><small><?= $e['username'] ?> | <?= $e['role_name'] ?></small></td>
                        <td><?= $e['position'] ?> (<?= $e['department'] ?>)</td>
                        <td>$<?= number_format((float)($e['base_salary'] ?? 0), 2) ?></td>
                        <td>
                            <button class="btn-primary-sm" onclick='openApprovalModal("HR", <?= $e['id'] ?>, "<?= addslashes($e['full_name'] ?? '') ?>", <?= json_encode($e) ?>)'>Review Profile</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div id="tab-tenders" class="tab-pane" style="display:none;">
            <!-- Section 1: Pre-Approval Queue -->
            <h4 class="mb-3 text-gold"><i class="fas fa-hourglass-half"></i> Stage 1: Pre-Approval Queue</h4>
            <table class="data-table mb-5">
                <thead>
                    <tr>
                        <th>Bid #</th>
                        <th>Project Title</th>
                        <th>Originator</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pre_approval_bids)): ?><tr><td colspan="4" class="text-center py-4">No bids ready for pre-approval review.</td></tr><?php endif; ?>
                    <?php foreach ($pre_approval_bids as $at): ?>
                    <tr>
                        <td style="color:var(--gold);"><?= $at['tender_no'] ?></td>
                        <td><strong><?= $at['title'] ?></strong><br><small><?= $at['client_name'] ?></small></td>
                        <td><?= $at['creator'] ?></td>
                        <td>
                            <button class="btn-primary-sm" 
                                onclick="openApprovalModal('BIDS', <?= $at['id'] ?>, '<?= $at['tender_no'] ?>', {
                                    desc: '<?= addslashes($at['description'] ?? '') ?>',
                                    mode: '<?= $at['submission_mode'] ?? '' ?>',
                                    file: '<?= $at['bid_file'] ?? '' ?>',
                                    client: '<?= addslashes($at['client_name'] ?? '') ?>',
                                    title: '<?= addslashes($at['title'] ?? '') ?>',
                                    stage: 'pre'
                                })">Pre-Approve</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Section 2: Final Decision Queue -->
            <h4 class="mb-3 text-success"><i class="fas fa-flag-checkered"></i> Stage 2: Final Decision Queue (WON/LOSS)</h4>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Bid #</th>
                        <th>Project Title</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($final_decision_bids)): ?><tr><td colspan="4" class="text-center py-4">No bids awaiting final commercial decision.</td></tr><?php endif; ?>
                    <?php foreach ($final_decision_bids as $ft): ?>
                    <tr>
                        <td style="color:var(--gold); font-family:monospace;"><?= $ft['tender_no'] ?></td>
                        <td><strong><?= $ft['title'] ?></strong><br><small><?= $ft['client_name'] ?></small></td>
                        <td><span class="status-badge" style="background:rgba(255,255,255,0.1); color:#fff; border:1px solid rgba(255,255,255,0.2);">FINANCIAL FINALIZED</span></td>
                        <td>
                            <button class="btn-primary-sm" style="background:#00ff64; color:#000;" 
                                onclick="openApprovalModal('BIDS', <?= $ft['id'] ?>, '<?= $ft['tender_no'] ?>', {
                                    desc: '<?= addslashes($ft['description'] ?? '') ?>',
                                    mode: '<?= $ft['submission_mode'] ?? '' ?>',
                                    file: '<?= $ft['bid_file'] ?? '' ?>',
                                    client: '<?= addslashes($ft['client_name'] ?? '') ?>',
                                    title: '<?= addslashes($ft['title'] ?? '') ?>',
                                    stage: 'final'
                                })">Make Final Decision</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div id="tab-finance" class="tab-pane" style="display:none;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Project</th>
                        <th>Budget Title</th>
                        <th>Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pending_budgets)): ?><tr><td colspan="4" class="text-center py-4">No budget requests pending.</td></tr><?php endif; ?>
                    <?php foreach ($pending_budgets as $b): ?>
                    <tr>
                        <td><?= $b['project_name'] ?></td>
                        <td><?= $b['budget_name'] ?? 'General Budget' ?></td>
                        <td>$<?= number_format((float)($b['total_amount'] ?? 0), 2) ?></td>
                        <td>
                            <button class="btn-primary-sm" onclick="openApprovalModal('FINANCE', <?= $b['id'] ?>, 'Budget: <?= $b['budget_name'] ?? 'ID '.$b['id'] ?>')">Decide</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div id="tab-procurement" class="tab-pane" style="display:none;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Project / Site</th>
                        <th>Requester</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pending_procurement)): ?><tr><td colspan="4" class="text-center py-4">No purchase requests waiting.</td></tr><?php endif; ?>
                    <?php foreach ($pending_procurement as $pr): ?>
                    <tr>
                        <td><strong><?= $pr['project_name'] ?></strong><br><small><?= $pr['site_name'] ?: 'Office' ?></small></td>
                        <td><?= $pr['requester'] ?></td>
                        <td><span class="status-badge <?= $pr['status'] ?>"><?= strtoupper($pr['status']) ?></span></td>
                        <td>
                            <button class="btn-primary-sm" onclick="openApprovalModal('PROCUREMENT', <?= $pr['id'] ?>, 'PR for <?= $pr['project_name'] ?>')">Authorize PR</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div id="tab-inventory" class="tab-pane" style="display:none;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Site</th>
                        <th>Requester</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pending_releases)): ?><tr><td colspan="4" class="text-center py-4">No material releases waiting.</td></tr><?php endif; ?>
                    <?php foreach ($pending_releases as $mr): ?>
                    <tr>
                        <td><strong><?= $mr['site_name'] ?></strong></td>
                        <td><?= $mr['requester'] ?></td>
                        <td><span class="status-badge <?= $mr['gm_approval_status'] ?>"><?= strtoupper($mr['gm_approval_status']) ?></span></td>
                        <td>
                            <button class="btn-primary-sm" onclick="openApprovalModal('INVENTORY', <?= $mr['id'] ?>, 'Material for <?= $mr['site_name'] ?>')">Authorize Release</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Approval Decision Modal -->
<div id="approvalModal" class="modal-overlay" style="display:none;">
    <div class="glass-card modal-content">
        <h3 id="modalTitle">Process Approval</h3>
        <p id="modalSub" class="text-dim"></p>
        
        <!-- Details Section -->
        <div id="detailsBox" style="display:none; margin: 1.5rem 0; padding: 1.5rem; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px;">
            <div id="bidDetails" style="display:none;">
                <div class="mb-2"><strong>Project:</strong> <span id="detTitle"></span></div>
                <div class="mb-2"><strong>Client:</strong> <span id="detClient"></span></div>
                <div class="mb-2"><strong>Mode:</strong> <span id="detMode" style="color:var(--gold);"></span></div>
                <div class="mb-2"><strong>Description/Scope:</strong><br><p id="detDesc" style="font-size:0.9rem; color:#ccc; margin-top:0.5rem;"></p></div>
                <div id="fileLink" style="display:none; margin-top:1rem;">
                    <a id="detFile" href="#" target="_blank" class="btn-secondary-sm" style="display:inline-block; text-decoration:none;"><i class="fas fa-file-download mr-1"></i> View Technical Soft-Copy</a>
                </div>
            </div>

            <div id="hrDetails" style="display:none;">
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div>
                        <div class="text-gold mb-2" style="font-size:0.8rem; text-transform:uppercase;">Professional Info</div>
                        <div class="mb-1"><strong>ID:</strong> <span id="hrId"></span></div>
                        <div class="mb-1"><strong>Exp:</strong> <span id="hrExp"></span> Years</div>
                        <div class="mb-1"><strong>Type:</strong> <span id="hrType"></span></div>
                        
                        <div class="text-gold mt-3 mb-2" style="font-size:0.8rem; text-transform:uppercase;">Emergency Contact</div>
                        <div class="mb-1"><strong>Name:</strong> <span id="hrEmerName"></span></div>
                        <div class="mb-1"><strong>Phone:</strong> <span id="hrEmerPhone"></span></div>
                        <div class="mb-1"><strong>Rel:</strong> <span id="hrEmerRel"></span></div>
                    </div>
                    <div>
                        <div class="text-gold mb-2" style="font-size:0.8rem; text-transform:uppercase;">Education PDF</div>
                        <div id="pdfViewer" style="height: 200px; background: #000; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <a id="hrEduPdf" href="#" target="_blank" class="text-gold" style="text-decoration:none;"><i class="fas fa-file-pdf fa-2x mb-2"></i><br>Open Credentials PDF</a>
                        </div>
                        <div style="font-size:0.75rem; margin-top:0.5rem; color:var(--text-dim);">
                            <span id="hrEduLevel"></span> - <span id="hrEduField"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <form method="POST">
            <input type="hidden" name="module" id="modalModule">
            <input type="hidden" name="ref_id" id="modalRefId">
            <input type="hidden" name="action" id="modalAction">

            <div class="form-group mt-3">
                <label>Decision Comments / Reason (Mandatory for Rejection/Query)</label>
                <textarea name="reason" id="modalReason" required placeholder="Enter justification here..."></textarea>
            </div>

            <div class="modal-actions mt-4">
                <button type="button" class="btn-secondary-sm" onclick="closeApprovalModal()">Cancel</button>
                <div id="standardActions" style="display:flex; gap:0.5rem;">
                    <button type="button" onclick="submitDecision('queried')" class="btn-primary-sm" style="background:#ffcc00; color:black;">Query</button>
                    <button type="button" onclick="submitDecision('rejected')" class="btn-primary-sm" style="background:#ff4444; color:white;">Reject</button>
                    <button type="button" id="approveBtn" onclick="submitDecision('approved')" class="btn-primary-sm" style="background:#00ff64; color:black;">Approve</button>
                </div>
                <div id="bidFinalActions" style="display:none; gap:0.5rem;">
                    <button type="button" onclick="submitDecision('loss')" class="btn-primary-sm" style="background:#ff4444; color:white; padding: 0.8rem 2rem;">🔴 LOSS</button>
                    <button type="button" onclick="submitDecision('won')" class="btn-primary-sm" style="background:#00ff64; color:black; padding: 0.8rem 2rem;">🟢 WON</button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
.tab-header { display: flex; gap: 1rem; border-bottom: 2px solid rgba(255,255,255,0.05); }
.tab-btn { background: none; border: none; color: var(--text-dim); padding: 1rem; cursor: pointer; font-weight: bold; transition: 0.3s; position: relative; }
.tab-btn.active { color: var(--gold); }
.tab-btn.active::after { content: ''; position: absolute; bottom: -2px; left: 0; width: 100%; height: 2px; background: var(--gold); }

.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 3000; display: flex; justify-content: center; align-items: center; }
.modal-content { width: 100%; max-width: 600px; padding: 2rem; }
.modal-actions { display: flex; justify-content: space-between; align-items: center; }

textarea { width: 100%; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: white; padding: 1rem; height: 120px; }
</style>

<script>
function showTab(tabId) {
    document.querySelectorAll('.tab-pane').forEach(p => p.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + tabId).style.display = 'block';
    event.currentTarget.classList.add('active');
}

function openApprovalModal(module, id, name, details = null) {
    document.getElementById('modalModule').value = module;
    document.getElementById('modalRefId').value = id;
    document.getElementById('modalTitle').innerText = "Processing: " + name;
    document.getElementById('modalSub').innerText = "Module: " + module + " | Ref ID: " + id;
    
    // Reset Sections
    const detailsBox = document.getElementById('detailsBox');
    const standardActions = document.getElementById('standardActions');
    const bidFinalActions = document.getElementById('bidFinalActions');
    const approveBtn = document.getElementById('approveBtn');
    
    const bidDetails = document.getElementById('bidDetails');
    const hrDetails = document.getElementById('hrDetails');
    
    detailsBox.style.display = 'none';
    bidDetails.style.display = 'none';
    hrDetails.style.display = 'none';
    
    standardActions.style.display = 'flex';
    bidFinalActions.style.display = 'none';
    approveBtn.innerText = 'Approve';
    
    if (module === 'BIDS' && details) {
        detailsBox.style.display = 'block';
        bidDetails.style.display = 'block';
        // ... Bid Details Logic ...
        document.getElementById('detTitle').innerText = details.title;
        document.getElementById('detClient').innerText = details.client;
        document.getElementById('detMode').innerText = (details.mode === 'hardcopy' ? 'Physical Hard-Copy' : 'Digital Soft-Copy');
        document.getElementById('detDesc').innerText = details.desc;
        
        const fileLink = document.getElementById('fileLink');
        if (details.file && details.file !== 'null' && details.file !== '') {
            fileLink.style.display = 'block';
            document.getElementById('detFile').href = details.file;
        } else {
            fileLink.style.display = 'none';
        }

        if (details.stage === 'pre') {
            approveBtn.innerText = 'Approve for Finance Finalization';
            approveBtn.setAttribute('onclick', "submitDecision('pre_approved')");
        } else if (details.stage === 'final') {
            standardActions.style.display = 'none';
            bidFinalActions.style.display = 'flex';
        }
    } else if (module === 'HR' && details) {
        detailsBox.style.display = 'block';
        hrDetails.style.display = 'block';
        
        document.getElementById('hrId').innerText = details.employee_code;
        document.getElementById('hrExp').innerText = details.work_experience_years;
        document.getElementById('hrType').innerText = details.employment_type;
        document.getElementById('hrEmerName').innerText = details.emergency_name;
        document.getElementById('hrEmerPhone').innerText = details.emergency_phone;
        document.getElementById('hrEmerRel').innerText = details.emergency_relationship;
        document.getElementById('hrEduLevel').innerText = details.education_level;
        document.getElementById('hrEduField').innerText = details.education_field;
        document.getElementById('hrEduPdf').href = details.education_pdf;
        
        approveBtn.innerText = 'Activate Profile & Enable Login';
        approveBtn.setAttribute('onclick', "submitDecision('approved')");
    } else {
        approveBtn.setAttribute('onclick', "submitDecision('approved')");
    }

    document.getElementById('approvalModal').style.display = 'flex';
}

function closeApprovalModal() {
    document.getElementById('approvalModal').style.display = 'none';
}

function submitDecision(action) {
    const reason = document.getElementById('modalReason').value;
    if ((action === 'rejected' || action === 'queried' || action === 'loss') && !reason.trim()) {
        alert("Reason/Comments are mandatory for this decision.");
        return;
    }

    if (action === 'won' || action === 'loss') {
        const confirmMsg = action === 'won' 
            ? "⚠️ CRITICAL: YOU ARE MARKING THIS BID AS WON.\n\nThis will automatically create a new Project, initialize the main Site, and notify all departments.\n\nTHIS ACTION IS FINAL AND IRREVERSIBLE. PROCEED?"
            : "⚠️ CRITICAL: YOU ARE MARKING THIS BID AS LOSS.\n\nThis will move the bid to history and lock it permanently.\n\nTHIS ACTION IS FINAL AND IRREVERSIBLE. PROCEED?";
        
        if (!confirm(confirmMsg)) return;
    }

    document.getElementById('modalAction').value = action;
    document.querySelector('#approvalModal form').submit();
}
</script>
