<?php
// modules/hr/tenders.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/HRManager.php';
require_once __DIR__ . '/../../includes/BidManager.php';

AuthManager::requireRole('HR_MANAGER');

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create_tender') {
            $data = $_POST;
            $data['bid_file'] = null;

            if (isset($_POST['submission_mode']) && $_POST['submission_mode'] === 'softcopy' && isset($_FILES['bid_file'])) {
                $file = $_FILES['bid_file'];
                if ($file['error'] === 0) {
                    $upload_dir = __DIR__ . '/../../uploads/tenders/';
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                    
                    $filename = time() . '_' . basename($file['name']);
                    $target_file = $upload_dir . $filename;
                    
                    if (move_uploaded_file($file['tmp_name'], $target_file)) {
                        $data['bid_file'] = 'uploads/tenders/' . $filename;
                    }
                }
            }
            
            HRManager::createTender($data, $_SESSION['user_id']);
            $mode_label = ($data['submission_mode'] === 'hardcopy') ? 'Hard-Copy (Physical)' : 'Soft-Copy (Digital)';
            $msg = "Tender origin created successfully as <strong>$mode_label</strong>.";
        } elseif ($_POST['action'] === 'submit_to_gm') {
            BidManager::completeTechnical($_POST['tender_id'], $_SESSION['user_id']);
            $msg = "Bid technical phase completed and pushed to Finance/GM.";
        }
    }
}

$tenders = HRManager::getTenders();
?>

<div class="tenders-module">
    <div class="section-header mb-4" style="display:flex; justify-content:space-between; align-items:center;">
        <h2><i class="fas fa-gavel"></i> Tender & Bid Origin</h2>
        <button class="btn-primary-sm" onclick="document.getElementById('newTenderModal').style.display='flex'">+ Create New Bid</button>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-success glass-card mb-4" style="color: #00ff64; border-left: 5px solid #00ff64;">
            <i class="fas fa-check-circle"></i> <?= $msg ?>
        </div>
    <?php endif; ?>

    <!-- Tenders Table -->
    <div class="glass-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Tender #</th>
                    <th>Client / Project</th>
                    <th>Status</th>
                    <th>Deadline</th>
                    <th>Created By</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tenders)): ?>
                    <tr><td colspan="6" style="text-align:center; padding:3rem; color:var(--text-dim);">No tenders originated yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($tenders as $t): ?>
                        <tr>
                            <td style="font-family: monospace; color: var(--gold);"><?= $t['tender_no'] ?></td>
                            <td>
                                <div style="font-weight:bold;"><?= htmlspecialchars($t['title']) ?></div>
                                <div style="font-size:0.8rem; color:var(--text-dim);">
                                    <?= htmlspecialchars($t['client_name']) ?> | 
                                    <span style="color:<?= ($t['submission_mode'] ?? 'softcopy') === 'hardcopy' ? '#ccc' : 'var(--gold)' ?>;">
                                        <i class="fas <?= ($t['submission_mode'] ?? 'softcopy') === 'hardcopy' ? 'fa-file-invoice' : 'fa-file-pdf' ?>"></i> 
                                        <?= ucfirst($t['submission_mode'] ?? 'softcopy') ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge <?= $t['status'] ?>"><?= strtoupper($t['status']) ?></span>
                            </td>
                            <td><?= date('M d, Y', strtotime($t['deadline'])) ?></td>
                            <td><?= $t['creator_name'] ?></td>
                            <td style="text-align:right;">
                                <div style="display:flex; justify-content:flex-end; gap:0.5rem;">
                                    <?php if ($t['status'] === 'DRAFT'): ?>
                                        <form method="POST" style="margin:0;">
                                            <input type="hidden" name="action" value="submit_to_gm">
                                            <input type="hidden" name="tender_id" value="<?= $t['id'] ?>">
                                            <button type="submit" class="btn-secondary-sm" style="color:var(--gold); border-color:var(--gold);">Push to Technical</button>
                                        </form>
                                    <?php elseif ($t['status'] === 'WON'): ?>
                                        <a href="main.php?module=hr/sites&tender_id=<?= $t['id'] ?>" class="btn-primary-sm" style="text-decoration:none; font-size:0.75rem; background:#00ff64; color:black;">Initialize Site</a>
                                    <?php endif; ?>
                                    <button class="btn-secondary-sm"><i class="fas fa-eye"></i></button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Premium Modal for New Tender -->
<div id="newTenderModal" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); backdrop-filter: blur(8px); z-index:2000; justify-content:center; align-items:flex-start; overflow-y:auto; padding:2rem 0;">
    <div class="glass-card" style="width:650px; padding:0; border:1px solid rgba(255,255,255,0.1); border-radius:24px; overflow:hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);">
        <!-- Modal Header -->
        <div style="background: linear-gradient(to right, rgba(255,204,0,0.1), transparent); padding:2rem 2.5rem; border-bottom: 1px solid rgba(255,255,255,0.05);">
            <h3 style="margin:0; font-size:1.75rem; letter-spacing:-0.5px;">Initialize <span style="color:var(--gold);">Tender Origin</span></h3>
            <p class="text-dim" style="margin:0.5rem 0 0 0; font-size:0.9rem;">Start a new bidding journey. Digital and physical modes supported.</p>
        </div>

        <form method="POST" enctype="multipart/form-data" style="padding:2.5rem;">
            <input type="hidden" name="action" value="create_tender">
            
            <div class="form-group mb-4">
                <label style="display:block; font-size:0.8rem; text-transform:uppercase; letter-spacing:1px; color:var(--text-dim); margin-bottom:0.75rem;">Project / Contract Title</label>
                <input type="text" name="title" required placeholder="e.g. Urban High-Rise Structural Phase B" style="width:100%; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.1); border-radius:12px; color:white; padding:1rem; font-size:1rem; transition: all 0.3s;" onfocus="this.style.borderColor='var(--gold)'; this.style.background='rgba(255,204,0,0.02)'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'; this.style.background='rgba(255,255,255,0.03)'">
            </div>
            
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.5rem;" class="mb-4">
                <div class="form-group">
                    <label style="display:block; font-size:0.8rem; text-transform:uppercase; letter-spacing:1px; color:var(--text-dim); margin-bottom:0.75rem;">Client Authority</label>
                    <input type="text" name="client_name" required placeholder="e.g. National Housing Dev." style="width:100%; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.1); border-radius:12px; color:white; padding:1rem;">
                </div>
                <div class="form-group">
                    <label style="display:block; font-size:0.8rem; text-transform:uppercase; letter-spacing:1px; color:var(--text-dim); margin-bottom:0.75rem;">Submission Clock</label>
                    <input type="datetime-local" name="deadline" required style="width:100%; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.1); border-radius:12px; color:white; padding:1rem;">
                </div>
            </div>

            <div class="form-group mb-4">
                <label style="display:block; font-size:0.8rem; text-transform:uppercase; letter-spacing:1px; color:var(--text-dim); margin-bottom:1rem;">Document Acquisition Mode</label>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                    <!-- Custom Radio Card 1 -->
                    <label class="mode-card" style="cursor:pointer;">
                        <input type="radio" name="submission_mode" value="softcopy" checked onchange="toggleFileUpload(true)" style="display:none;">
                        <div class="card-content">
                            <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i>
                            <div style="font-weight:bold;">Digital Soft-Copy</div>
                            <div style="font-size:0.7rem; opacity:0.6;">Cloud PDF / E-Bidding</div>
                        </div>
                    </label>
                    <!-- Custom Radio Card 2 -->
                    <label class="mode-card" style="cursor:pointer;">
                        <input type="radio" name="submission_mode" value="hardcopy" onchange="toggleFileUpload(false)" style="display:none;">
                        <div class="card-content">
                            <i class="fas fa-archive fa-2x mb-2"></i>
                            <div style="font-weight:bold;">Physical Hard-Copy</div>
                            <div style="font-size:0.7rem; opacity:0.6;">Sealed Envelopes / Site Map</div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Enhanced File Uploader -->
            <div id="fileUploadGroup" class="form-group mb-4">
                <label style="display:block; font-size:0.8rem; text-transform:uppercase; letter-spacing:1px; color:var(--text-dim); margin-bottom:0.75rem;">Registration Document</label>
                <div class="file-drop-zone">
                    <input type="file" name="bid_file" id="bid_file_input" accept=".pdf,.doc,.docx,.zip" style="display:none;" onchange="updateFileName(this)">
                    <div onclick="document.getElementById('bid_file_input').click()" style="cursor:pointer; text-align:center; padding:2rem; border:2px dashed rgba(255,204,0,0.2); border-radius:16px; background:rgba(255,204,0,0.01); transition:all 0.3s;" onmouseover="this.style.borderColor='var(--gold)'; this.style.background='rgba(255,204,0,0.05)'" onmouseout="this.style.borderColor='rgba(255,204,0,0.2)'; this.style.background='rgba(255,204,0,0.01)'">
                        <i class="fas fa-file-contract fa-3x mb-3" style="color:var(--gold);"></i>
                        <div id="fileNameDisplay" style="font-size:1rem; font-weight:bold;">Click to Upload Technical Specs</div>
                        <div class="text-dim" style="font-size:0.75rem; margin-top:0.5rem;">Maximum size: 20MB (PDF, DOCX, ZIP)</div>
                    </div>
                </div>
            </div>
            
            <div id="hardCopyNote" class="form-group mb-4" style="display:none;">
                <div style="background:rgba(255,204,0,0.05); border:1px solid rgba(255,204,0,0.2); padding:1.5rem; border-radius:16px; display:flex; gap:1rem; align-items:center;">
                    <div style="width:50px; height:50px; background:var(--gold); color:black; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i class="fas fa-exclamation-triangle fa-lg"></i>
                    </div>
                    <div>
                        <div style="font-weight:bold; color:var(--gold);">Physical Filing Required</div>
                        <div style="font-size:0.8rem; color:var(--text-dim);">Verify that the RFP book is logged into the Bidding Cabinet (Site-M/2026).</div>
                    </div>
                </div>
            </div>

            <div class="form-group mb-4">
                <label style="display:block; font-size:0.8rem; text-transform:uppercase; letter-spacing:1px; color:var(--text-dim); margin-bottom:0.75rem;">Strategic Scope Overview</label>
                <textarea name="description" rows="3" style="width:100%; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.1); border-radius:12px; color:white; padding:1rem; font-family: inherit; resize:none;"></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:1.25rem; margin-top:1rem;">
                <button type="button" onclick="document.getElementById('newTenderModal').style.display='none'" style="background:transparent; border:none; color:var(--text-dim); cursor:pointer; font-weight:bold; transition:color 0.3s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='var(--text-dim)'">Dismiss</button>
                <button type="submit" class="btn-primary-sm" style="padding: 1.1rem 3rem; font-size:1rem; border-radius:12px; box-shadow: 0 10px 20px -5px rgba(255,204,0,0.3);">Register <i class="fas fa-arrow-right ml-2"></i></button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleFileUpload(show) {
    const fileGroup = document.getElementById('fileUploadGroup');
    const noteGroup = document.getElementById('hardCopyNote');
    
    if (show) {
        fileGroup.style.display = 'block';
        noteGroup.style.display = 'none';
    } else {
        fileGroup.style.display = 'none';
        noteGroup.style.display = 'block';
    }
}

function updateFileName(input) {
    const display = document.getElementById('fileNameDisplay');
    if (input.files && input.files[0]) {
        display.innerHTML = '<span style="color:#00ff64;"><i class="fas fa-check-circle"></i> ' + input.files[0].name + '</span>';
    } else {
        display.innerText = "Click to Upload Technical Specs";
    }
}
</script>

<style>
.mode-card .card-content {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 16px;
    padding: 1.5rem;
    text-align: center;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    color: var(--text-dim);
}

.mode-card input:checked + .card-content {
    background: rgba(255,204,0,0.1);
    border-color: var(--gold);
    color: var(--gold);
    box-shadow: 0 10px 20px rgba(0,0,0,0.3);
    transform: translateY(-5px);
}

.mode-card:hover .card-content {
    border-color: rgba(255,204,0,0.4);
}
</style>

<style>
.modal-overlay { display: none; }
.status-badge.WON { background: rgba(0, 255, 100, 0.2); color: #00ff64; }
.status-badge.LOSS { background: rgba(255, 68, 68, 0.2); color: #ff4444; }
.status-badge.DRAFT { background: rgba(255, 255, 255, 0.1); color: #ccc; }
.status-badge.TECHNICAL_COMPLETED { background: rgba(255, 204, 0, 0.1); color: var(--gold); }
.status-badge.FINANCIAL_COMPLETED { background: rgba(0, 150, 255, 0.1); color: #0096ff; }
.status-badge.GM_PRE_APPROVED { background: rgba(255, 204, 0, 0.2); color: var(--gold); border: 1px solid var(--gold); }
.status-badge.FINANCE_FINAL_REVIEW { background: rgba(0, 255, 100, 0.1); color: #00ff64; border: 1px dotted #00ff64; }
</style>
