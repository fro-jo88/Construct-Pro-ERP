<?php
// modules/planning/engineer_dashboard/feedback.php

$db = Database::getInstance();
$user_id = $_SESSION['user_id'];

// In a real system, we'd have a feedback/comments table joined with schedules.
// For now, I'll mock some data or use 'gm_comments' as a generic feedback field if repurposing.
// But the requirement says "Manager comments mandatory on rejection".
// I'll assume we check for schedules/plans where status = 'revision_required'.

$revisions = [
    [
        'id' => 101,
        'type' => 'Manpower Schedule',
        'site' => 'Bole Tower',
        'comment' => 'Labor count for weeks 3-5 is under-estimated based on current structural pace. Please adjust.',
        'from' => 'Planning Manager',
        'date' => '2026-01-28 15:30'
    ],
    [
        'id' => 102,
        'type' => 'Weekly Plan',
        'site' => 'Mall of Addis',
        'comment' => 'Material request for concrete grade C30 is missing from the resource tab.',
        'from' => 'GM (via Manager)',
        'date' => '2026-01-29 09:15'
    ]
];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">Revisions & Feedback</h3>
    <button class="btn btn-sm btn-outline-secondary">Mark All as Read</button>
</div>

<div class="row g-4">
    <div class="col-md-5">
        <div class="glass-panel p-4 h-100">
            <h5 class="fw-bold mb-4">Pending Revisions</h5>
            <div class="list-group list-group-flush bg-transparent">
                <?php foreach ($revisions as $rev): ?>
                    <div class="list-group-item bg-transparent border-0 px-0 mb-3">
                        <div class="glass-card p-3 border-start border-4 border-danger" style="cursor: pointer;">
                            <div class="d-flex justify-content-between mb-1 text-xs">
                                <span class="text-danger fw-bold"><i class="fas fa-exclamation-circle me-1"></i> REVISION</span>
                                <span class="text-secondary"><?= date('M d, H:i', strtotime($rev['date'])) ?></span>
                            </div>
                            <h6 class="fw-bold text-white mb-1"><?= $rev['type'] ?></h6>
                            <p class="text-xs text-secondary mb-2"><?= $rev['site'] ?></p>
                            <div class="bg-dark-eval p-2 rounded-2 text-sm text-secondary italic mb-2">
                                "<?= $rev['comment'] ?>"
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-xs text-info">From: <?= $rev['from'] ?></span>
                                <button class="btn btn-xs btn-primary">Fix Now</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="glass-panel p-4 h-100 d-flex flex-column">
            <h5 class="fw-bold mb-4">General Communications</h5>
            <div class="flex-grow-1 d-flex align-items-center justify-content-center text-center opacity-50">
                <div>
                    <i class="fas fa-comments fa-4x mb-3"></i>
                    <p>No new messages from Management.</p>
                </div>
            </div>
            
            <div class="mt-4 pt-3 border-top border-secondary">
                 <div class="input-group">
                    <input type="text" class="form-control bg-dark text-white border-secondary" placeholder="Type a message to Planning Manager...">
                    <button class="btn btn-primary"><i class="fas fa-paper-plane"></i></button>
                 </div>
            </div>
        </div>
    </div>
</div>
