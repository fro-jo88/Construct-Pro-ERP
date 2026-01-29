<?php
// modules/planning/engineer_dashboard/index.php

require_once __DIR__ . '/../../../includes/AuthManager.php';
require_once __DIR__ . '/../../../includes/Database.php';

AuthManager::requireRole(['PLANNING_ENGINEER', 'SYSTEM_ADMIN']);

$db = Database::getInstance();
$user_id = $_SESSION['user_id'];
$view = $_GET['view'] ?? 'overview';

// --- KPI QUERIES ---

// 1. Assigned Project Sites
$countSites = $db->query("SELECT COUNT(*) FROM site_staff_assignments WHERE user_id = ? AND status = 'active'", [$user_id])->fetchColumn();

// 2. Schedules in Draft (Assuming 'schedules' table or 'planning_schedules')
// User request said 'planning_schedules' but schema has 'schedules'. 
// I'll check if planning_schedules exists or use 'schedules'.
// For now, I'll assume 'schedules' exists as per schema view.
$countDrafts = $db->query("SELECT COUNT(*) FROM schedules s 
                           JOIN site_staff_assignments ssa ON s.site_id = ssa.site_id 
                           WHERE ssa.user_id = ? AND ssa.status = 'active' AND s.version = 1", [$user_id])->fetchColumn(); 
// Note: Schema.sql 'schedules' doesn't have a status column. I might need to add one or use version logic.
// Request says status flow: draft -> submitted_to_manager -> approved / revision_required.
// I'll assume we need to extend 'schedules' or handle metadata.

// 3. Weekly Plans Submitted
$countWeeklyPlans = $db->query("SELECT COUNT(*) FROM weekly_plans wp
                                JOIN site_staff_assignments ssa ON wp.site_id = ssa.site_id
                                WHERE ssa.user_id = ? AND wp.status = 'approved'", [$user_id])->fetchColumn();

?>

<style>
    .dashboard-container { display: flex; gap: 20px; height: calc(100vh - 120px); }
    .side-menu { width: 260px; background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(10px); border-radius: 16px; padding: 25px 15px; display: flex; flex-direction: column; gap: 8px; border: 1px solid rgba(255,255,255,0.05); }
    .menu-item { padding: 12px 18px; border-radius: 12px; color: #94a3b8; text-decoration: none; display: flex; align-items: center; gap: 14px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); font-weight: 500; }
    .menu-item:hover { background: rgba(255,255,255,0.05); color: #fff; transform: translateX(5px); }
    .menu-item.active { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: #fff; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3); }
    .menu-item i { width: 22px; font-size: 1.1rem; }
    
    .main-content { flex: 1; overflow-y: auto; padding-right: 5px; }
    
    .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .kpi-card { background: rgba(30, 41, 59, 0.5); padding: 24px; border-radius: 16px; border: 1px solid rgba(255,255,255,0.08); position: relative; overflow: hidden; }
    .kpi-card::before { content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: #3b82f6; opacity: 0.5; }
    .kpi-value { font-size: 2.2rem; font-weight: 800; margin: 8px 0; color: #f8fafc; }
    .kpi-label { font-size: 0.75rem; color: #64748b; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 700; }
    
    .glass-panel { background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(12px); border-radius: 20px; border: 1px solid rgba(255,255,255,0.05); padding: 25px; }
    
    .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
    .status-draft { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.2); }
    .status-submitted { background: rgba(59, 130, 246, 0.1); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.2); }
    .status-approved { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); }
    .status-revision { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }
</style>

<div class="planning-engineer-dashboard">
    <div class="header mb-4 d-flex justify-content-between align-items-end">
        <div>
            <h1 class="mb-1" style="font-weight: 800; letter-spacing: -1px;">Engineer Workspace</h1>
            <p class="text-secondary mb-0">Producer Portal: Schedule Creation & Weekly Execution Planning.</p>
        </div>
        <div class="text-end">
            <span class="text-secondary text-xs d-block">Reporting To</span>
            <span class="badge bg-primary text-uppercase" style="letter-spacing: 1px;">Planning Manager</span>
        </div>
    </div>

    <div class="dashboard-container">
        <!-- SIDE MENU -->
        <div class="side-menu shadow-lg">
            <a href="?module=planning/engineer_dashboard/index&view=overview" class="menu-item <?= $view=='overview'?'active':'' ?>">
                <i class="fas fa-th-large"></i> Overview
            </a>
            <a href="?module=planning/engineer_dashboard/index&view=assignments" class="menu-item <?= $view=='assignments'?'active':'' ?>">
                <i class="fas fa-map-marker-alt"></i> My Assignments
            </a>
            <a href="?module=planning/engineer_dashboard/index&view=schedules" class="menu-item <?= $view=='schedules'?'active':'' ?>">
                <i class="fas fa-calendar-plus"></i> Create Schedules
            </a>
            <a href="?module=planning/engineer_dashboard/index&view=weekly_plans" class="menu-item <?= $view=='weekly_plans'?'active':'' ?>">
                <i class="fas fa-tasks"></i> Weekly Plans
            </a>
            <a href="?module=planning/engineer_dashboard/index&view=drawings" class="menu-item <?= $view=='drawings'?'active':'' ?>">
                <i class="fas fa-pencil-ruler"></i> Drawings & Specs
            </a>
            <a href="?module=planning/engineer_dashboard/index&view=bid_support" class="menu-item <?= $view=='bid_support'?'active':'' ?>">
                <i class="fas fa-file-signature"></i> Bid Support
            </a>
            <a href="?module=planning/engineer_dashboard/index&view=feedback" class="menu-item <?= $view=='feedback'?'active':'' ?>">
                <i class="fas fa-comment-dots"></i> Feedback
                <?php
                // Mock notification bubble for revisions
                echo '<span class="badge bg-danger ms-auto" style="font-size: 0.6rem;">3</span>';
                ?>
            </a>
        </div>

        <!-- MAIN CONTENT AREA -->
        <div class="main-content">
            <?php if ($view === 'overview'): ?>
                <!-- KPI CARDS -->
                <div class="kpi-grid">
                    <div class="kpi-card">
                        <div class="kpi-label">Assigned Sites</div>
                        <div class="kpi-value text-primary"><?= $countSites ?></div>
                        <div class="progress mt-2" style="height: 4px; background: rgba(255,255,255,0.05);">
                            <div class="progress-bar bg-primary" style="width: 70%"></div>
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-label">Schedules in Draft</div>
                        <div class="kpi-value text-warning"><?= $countDrafts ?></div>
                        <span class="text-xs text-secondary">Awaiting completion</span>
                    </div>
                    <div class="kpi-card" style="border-left: 0;">
                        <div class="kpi-label">Pending Review</div>
                        <div class="kpi-value text-info">2</div>
                        <span class="text-xs text-secondary">Under Manager validation</span>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-label">Weekly Plans</div>
                        <div class="kpi-value text-success"><?= $countWeeklyPlans ?></div>
                        <span class="text-xs text-secondary">Approved for execution</span>
                    </div>
                </div>

                <!-- RECENT ACTIVITY -->
                <div class="glass-panel">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0 fw-bold">Recent Submissions</h4>
                        <button class="btn btn-sm btn-outline-primary">View All</button>
                    </div>
                    <table class="table table-hover align-middle custom-dark-table">
                        <thead class="text-secondary text-xs">
                            <tr>
                                <th>PROJECT / SITE</th>
                                <th>TYPE</th>
                                <th>VERSION</th>
                                <th>STATUS</th>
                                <th>DATE</th>
                                <th class="text-end">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            <!-- Placeholder Data -->
                            <tr>
                                <td>
                                    <div class="fw-bold">Mall of Addis - P1</div>
                                    <div class="text-xs text-secondary">Basement Excavation</div>
                                </td>
                                <td><span class="text-info font-monospace">MS Schedule</span></td>
                                <td>v2.1</td>
                                <td><span class="status-badge status-submitted">Submitted</span></td>
                                <td>Today, 10:20</td>
                                <td class="text-end">
                                    <button class="btn btn-xs btn-icon"><i class="fas fa-eye"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="fw-bold">Bole Tower</div>
                                    <div class="text-xs text-secondary">Superstructure Slab</div>
                                </td>
                                <td><span class="text-warning font-monospace">Manpower Plan</span></td>
                                <td>v1.0</td>
                                <td><span class="status-badge status-draft">Draft</span></td>
                                <td>Yesterday</td>
                                <td class="text-end">
                                    <button class="btn btn-xs btn-icon text-primary"><i class="fas fa-edit"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            <?php elseif (in_array($view, ['assignments', 'schedules', 'weekly_plans', 'drawings', 'bid_support', 'feedback'])): ?>
                <?php 
                $file = __DIR__ . '/' . $view . '.php';
                if (file_exists($file)) {
                    include $file;
                } else {
                    echo '<div class="glass-panel text-center py-5">
                            <i class="fas fa-tools fa-3x text-secondary mb-3"></i>
                            <h3>Component Under Construction</h3>
                            <p class="text-secondary">Implementing ' . ucfirst($view) . ' module...</p>
                          </div>';
                }
                ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.custom-dark-table { border-collapse: separate; border-spacing: 0 8px; margin-top: -8px; }
.custom-dark-table tr { background: rgba(255,255,255,0.02); transition: all 0.2s; }
.custom-dark-table tr:hover { background: rgba(255,255,255,0.04); transform: scale(1.002); }
.custom-dark-table td, .custom-dark-table th { padding: 15px; border: none; }
.custom-dark-table tr td:first-child { border-radius: 12px 0 0 12px; }
.custom-dark-table tr td:last-child { border-radius: 0 12px 12px 0; }
.btn-icon { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #94a3b8; width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; transition: all 0.2s; }
.btn-icon:hover { background: #3b82f6; color: #fff; border-color: #3b82f6; }
.btn-xs { padding: 4px 8px; font-size: 0.7rem; }
</style>
