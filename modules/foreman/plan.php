<?php
// modules/foreman/plan.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/ForemanManager.php';

AuthManager::requireRole('FORMAN');
$site = ForemanManager::getAssignedSite($_SESSION['user_id']);
$plan = ForemanManager::getWeeklyPlan($site['id']);
?>

<div class="main-content mobile-layout">
    <div class="mb-3">
        <a href="index.php?module=foreman/dashboard" class="text-muted"><i class="fas fa-arrow-left"></i> To Dashboard</a>
        <h2 class="text-info mt-2">Weekly Plan</h2>
    </div>

    <?php if ($plan): ?>
    <div class="card glass-panel mb-4">
        <div class="card-header d-flex justify-content-between">
            <span>Week: <?= date('M d', strtotime($plan['week_start_date'])) ?> - <?= date('M d', strtotime($plan['week_end_date'])) ?></span>
            <span class="badge badge-success">APPROVED</span>
        </div>
        <div class="card-body">
            <h5 class="text-info">Primary Goals</h5>
            <div class="plan-content p-3 bg-dark rounded mb-3">
                <?= nl2br(htmlspecialchars($plan['goals'])) ?>
            </div>

            <div class="row text-center">
                <div class="col-6">
                    <h3 class="text-white"><?= $plan['planned_labor_count'] ?></h3>
                    <small class="text-muted">Target Manpower</small>
                </div>
                <div class="col-6">
                    <h3 class="text-white">100%</h3>
                    <small class="text-muted">Safety Target</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> This plan is read-only. Contact your Project Manager for adjustments.
    </div>

    <?php else: ?>
        <div class="alert alert-warning">
            <h4>No Plan Found</h4>
            <p>There is no approved plan for the current week. Please contact the Planning Department.</p>
        </div>
    <?php endif; ?>
</div>
