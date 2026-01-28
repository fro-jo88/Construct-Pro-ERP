<?php
// modules/bidding/collaborate.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/BidManager.php';
require_once __DIR__ . '/../../includes/TenderManager.php';

AuthManager::requireRole(['TECH_BID_MANAGER', 'TENDER_TECHNICAL', 'TENDER_FINANCE', 'PLANNING_MANAGER', 'GM']);

$bid_id = $_GET['id'] ?? null;
if (!$bid_id) {
    echo "<div class='glass-card alert-danger'>Bid context required for collaboration.</div>";
    return;
}

$tender = TenderManager::getTenderWithBids($bid_id);
if (!$tender) {
    echo "<div class='glass-card alert-danger'>Bid context not found.</div>";
    return;
}

// Handle Message Posting (Simulated)
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'post_note') {
    $msg = "Collaboration note recorded.";
}

?>

<div class="bidding-collaboration">
    <div class="section-header mb-4" style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h2 style="color:var(--gold);"><i class="fas fa-users"></i> Bid War-Room: Collaboration</h2>
            <p class="text-dim">Bridging Technical, Finance, and Planning for: <?= htmlspecialchars($tender['title']) ?></p>
        </div>
        <a href="main.php?module=bidding/technical_dashboard&id=<?= $bid_id ?>" class="btn-secondary-sm">Back to Workspace</a>
    </div>

    <div style="display:grid; grid-template-columns: 1fr 2fr; gap:1.5rem;">
        <!-- Left: Team Status -->
        <div style="display:flex; flex-direction:column; gap:1.5rem;">
            <div class="glass-card">
                <h4 style="color:var(--gold); margin-bottom:1.5rem;"><i class="fas fa-user-shield"></i> Stakeholder Pulse</h4>
                <div style="display:grid; gap:1.25rem;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:0.9rem;">Technical Team</span>
                        <span class="status-badge" style="background:#00ff64; color:black;">READY</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:0.9rem;">Finance Dept.</span>
                        <span class="status-badge" style="background:var(--gold); color:black;">REVIEWING</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:0.9rem;">Planning Manager</span>
                        <span class="status-badge" style="background:rgba(255,255,255,0.1);">AWAITING MS</span>
                    </div>
                </div>
            </div>

            <div class="glass-card">
                <h4 style="color:var(--gold); margin-bottom:1rem;">Internal Timeline</h4>
                <div style="font-size:0.85rem; border-left: 2px solid var(--gold); padding-left:1.5rem; margin-left:0.5rem; position:relative;">
                    <div style="margin-bottom:1.5rem; position:relative;">
                        <div style="position:absolute; left:-23px; top:0; width:14px; height:14px; background:var(--gold); border-radius:50%;"></div>
                        <div style="font-weight:bold;">Tender Originated</div>
                        <span class="text-dim">3 days ago</span>
                    </div>
                    <div style="margin-bottom:1.5rem; position:relative;">
                        <div style="position:absolute; left:-23px; top:0; width:14px; height:14px; background:var(--gold); border-radius:50%;"></div>
                        <div style="font-weight:bold;">Planning Assigned</div>
                        <span class="text-dim">2 days ago</span>
                    </div>
                    <div style="position:relative;">
                        <div style="position:absolute; left:-23px; top:0; width:14px; height:14px; background:rgba(255,255,255,0.2); border-radius:50%;"></div>
                        <div style="color:var(--text-dim);">Final Submission Gate</div>
                        <span class="text-dim">Jan 30, 2026</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Activity / Discussion -->
        <div class="glass-card" style="display:flex; flex-direction:column; height: 600px;">
            <h4 style="color:var(--gold); margin-bottom:1.5rem; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:0.5rem;">Team Communication</h4>
            
            <div style="flex:1; overflow-y:auto; padding-right:1rem;" id="chatBox">
                <!-- Message 1 -->
                <div style="margin-bottom:1.5rem; display:flex; gap:1rem;">
                    <div style="width:40px; height:40px; border-radius:50%; background:var(--gold); color:black; display:flex; align-items:center; justify-content:center; font-weight:bold; flex-shrink:0;">TM</div>
                    <div style="background:rgba(255,255,255,0.05); padding:1rem; border-radius:0 12px 12px 12px; flex:1;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:0.25rem;">
                            <strong style="color:var(--gold);">Technical Manager</strong>
                            <span style="font-size:0.75rem; color:var(--text-dim);">14:20 PM</span>
                        </div>
                        <p style="margin:0; font-size:0.9rem;">We noticed a discrepancy in the soil report. Finance, please hold on the excavation costing until we double-check the depth requirement.</p>
                    </div>
                </div>

                <!-- Message 2 -->
                <div style="margin-bottom:1.5rem; display:flex; gap:1rem;">
                    <div style="width:40px; height:40px; border-radius:50%; background:rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center; font-weight:bold; flex-shrink:0;">FE</div>
                    <div style="background:rgba(255,255,255,0.05); padding:1rem; border-radius:0 12px 12px 12px; flex:1;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:0.25rem;">
                            <strong>Financial Estimator</strong>
                            <span style="font-size:0.75rem; color:var(--text-dim);">15:45 PM</span>
                        </div>
                        <p style="margin:0; font-size:0.9rem;">Acknowledged. We are pausing Work Package #4 (Groundworks) pending Technical confirmation.</p>
                    </div>
                </div>
            </div>

            <div style="margin-top:2rem; border-top:1px solid rgba(255,255,255,0.1); padding-top:1.5rem;">
                <form method="POST">
                    <input type="hidden" name="action" value="post_note">
                    <div style="display:flex; gap:1rem;">
                        <textarea name="note" style="flex:1; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); border-radius:12px; color:white; padding:1rem; min-height:60px;" placeholder="Add a coordination note or query..."></textarea>
                        <button type="submit" class="btn-primary-sm" style="display:flex; align-items:center; justify-content:center; width:60px; height:60px; border-radius:12px;">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
#chatBox::-webkit-scrollbar { width: 4px; }
#chatBox::-webkit-scrollbar-track { background: transparent; }
#chatBox::-webkit-scrollbar-thumb { background: rgba(255,204,0,0.2); border-radius: 10px; }
</style>
