<?php
// modules/site/forman_dashboard/index.php

require_once __DIR__ . '/../../../includes/AuthManager.php';
require_once __DIR__ . '/../../../includes/Database.php';

AuthManager::requireRole(['FORMAN', 'SYSTEM_ADMIN']);

$db = Database::getInstance();
$user_id = $_SESSION['user_id'];
$view = $_GET['view'] ?? 'overview';

// --- SITE CONTEXT ---
// Get site assigned to this foreman
$site = $db->query("SELECT s.*, p.project_name FROM sites s 
                    JOIN projects p ON s.project_id = p.id 
                    WHERE s.foreman_id = ?", [$user_id])->fetch();

$site_id = $site['id'] ?? null;

// --- KPI DATA ---
$today = date('Y-m-d');
$todayReport = $site_id ? $db->query("SELECT * FROM daily_site_reports WHERE site_id = ? AND report_date = ?", [$site_id, $today])->fetch() : null;
$pendingRequests = $site_id ? $db->query("SELECT COUNT(*) FROM material_requests WHERE site_id = ? AND status = 'pending'", [$site_id])->fetchColumn() : 0;
$activeIssues = $site_id ? $db->query("SELECT COUNT(*) FROM site_incidents WHERE site_id = ? AND status != 'resolved'", [$site_id])->fetchColumn() : 0;

// Get weekly plan for context
$activePlan = $site_id ? $db->query("SELECT * FROM weekly_plans WHERE site_id = ? AND ? BETWEEN week_start_date AND week_end_date AND status = 'approved'", [$site_id, $today])->fetch() : null;

?>

<style>
    :root { --site-gold: #f59e0b; --site-dark: #0f172a; --site-border: rgba(255,255,255,0.08); }
    .forman-dashboard { padding: 20px; color: #e2e8f0; }
    .site-header { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); padding: 30px; border-radius: 20px; border: 1px solid var(--site-border); margin-bottom: 30px; position: relative; overflow: hidden; }
    .site-header::after { content: '\f807'; font-family: 'Font Awesome 5 Free'; font-weight: 900; position: absolute; right: -20px; bottom: -20px; font-size: 15rem; opacity: 0.03; color: white; }
    
    .nav-card { background: rgba(30, 41, 59, 0.5); border: 1px solid var(--site-border); border-radius: 16px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .nav-card:hover { transform: translateY(-5px); background: rgba(51, 65, 85, 0.5); border-color: var(--site-gold); }
    .nav-link { text-decoration: none; color: inherit; display: block; padding: 20px; }
    
    .kpi-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .kpi-box { background: rgba(15, 23, 42, 0.6); padding: 20px; border-radius: 16px; border-left: 4px solid var(--site-gold); }
    .kpi-val { font-size: 1.8rem; font-weight: 800; color: #fff; margin: 5px 0; }
    .kpi-lbl { font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; font-weight: 700; letter-spacing: 1px; }

    .glass-panel { background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(10px); border-radius: 20px; border: 1px solid var(--site-border); padding: 25px; }
</style>

<div class="forman-dashboard">
    <?php if (!$site): ?>
        <div class="glass-panel text-center py-5 shadow-2xl">
            <i class="fas fa-exclamation-circle fa-4x text-warning mb-4"></i>
            <h2 class="fw-bold">No Active Site Assigned</h2>
            <p class="text-secondary">Please contact the General Manager or PLANNING_MANAGER to assign you to a site location.</p>
        </div>
    <?php else: ?>
        <div class="site-header shadow-xl">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge bg-warning text-dark mb-2 px-3 py-2 fw-bold" style="letter-spacing: 1px;">ACTIVE EXECUTION</span>
                    <h1 class="display-5 fw-bold text-white mb-1"><?= htmlspecialchars($site['site_name']) ?></h1>
                    <p class="text-secondary mb-0 fw-medium"><i class="fas fa-building me-2"></i> Project: <?= htmlspecialchars($site['project_name']) ?></p>
                </div>
                <div class="text-end">
                    <div class="h3 fw-bold mb-0 text-gold"><?= date('H:i') ?></div>
                    <div class="text-secondary text-sm"><?= date('D, M d, Y') ?></div>
                </div>
            </div>
        </div>

        <div class="kpi-row">
            <div class="kpi-box">
                <div class="kpi-lbl">Today's Progress</div>
                <div class="kpi-val"><?= $todayReport ? $todayReport['progress_percent'] : '0' ?>%</div>
                <div class="progress" style="height: 4px; background: rgba(255,255,255,0.05);">
                    <div class="progress-bar bg-warning" style="width: <?= $todayReport ? $todayReport['progress_percent'] : '0' ?>%"></div>
                </div>
            </div>
            <div class="kpi-box">
                <div class="kpi-lbl">Labor Count</div>
                <div class="kpi-val"><?= $todayReport ? $todayReport['labor_count'] : '0' ?></div>
                <span class="text-xs text-secondary">Active on site</span>
            </div>
            <div class="kpi-box">
                <div class="kpi-lbl">Material Requests</div>
                <div class="kpi-val"><?= $pendingRequests ?></div>
                <span class="text-xs text-secondary">Awaiting supply</span>
            </div>
            <div class="kpi-box" style="border-left-color: #ef4444;">
                <div class="kpi-lbl">Active Issues</div>
                <div class="kpi-val"><?= $activeIssues ?></div>
                <span class="text-xs text-secondary">Requires attention</span>
            </div>
        </div>

        <!-- NAVIGATION GRID (When in Overview) -->
        <?php if ($view === 'overview'): ?>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="nav-card">
                        <a href="?module=site/forman_dashboard/index&view=reports" class="nav-link">
                            <i class="fas fa-clipboard-check mb-3 text-gold fa-2x"></i>
                            <h5 class="fw-bold">Daily Progress Reporting</h5>
                            <p class="text-secondary text-sm mb-0">Submit actual work completed, labor, and equipment logs today.</p>
                        </a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="nav-card">
                        <a href="?module=site/forman_dashboard/index&view=materials" class="nav-link">
                            <i class="fas fa-truck-loading mb-3 text-gold fa-2x"></i>
                            <h5 class="fw-bold">Material Requests</h5>
                            <p class="text-secondary text-sm mb-0">Order concrete, steel, or tools based on your weekly plan.</p>
                        </a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="nav-card">
                        <a href="?module=site/forman_dashboard/index&view=plans" class="nav-link">
                            <i class="fas fa-map mb-3 text-gold fa-2x"></i>
                            <h5 class="fw-bold">Weekly Plan Viewer</h5>
                            <p class="text-secondary text-sm mb-0">View approved execution targets from the Planning Team.</p>
                        </a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="nav-card">
                        <a href="?module=site/forman_dashboard/index&view=safety" class="nav-link">
                            <i class="fas fa-hard-hat mb-3 text-gold fa-2x"></i>
                            <h5 class="fw-bold">Safety & Issue Log</h5>
                            <p class="text-secondary text-sm mb-0">Report accidents, site delays, or critical risks directly to GM.</p>
                        </a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="nav-card">
                        <a href="?module=site/forman_dashboard/index&view=messages" class="nav-link">
                            <i class="fas fa-envelope-open-text mb-3 text-gold fa-2x"></i>
                            <h5 class="fw-bold">GM Communication</h5>
                            <p class="text-secondary text-sm mb-0">Receive instructions and feedback on your daily submissions.</p>
                        </a>
                    </div>
                </div>
            </div>

            <!-- TODAY'S PLAN PREVIEW -->
            <div class="glass-panel mt-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 fw-bold">Active Week Targets</h5>
                    <span class="text-secondary text-sm">Valid until <?= $activePlan ? date('M d', strtotime($activePlan['week_end_date'])) : '--' ?></span>
                </div>
                <?php if ($activePlan): ?>
                    <div class="bg-dark p-3 rounded-3 border border-secondary">
                        <h6 class="text-gold fw-bold mb-2">Primary Goals:</h6>
                        <p class="text-sm"><?= nl2br(htmlspecialchars($activePlan['goals'])) ?></p>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-sm py-2">No approved weekly plan found for current period.</div>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <!-- VIEW CONTENT -->
            <div class="main-view mt-4">
                <?php 
                $file = __DIR__ . '/' . $view . '.php';
                if (file_exists($file)) {
                    include $file;
                } else {
                    echo '<div class="glass-panel text-center py-5">
                            <i class="fas fa-tools fa-3x text-secondary mb-3"></i>
                            <h3>Component Scaling...</h3>
                            <p class="text-secondary text-sm">Building out ' . $view . ' module to site standards.</p>
                          </div>';
                }
                ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
