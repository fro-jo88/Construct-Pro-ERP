<?php
// modules/technical/planning.php
// Planning Integration Module for TECH_BID_MANAGER
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/BidManager.php';

AuthManager::requireRole(['TECH_BID_MANAGER', 'PLANNING_MANAGER', 'PLANNING_ENGINEER', 'GM', 'SUPER_ADMIN']);
$db = Database::getInstance();

// Get bids needing planning support
$bids = $db->query("
    SELECT b.*, tb.status as tech_status, tb.id as tech_bid_id
    FROM bids b 
    LEFT JOIN technical_bids tb ON tb.bid_id = b.id
    WHERE b.status IN ('DRAFT', 'TECHNICAL_COMPLETED')
    ORDER BY b.deadline ASC
")->fetchAll();

// Get planning requests
$requests = $db->query("
    SELECT pr.*, b.tender_no, b.title, u.username as requester_name
    FROM planning_requests pr
    JOIN bids b ON pr.bid_id = b.id
    LEFT JOIN users u ON pr.requested_by = u.id
    ORDER BY pr.created_at DESC
    LIMIT 20
")->fetchAll();

// Handle form submissions
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'request_planning') {
            $bid_id = $_POST['bid_id'];
            $details = $_POST['request_details'];
            
            BidManager::createPlanningRequest($bid_id, $_SESSION['user_id'], $details);
            $msg = "Planning support request submitted successfully.";
        }
    }
}
?>

<div class="planning-integration-module">
    <div class="section-header mb-4">
        <h2><i class="fas fa-project-diagram text-gold"></i> Planning Integration</h2>
        <p class="text-dim">Request schedules, manpower plans, and material lists from Planning team</p>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-success glass-card mb-4" style="color: #00ff64; border-left: 5px solid #00ff64;">
            <i class="fas fa-check-circle"></i> <?= $msg ?>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
        <!-- Request New Planning Support -->
        <div class="glass-card">
            <h3 class="mb-3"><i class="fas fa-plus-circle text-gold"></i> Request Planning Support</h3>
            
            <form method="POST">
                <input type="hidden" name="action" value="request_planning">
                
                <div class="form-group mb-3">
                    <label class="text-dim" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Select Bid</label>
                    <select name="bid_id" required style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 0.75rem; color: white;">
                        <option value="">-- Select Bid --</option>
                        <?php foreach ($bids as $bid): ?>
                            <option value="<?= $bid['id'] ?>"><?= htmlspecialchars($bid['tender_no']) ?> - <?= htmlspecialchars($bid['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label class="text-dim" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Request Details</label>
                    <textarea name="request_details" rows="4" required placeholder="Describe what planning outputs you need (MS Schedule, Manpower Plan, Material List, etc.)" 
                              style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 0.75rem; color: white; resize: none;"></textarea>
                </div>

                <button type="submit" class="btn-primary-sm" style="width: 100%;">
                    <i class="fas fa-paper-plane"></i> Submit Request
                </button>
            </form>
        </div>

        <!-- Quick Output Types -->
        <div class="glass-card">
            <h3 class="mb-3"><i class="fas fa-list-alt text-gold"></i> Output Types Available</h3>
            
            <div style="display: grid; gap: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 8px;">
                    <div style="width: 40px; height: 40px; background: var(--gold); color: black; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <strong>MS Schedule</strong>
                        <div class="text-dim" style="font-size: 0.8rem;">Project timeline with milestones</div>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 8px;">
                    <div style="width: 40px; height: 40px; background: #00ff64; color: black; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <strong>Manpower Plan</strong>
                        <div class="text-dim" style="font-size: 0.8rem;">Labor allocation and skills matrix</div>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 8px;">
                    <div style="width: 40px; height: 40px; background: #0096ff; color: white; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div>
                        <strong>Material List (BOQ)</strong>
                        <div class="text-dim" style="font-size: 0.8rem;">Bill of Quantities with pricing</div>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 8px;">
                    <div style="width: 40px; height: 40px; background: #ff6b35; color: white; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div>
                        <strong>Machinery List</strong>
                        <div class="text-dim" style="font-size: 0.8rem;">Equipment requirements and costs</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Requests -->
    <div class="glass-card mt-4">
        <h3 class="mb-3"><i class="fas fa-history text-gold"></i> Recent Planning Requests</h3>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>Bid</th>
                    <th>Request Details</th>
                    <th>Requested By</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($requests)): ?>
                    <tr><td colspan="5" style="text-align: center; padding: 2rem;" class="text-dim">No planning requests yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($requests as $req): ?>
                        <tr>
                            <td>
                                <span style="color: var(--gold); font-family: monospace;"><?= htmlspecialchars($req['tender_no']) ?></span>
                                <div class="text-dim" style="font-size: 0.8rem;"><?= htmlspecialchars(substr($req['title'], 0, 30)) ?>...</div>
                            </td>
                            <td><?= htmlspecialchars(substr($req['request_details'], 0, 50)) ?>...</td>
                            <td><?= htmlspecialchars($req['requester_name'] ?? 'Unknown') ?></td>
                            <td>
                                <span class="status-badge" style="background: rgba(<?= $req['status'] === 'PENDING' ? '255,204,0' : ($req['status'] === 'completed' ? '0,255,100' : '100,150,255') ?>, 0.2); color: <?= $req['status'] === 'PENDING' ? 'var(--gold)' : ($req['status'] === 'completed' ? '#00ff64' : '#6496ff') ?>;">
                                    <?= strtoupper($req['status']) ?>
                                </span>
                            </td>
                            <td class="text-dim"><?= date('M d, Y', strtotime($req['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
