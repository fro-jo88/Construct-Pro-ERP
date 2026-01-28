<?php
// modules/bidding/specs.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/BidManager.php';
require_once __DIR__ . '/../../includes/TenderManager.php';

AuthManager::requireRole(['TECH_BID_MANAGER', 'TENDER_TECHNICAL', 'GM']);

$bid_id = $_GET['id'] ?? null;
if (!$bid_id) {
    echo "<div class='glass-card alert-danger'>Bid/Tender ID required to view specifications.</div>";
    return;
}

$tender = TenderManager::getTenderWithBids($bid_id);
if (!$tender) {
    echo "<div class='glass-card alert-danger'>Tender not found.</div>";
    return;
}

$db = Database::getInstance();

// Handle Spec Upload/Update (Placeholder for now)
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_spec') {
    $msg = "Specification item added successfully (Demo Mode).";
}

?>

<div class="bidding-specs-module">
    <div class="section-header mb-4" style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h2 style="color:var(--gold);"><i class="fas fa-file-contract"></i> Technical Specs & BoQ</h2>
            <p class="text-dim">Project: <?= htmlspecialchars($tender['title']) ?> (<?= htmlspecialchars($tender['tender_no']) ?>)</p>
        </div>
        <a href="main.php?module=bidding/technical_dashboard&id=<?= $bid_id ?>" class="btn-secondary-sm">Back to Workspace</a>
    </div>

    <?php if ($msg): ?>
        <div class="alert glass-card mb-4" style="color:var(--gold);"><?= $msg ?></div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns: 2fr 1fr; gap:1.5rem;">
        <div class="glass-card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:0.5rem;">
                <h4 style="margin:0;">Requirement Checklist</h4>
                <button class="btn-primary-sm" onclick="alert('Demo: Add requirement logic')">+ Add Item</button>
            </div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Specification / Requirement</th>
                        <th>Standard</th>
                        <th>Status</th>
                        <th>Compliance</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Structural Integrity Grade C30/37</td>
                        <td>AASHTO / BS</td>
                        <td><span class="status-badge" style="background:#00ff64; color:black;">Verified</span></td>
                        <td style="color:#00ff64;">100%</td>
                    </tr>
                    <tr>
                        <td>MEP Integration - HVAC Load</td>
                        <td>ASHRAE</td>
                        <td><span class="status-badge" style="background:var(--gold); color:black;">Pending Review</span></td>
                        <td style="color:var(--gold);">85%</td>
                    </tr>
                    <tr>
                        <td>Safety & Fire Retardant Specs</td>
                        <td>NFPA 101</td>
                        <td><span class="status-badge" style="background:rgba(255,255,255,0.1);">Awaiting Doc</span></td>
                        <td style="color:var(--text-dim);">0%</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div style="display:flex; flex-direction:column; gap:1.5rem;">
            <div class="glass-card">
                <h4 style="color:var(--gold); margin-bottom:1rem;">Document Library</h4>
                <div style="display:grid; gap:1rem;">
                    <button class="btn-secondary-sm" style="width:100%; text-align:left; display:flex; justify-content:space-between; align-items:center;">
                        <span><i class="fas fa-file-pdf mr-2" style="color:#ff4444;"></i> Main BoQ.pdf</span>
                        <i class="fas fa-download"></i>
                    </button>
                    <button class="btn-secondary-sm" style="width:100%; text-align:left; display:flex; justify-content:space-between; align-items:center;">
                        <span><i class="fas fa-file-image mr-2" style="color:var(--gold);"></i> Site Plan.dwg</span>
                        <i class="fas fa-download"></i>
                    </button>
                </div>
                <div class="mt-4" style="border-top:1px solid rgba(255,255,255,0.1); padding-top:1rem;">
                    <label style="font-size:0.75rem; color:var(--text-dim);">Upload New Spec Doc</label>
                    <input type="file" style="display:none;" id="specUpload">
                    <button onclick="document.getElementById('specUpload').click()" class="btn-primary-sm" style="width:100%; margin-top:0.5rem; background:rgba(255,204,0,0.1); border:1px dashed var(--gold); color:var(--gold);">
                        <i class="fas fa-cloud-upload-alt mr-2"></i> Attach Technical File
                    </button>
                </div>
            </div>

            <div class="glass-card" style="background:rgba(0,255,100,0.05); border:1px solid rgba(0,255,100,0.2);">
                <h4>Summary Status</h4>
                <div style="font-size:2.5rem; font-weight:bold; color:#00ff64;">92%</div>
                <p class="text-dim" style="font-size:0.8rem; margin:0;">Overall Technical Compliance for this bid.</p>
            </div>
        </div>
    </div>
</div>
