<?php
// modules/bidding/technical_dashboard.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/TenderManager.php';
require_once __DIR__ . '/../../includes/BidManager.php';

AuthManager::requireRole(['TECH_BID_MANAGER', 'TENDER_TECHNICAL', 'GM']);
$role = $_SESSION['role'];

$tender_id = $_GET['id'] ?? null;
// Handle List View like Financial Dashboard...
if (!$tender_id) {
     $tenders = TenderManager::getAllTenders(); // Filter for Technical accessible
} else {
    $bid = BidManager::getTechnicalBid($tender_id);
    $tender = TenderManager::getTenderWithBids($tender_id);
    $planning_reqs = BidManager::getPlanningRequests($tender_id);

    // Handle Actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['request_planning'])) {
            $items = $_POST['schedules'] ?? [];
            if (!empty($items)) {
                $details = "Requested Schedules: " . implode(', ', $items);
                if (!empty($_POST['notes'])) {
                    $details .= " | Notes: " . $_POST['notes'];
                }
                BidManager::createPlanningRequest($tender_id, $_SESSION['user_id'], $details);
                header("Location: main.php?module=bidding/technical_dashboard&id=$tender_id&success=planning_requested");
                exit;
            }
        }
        if (isset($_POST['mark_ready'])) {
             // Validate Planning first
             BidManager::markTechnicalReady($bid['id']);
             header("Location: main.php?module=bidding/technical_dashboard&id=$tender_id&success=marked_ready");
             exit;
        }
    }
}
?>

<div class="main-content">
    <div class="page-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem;">
        <h1 style="color:var(--gold);"><i class="fas fa-microchip"></i> Technical Bid Dashboard</h1>
        <?php if ($tender_id): ?>
            <a href="main.php?module=bidding/technical_dashboard" class="btn-secondary-sm"><i class="fas fa-arrow-left"></i> Back to Queue</a>
        <?php endif; ?>
    </div>

    <?php if (!$tender_id): ?>
         <!-- LIST VIEW -->
        <div class="glass-card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tender #</th>
                        <th>Project / Client</th>
                        <th>Deadline</th>
                        <th style="text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tenders as $at): ?>
                    <tr>
                        <td style="font-family:monospace; color:var(--gold);"><?= $at['tender_no'] ?></td>
                        <td>
                            <div style="font-weight:bold;"><?= htmlspecialchars($at['title']) ?></div>
                            <div style="font-size:0.8rem; color:var(--text-dim);"><?= htmlspecialchars($at['client_name']) ?></div>
                        </td>
                        <td><?= date('M d, Y', strtotime($at['deadline'])) ?></td>
                        <td style="text-align:right;">
                            <a href="main.php?module=bidding/technical_dashboard&id=<?= $at['id'] ?>" class="btn-primary-sm" style="text-decoration:none;">Manage Technical</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <!-- WORKSPACE -->
        <div style="display:grid; grid-template-columns: 2fr 1fr; gap:1.5rem;">
            <div>
                <!-- PLANNING INTEGRATION -->
                <div class="glass-card mb-4">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:0.5rem;">
                        <h4 style="color:var(--gold); margin:0;"><i class="fas fa-project-diagram"></i> Planning & Resource Inputs</h4>
                        <?php if ($bid['status'] === 'draft'): ?>
                            <button class="btn-primary-sm" onclick="document.getElementById('planningModal').style.display='flex'">+ Request Planning Specs</button>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-body">
                        <?php if (empty($planning_reqs)): ?>
                            <div style="text-align:center; padding:2rem; background:rgba(255,204,0,0.05); border:1px dashed var(--gold); border-radius:12px; color:var(--gold);">
                                <i class="fas fa-info-circle mb-2"></i><br>
                                No Planning Schedules Requested yet. You must coordinate with the Planning Manager.
                            </div>
                        <?php else: ?>
                            <table class="data-table">
                                <thead><tr><th>Assignment Details</th><th>Status</th><th>Output</th></tr></thead>
                                <tbody>
                                    <?php foreach ($planning_reqs as $req): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($req['request_details']) ?></td>
                                        <td><span class="status-badge" style="background:rgba(255,255,255,0.1);"><?= $req['status'] ?></span></td>
                                        <td>
                                            <?php if ($req['output_type']): ?>
                                                <span style="color:#00ff64;"><i class="fas fa-check-circle"></i> Received</span>
                                            <?php else: ?>
                                                <span style="color:var(--text-dim);">Awaiting Response</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- COMPLIANCE CHECKLIST -->
                <div class="glass-card">
                    <h4 style="color:var(--gold); margin-bottom:1.5rem; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:0.5rem;"><i class="fas fa-tasks"></i> Technical Compliance</h4>
                    <div class="card-body">
                        <div style="background:rgba(255,255,255,0.03); padding:1rem; border-radius:12px; margin-bottom:1.5rem;">
                            <label style="color:var(--text-dim); font-size:0.8rem;">Current Technical Readiness Score</label>
                            <div style="font-size:2rem; font-weight:bold; color:<?= ($bid['compliance_score'] ?? 0) > 70 ? '#00ff64' : 'var(--gold)' ?>;"><?= $bid['compliance_score'] ?? 0 ?>%</div>
                        </div>
                        
                        <div style="display:grid; gap:1rem;">
                            <div style="display:flex; align-items:center; gap:0.75rem; background:rgba(255,255,255,0.02); padding:0.8rem; border-radius:8px;">
                                <i class="fas fa-check-circle" style="color:#00ff64;"></i>
                                <span>Design & Shop Drawings Analysis</span>
                            </div>
                            <div style="display:flex; align-items:center; gap:0.75rem; background:rgba(255,255,255,0.02); padding:0.8rem; border-radius:8px;">
                                <i class="fas fa-check-circle" style="color:#00ff64;"></i>
                                <span>Technical Specifications Compliance</span>
                            </div>
                            <div style="display:flex; align-items:center; gap:0.75rem; background:rgba(255,255,255,0.02); padding:0.8rem; border-radius:8px;">
                                <i class="fas fa-spinner fa-spin" style="color:var(--gold);"></i>
                                <span style="color:var(--gold);">Methodology Statement Preparation</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div style="display:flex; flex-direction:column; gap:1.5rem;">
                <div class="glass-card" style="border-left: 5px solid var(--gold);">
                    <h4 style="margin-bottom:1rem;">Action Control</h4>
                    <div class="card-body">
                        <div class="mb-3">
                            <label style="font-size:0.8rem; color:var(--text-dim);">Workspace Status</label>
                            <span class="status-badge" style="display:block; margin-top:0.25rem; font-size:1rem; background:rgba(255,204,0,0.1); color:var(--gold); border:1px solid var(--gold);"><?= strtoupper($bid['status'] ?? 'DRAFT') ?></span>
                        </div>
                        
                        <?php if (($bid['status'] ?? 'draft') === 'draft' || ($bid['status'] ?? 'draft') === 'DRAFT'): ?>
                            <form method="POST">
                                <input type="hidden" name="mark_ready" value="1">
                                <button type="submit" class="btn-primary-sm" style="width:100%; padding:1rem; font-size:1rem; box-shadow: 0 10px 20px -5px rgba(0,255,100,0.2); background:#00ff64; color:black;" <?= empty($planning_reqs) ? 'disabled title="Request Planning Info First"' : '' ?>>
                                    Finalize Technical Ready <i class="fas fa-check-double ml-2"></i>
                                </button>
                            </form>
                            <?php if(empty($planning_reqs)): ?>
                                <small style="color:#ff4444; display:block; margin-top:0.75rem; font-size:0.75rem;"><i class="fas fa-exclamation-triangle"></i> Blocked: Coordinaton with Planning required before submission.</small>
                            <?php endif; ?>
                        <?php else: ?>
                            <div style="background:rgba(0,255,100,0.1); border:1px solid #00ff64; padding:1.5rem; border-radius:12px; text-align:center;">
                                <i class="fas fa-check-circle fa-2x mb-2" style="color:#00ff64;"></i>
                                <div style="font-weight:bold; color:#00ff64;">Technical Finalized</div>
                                <p style="font-size:0.8rem; margin:0.5rem 0 0 0;">Ready for Commercial/GM Review</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="glass-card mb-4" style="background: linear-gradient(to bottom right, rgba(255,204,0,0.05), transparent);">
                    <h4 style="color:var(--gold); margin-bottom:1.25rem;">Technical Resources</h4>
                    <div style="display:grid; gap:1rem;">
                        <a href="main.php?module=bidding/specs&id=<?= $tender_id ?>" class="btn-secondary-sm" style="text-decoration:none; display:flex; align-items:center; gap:0.75rem;">
                            <i class="fas fa-file-invoice" style="color:var(--gold);"></i>
                            <span>Technical Specs & BoQ</span>
                        </a>
                        <a href="main.php?module=bidding/collaborate&id=<?= $tender_id ?>" class="btn-secondary-sm" style="text-decoration:none; display:flex; align-items:center; gap:0.75rem;">
                            <i class="fas fa-comments" style="color:#00ff64;"></i>
                            <span>Bid Team War-Room</span>
                        </a>
                    </div>
                </div>

                <div class="glass-card">
                    <h4 style="color:var(--gold); margin-bottom:1rem;">Tender Overview</h4>
                    <div style="font-size:0.9rem;">
                        <div class="mb-2"><span style="color:var(--text-dim);">Client:</span> <?= htmlspecialchars($tender['client_name']) ?></div>
                        <div class="mb-2"><span style="color:var(--text-dim);">Est. Value:</span> $<?= number_format($tender['estimated_value'] ?? 0, 2) ?></div>
                        <div class="mb-2"><span style="color:var(--text-dim);">Type:</span> <?= ucfirst($tender['submission_mode'] ?? 'Softcopy') ?></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Planning Request Modal -->
<div id="planningModal" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); backdrop-filter: blur(8px); z-index:9000; justify-content:center; align-items:center;">
    <div class="glass-card" style="width:500px; padding:2.5rem; border:1px solid rgba(255,204,0,0.2); border-radius:24px;">
        <h3 style="color:var(--gold); margin-top:0;"><i class="fas fa-paper-plane"></i> Coordination Request</h3>
        <p class="text-dim" style="font-size:0.9rem;">Send a work assignment to the <strong style="color:white;">Planning Manager</strong> to allocate an Engineer for this bid.</p>
        
        <form method="POST">
            <input type="hidden" name="request_planning" value="1">
            
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem; margin:1.5rem 0;">
                <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                    <input type="checkbox" name="schedules[]" value="MS Schedule" checked> MS Schedule
                </label>
                <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                    <input type="checkbox" name="schedules[]" value="Manpower Schedule"> Manpower
                </label>
                <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                    <input type="checkbox" name="schedules[]" value="Equipment Schedule"> Equipment
                </label>
                <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                    <input type="checkbox" name="schedules[]" value="Material Schedule"> Materials
                </label>
            </div>

            <div class="form-group mb-4">
                <label style="font-size:0.8rem; color:var(--text-dim); display:block; margin-bottom:0.5rem;">Specific Notes / Requirements</label>
                <textarea name="notes" style="width:100%; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:12px; color:white; padding:1rem; height:80px;" placeholder="e.g. Needs high priority structural phase breakdown..."></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:1.5rem;">
                <button type="button" class="btn-secondary-sm" onclick="document.getElementById('planningModal').style.display='none'">Dismiss</button>
                <button type="submit" class="btn-primary-sm" style="background:var(--gold); color:black;">Send to Planning</button>
            </div>
        </form>
    </div>
</div>

<style>
.modal-overlay { display: none; }
input[type="checkbox"] { accent-color: var(--gold); width: 18px; height: 18px; }
</style>

