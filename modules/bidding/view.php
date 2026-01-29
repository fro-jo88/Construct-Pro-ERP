<?php
// modules/bidding/view.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/BidManager.php';

AuthManager::requireLogin();

$bid_id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$bid_id) {
    echo "<div class='text-red'>Error: Bid ID missing.</div>";
    return;
}

$bid = BidManager::getBid($bid_id);
if (!$bid) {
    echo "<div class='text-red'>Error: Bid not found.</div>";
    return;
}

// Security Check
if (!BidManager::canUserViewBidFile($user_id, $bid_id)) {
    echo "<div style='padding: 50px; text-align: center;'>
            <i class='fas fa-shield-alt fa-3x text-red mb-3'></i>
            <h3 class='text-white'>Access Restricted</h3>
            <p class='text-secondary'>You do not have authorization to view this bid's documentation.</p>
          </div>";
    return;
}

$files = BidManager::getBidFiles($bid_id);
?>

<div class="bid-view-container">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem;">
        <div>
            <h1 style="color: #fff; margin: 0; font-size: 2.25rem;">
                <span style="color: var(--gold);"><?= htmlspecialchars($bid['tender_no']) ?></span>: 
                <?= htmlspecialchars($bid['title']) ?>
            </h1>
            <p class="text-secondary" style="margin-top: 10px; font-size: 1.1rem;">
                <i class="fas fa-building me-2"></i> <?= htmlspecialchars($bid['client_name']) ?>
            </p>
        </div>
        <div style="text-align: right;">
            <span class="badge-premium" style="background: rgba(255, 204, 0, 0.1); color: var(--gold); border: 1px solid var(--gold); font-size: 0.9rem;">
                <?= strtoupper(str_replace('_', ' ', $bid['status'])) ?>
            </span>
            <div style="color: rgba(255,255,255,0.4); font-size: 0.8rem; margin-top: 8px;">
                Deadline: <?= date('M d, Y', strtotime($bid['deadline'])) ?>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
        <!-- DESCRIPTION AREA -->
        <div class="glass-card" style="padding: 30px;">
            <h3 style="color: var(--gold); margin-bottom: 20px;"><i class="fas fa-file-alt"></i> Tender Description</h3>
            <div style="color: rgba(255,255,255,0.8); line-height: 1.8; font-size: 1rem;">
                <?= nl2br(htmlspecialchars($bid['description'])) ?>
            </div>
            
            <div style="margin-top: 40px; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 20px; display: flex; gap: 40px;">
                <div>
                    <div style="font-size: 0.75rem; text-transform: uppercase; color: rgba(255,255,255,0.4);">Created By</div>
                    <div style="color: #fff; font-weight: 600;"><?= htmlspecialchars($bid['creator_name']) ?></div>
                </div>
                <div>
                    <div style="font-size: 0.75rem; text-transform: uppercase; color: rgba(255,255,255,0.4);">System Date</div>
                    <div style="color: #fff; font-weight: 600;"><?= date('M d, Y H:i', strtotime($bid['created_at'])) ?></div>
                </div>
            </div>
        </div>

        <!-- ATTACHMENTS AREA -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="glass-card" style="padding: 24px; border-top: 4px solid var(--gold);">
                <h4 style="color: #fff; margin-bottom: 20px;">📎 Attached Soft-Copy</h4>
                
                <?php if (empty($files)): ?>
                    <div style="text-align: center; padding: 30px; border: 1px dashed rgba(255,255,255,0.1); border-radius: 12px;">
                        <i class="fas fa-folder-open fa-2x text-secondary mb-3"></i>
                        <p class="text-secondary" style="font-size: 0.9rem;">No documents attached to this bid.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($files as $f): 
                        $icon = 'fa-file-alt';
                        if ($f['file_type'] == 'pdf') $icon = 'fa-file-pdf text-red';
                        if (in_array($f['file_type'], ['doc', 'docx'])) $icon = 'fa-file-word text-blue';
                        if (in_array($f['file_type'], ['xls', 'xlsx'])) $icon = 'fa-file-excel text-green';
                    ?>
                        <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); border-radius: 15px; padding: 20px; transition: all 0.3s ease;">
                            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                                <i class="fas <?= $icon ?> fa-2x"></i>
                                <div style="flex: 1; overflow: hidden;">
                                    <div style="color: #fff; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($f['file_name']) ?>">
                                        <?= htmlspecialchars($f['file_name']) ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: rgba(255,255,255,0.4); text-transform: uppercase;">
                                        <?= strtoupper($f['file_type']) ?> Document
                                    </div>
                                </div>
                            </div>
                            
                            <a href="modules/bidding/download.php?file_id=<?= $f['id'] ?>" target="_blank" class="btn-primary-sm" style="width: 100%; text-align: center; display: block; text-decoration: none; background: rgba(255, 204, 0, 0.1); color: var(--gold); border: 1px solid var(--gold);">
                                <i class="fas fa-download me-2"></i> View / Download
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <div style="margin-top: 20px; font-size: 0.8rem; color: rgba(255,255,255,0.3); background: rgba(0,0,0,0.2); padding: 12px; border-radius: 8px;">
                    <i class="fas fa-shield-check text-green me-1"></i> Secure Document Management: Access is logged and restricted to authorized workflow roles.
                </div>
            </div>
            
            <!-- ACTIONS -->
            <div class="glass-card" style="padding: 20px;">
                <h5 style="color: rgba(255,255,255,0.5); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px;">Workflow Redirects</h5>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <a href="main.php?module=technical/submit&id=<?= $bid_id ?>" class="btn-secondary-sm" style="text-decoration: none; text-align: center; background: rgba(0, 150, 255, 0.05); border-color: rgba(0, 150, 255, 0.2);">Technical Evaluation</a>
                    <a href="main.php?module=bidding/finance_bid_dashboard/index&view=preparation&id=<?= $bid_id ?>" class="btn-secondary-sm" style="text-decoration: none; text-align: center; background: rgba(255, 204, 0, 0.05); border-color: rgba(255, 204, 0, 0.2);">Financial Modeling</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .badge-premium { padding: 5px 15px; border-radius: 20px; font-weight: 700; }
    .glass-card { background: rgba(255,255,255,0.03); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08); border-radius: 20px; }
    .text-red { color: #ff4444; }
    .text-green { color: #00ff64; }
    .text-blue { color: #0096ff; }
</style>
