<?php
// modules/planning/engineer_dashboard/bid_support.php

$db = Database::getInstance();
$user_id = $_SESSION['user_id'];

// Fetch Bid Planning Requests
$requests = $db->query("SELECT pr.*, t.title, t.tender_no, u.username as requester 
                        FROM planning_requests pr 
                        JOIN tenders t ON pr.tender_id = t.id 
                        JOIN users u ON pr.requested_by = u.id 
                        WHERE pr.status IN ('requested', 'in_progress')")->fetchAll();

?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">Technical Bid Support</h3>
    <span class="badge bg-danger rounded-pill px-3"><?= count($requests) ?> Pending Requests</span>
</div>

<div class="row g-4">
    <?php foreach ($requests as $r): ?>
        <div class="col-md-6">
            <div class="glass-panel p-4 h-100 border-top border-warning border-4">
                <div class="d-flex justify-content-between mb-3">
                    <div>
                        <span class="text-xs text-secondary font-monospace"><?= htmlspecialchars($r['tender_no']) ?></span>
                        <h5 class="fw-bold text-white"><?= htmlspecialchars($r['title']) ?></h5>
                    </div>
                    <span class="status-badge status-draft"><?= $r['status'] ?></span>
                </div>
                
                <div class="bg-dark p-3 rounded-3 mb-3 text-sm">
                    <div class="text-secondary mb-1">Request From: <strong class="text-info"><?= htmlspecialchars($r['requester']) ?></strong></div>
                    <p class="mb-0 italic"><?= htmlspecialchars($r['request_details'] ?: 'No specific details provided.') ?></p>
                </div>

                <div class="mb-4">
                    <h6 class="text-xs text-secondary text-uppercase mb-2">Required Outputs</h6>
                    <div class="d-flex gap-2 flex-wrap">
                        <div class="form-check form-check-inline text-xs">
                            <input class="form-check-input" type="checkbox" checked disabled> MS Schedule
                        </div>
                        <div class="form-check form-check-inline text-xs">
                            <input class="form-check-input" type="checkbox" checked disabled> Manpower
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadOutputModal<?= $r['id'] ?>">
                        <i class="fas fa-upload me-2"></i> Submit Planning Data
                    </button>
                    <button class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-comment-dots me-2"></i> Query Technical Manager
                    </button>
                </div>
            </div>

            <!-- Upload Modal -->
            <div class="modal fade" id="uploadOutputModal<?= $r['id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content bg-dark-eval border-warning">
                        <form method="POST" action="modules/planning/engineer_dashboard/save_bid_output.php" enctype="multipart/form-data">
                            <input type="hidden" name="request_id" value="<?= $r['id'] ?>">
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold text-warning">Submit Bid Planning Data</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p class="text-sm text-secondary">Uploading planning specs for tender submission. Ensure accuracy with technical drawings.</p>
                                <div class="mb-3">
                                    <label class="form-label text-secondary">Output Type</label>
                                    <select class="form-select bg-dark text-white" name="output_type">
                                        <option value="ms_schedule">MS Schedule (Gantt)</option>
                                        <option value="manpower_plan">Manpower Plan</option>
                                        <option value="equipment_list">Equipment Schedule</option>
                                        <option value="material_list">Material Estimate</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-secondary">Select File</label>
                                    <input type="file" class="form-control bg-dark text-white" name="output_file" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-warning fw-bold text-dark">Submit to Tech Manager</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($requests)): ?>
        <div class="col-12 text-center py-5 glass-panel">
            <i class="fas fa-check-circle fa-3x text-success mb-3 opacity-50"></i>
            <h4 class="text-secondary">No active bid requests</h4>
            <p class="text-muted">You will be notified when the Technical Bid Manager requests planning data.</p>
        </div>
    <?php endif; ?>
</div>
