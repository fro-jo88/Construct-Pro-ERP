<?php
// modules/audit/construction_dashboard/index.php

require_once __DIR__ . '/../../../includes/AuthManager.php';
require_once __DIR__ . '/../../../includes/Database.php';

AuthManager::requireRole(['CONSTRUCTION_AUDIT', 'GM', 'SYSTEM_ADMIN']);

$db = Database::getInstance();
$user_id = $_SESSION['user_id'];
$view = $_GET['view'] ?? 'overview';

// --- KPI DATA ---
$totalAudits = $db->query("SELECT COUNT(*) FROM audit_reports WHERE auditor_id = ?", [$user_id])->fetchColumn();
$pendingFindings = $db->query("SELECT COUNT(*) FROM audit_findings WHERE auditor_id = ? AND status = 'draft'", [$user_id])->fetchColumn();
$criticalIssues = $db->query("SELECT COUNT(*) FROM audit_findings WHERE auditor_id = ? AND severity = 'critical' AND status = 'submitted'", [$user_id])->fetchColumn();

$today = date('Y-m-d');
$thisMonth = date('Y-m');
$monthlyReports = $db->query("SELECT COUNT(*) FROM audit_reports WHERE auditor_id = ? AND DATE_FORMAT(created_at, '%Y-%m') = ?", [$user_id, $thisMonth])->fetchColumn();

?>

<style>
    :root { --audit-primary: #ef4444; --audit-bg: #0f172a; --audit-accent: #f59e0b; }
    .audit-dashboard { padding: 25px; color: #f8fafc; }
    
    .kpi-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .kpi-card { background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(255,255,255,0.05); border-radius: 20px; padding: 25px; position: relative; overflow: hidden; }
    .kpi-card:hover { transform: translateY(-5px); transition: 0.3s; border-color: var(--audit-primary); }
    .kpi-card i { position: absolute; right: -15px; bottom: -15px; font-size: 5rem; opacity: 0.05; transform: rotate(-15deg); }
    .kpi-val { font-size: 2.2rem; font-weight: 800; margin: 5px 0; color: #fff; }
    .kpi-lbl { font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; font-weight: 700; letter-spacing: 1.5px; }

    .nav-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
    .nav-card { background: rgba(30, 41, 59, 0.4); border: 1px solid rgba(255,255,255,0.08); border-radius: 20px; padding: 25px; text-decoration: none; color: inherit; transition: 0.3s; }
    .nav-card:hover { background: rgba(239, 68, 68, 0.1); border-color: var(--audit-primary); box-shadow: 0 10px 20px rgba(0,0,0,0.2); }
    
    .glass-panel { background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(12px); border-radius: 24px; border: 1px solid rgba(255,255,255,0.05); padding: 30px; }
    .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
    .flag-critical { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }
    .flag-high { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.2); }
    .flag-normal { background: rgba(34, 197, 94, 0.1); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.2); }
</style>

<div class="audit-dashboard">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <span class="badge bg-danger mb-2 px-3 fw-bold">CONSTRUCTION AUDIT</span>
            <h1 class="fw-extrabold mb-1" style="font-size: 2.5rem; letter-spacing: -1.5px;">Independent Verification</h1>
            <p class="text-secondary mb-0 fw-medium">Site Execution, Material Usage & Reporting Accuracy Control.</p>
        </div>
        <div class="text-end">
            <div class="h2 fw-bold mb-0 text-white"><?= date('H:i') ?></div>
            <div class="text-secondary text-sm"><?= date('l, M d') ?></div>
        </div>
    </div>

    <!-- KPI ROW -->
    <div class="kpi-row">
        <div class="kpi-card">
            <i class="fas fa-clipboard-check"></i>
            <div class="kpi-lbl">Total Audits</div>
            <div class="kpi-val text-info"><?= $totalAudits ?></div>
            <div class="text-xs text-secondary">Lifetime Reports</div>
        </div>
        <div class="kpi-card" style="border-left: 4px solid #f59e0b;">
            <i class="fas fa-exclamation-triangle"></i>
            <div class="kpi-lbl">Pending Findings</div>
            <div class="kpi-val text-warning"><?= $pendingFindings ?></div>
            <div class="text-xs text-secondary">Awaiting Submission</div>
        </div>
        <div class="kpi-card" style="border-left: 4px solid #ef4444;">
            <i class="fas fa-radiation"></i>
            <div class="kpi-lbl">Critical Issues</div>
            <div class="kpi-val text-danger"><?= $criticalIssues ?></div>
            <div class="text-xs text-secondary">Require Immediate Action</div>
        </div>
        <div class="kpi-card">
            <i class="fas fa-calendar-alt"></i>
            <div class="kpi-lbl">This Month</div>
            <div class="kpi-val text-success"><?= $monthlyReports ?></div>
            <div class="text-xs text-secondary">Reports Submitted</div>
        </div>
    </div>

    <?php if ($view === 'overview'): ?>
        <div class="nav-grid">
            <a href="?module=audit/construction_dashboard/index&view=site_audits" class="nav-card">
                <i class="fas fa-hard-hat fa-2x text-danger mb-3"></i>
                <h5 class="fw-bold text-white">Site Execution Audits</h5>
                <p class="text-sm text-secondary mb-0">Verify planning vs actual progress, work quality, and Forman reporting accuracy.</p>
            </a>
            <a href="?module=audit/construction_dashboard/index&view=material_audits" class="nav-card">
                <i class="fas fa-boxes fa-2x text-warning mb-3"></i>
                <h5 class="fw-bold text-white">Material Usage Audits</h5>
                <p class="text-sm text-secondary mb-0">Cross-check planned quantities against store issues and site consumption.</p>
            </a>
            <a href="?module=audit/construction_dashboard/index&view=new_audit" class="nav-card">
                <i class="fas fa-plus-circle fa-2x text-success mb-3"></i>
                <h5 class="fw-bold text-white">New Audit Session</h5>
                <p class="text-sm text-secondary mb-0">Start a fresh audit for a specific site, period, and work category.</p>
            </a>
            <a href="?module=audit/construction_dashboard/index&view=reports" class="nav-card">
                <i class="fas fa-file-alt fa-2x text-info mb-3"></i>
                <h5 class="fw-bold text-white">Reports Archive</h5>
                <p class="text-sm text-secondary mb-0">Access historical audit submissions and track resolution status.</p>
            </a>
        </div>

        <!-- RECENT ACTIVITY -->
        <div class="glass-panel mt-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0">Recent Audit Activity</h5>
                <span class="text-secondary text-xs text-uppercase fw-bold letter-spacing-1">Last 7 Days</span>
            </div>
            <div class="table-responsive">
                <table class="table text-white mb-0">
                    <thead class="text-secondary text-xs border-0">
                        <tr>
                            <th>DATE</th>
                            <th>SITE</th>
                            <th>CATEGORY</th>
                            <th>FINDINGS</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $recentActivity = $db->query("SELECT ar.*, s.site_name, p.project_name 
                                                      FROM audit_reports ar 
                                                      LEFT JOIN sites s ON ar.site_id = s.id 
                                                      LEFT JOIN projects p ON ar.project_id = p.id 
                                                      WHERE ar.auditor_id = ? 
                                                      AND ar.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                                                      ORDER BY ar.created_at DESC LIMIT 5", [$user_id])->fetchAll();
                        
                        foreach ($recentActivity as $activity):
                        ?>
                        <tr class="align-middle">
                            <td class="text-xs"><?= date('M d, Y', strtotime($activity['created_at'])) ?></td>
                            <td>
                                <div class="fw-medium"><?= htmlspecialchars($activity['site_name'] ?? 'N/A') ?></div>
                                <div class="text-xs text-secondary"><?= htmlspecialchars($activity['project_name'] ?? '') ?></div>
                            </td>
                            <td class="text-xs"><?= htmlspecialchars($activity['work_category'] ?? 'General') ?></td>
                            <td>
                                <span class="badge bg-<?= $activity['critical_findings'] > 0 ? 'danger' : 'secondary' ?>"><?= $activity['total_findings'] ?></span>
                                <?php if ($activity['critical_findings'] > 0): ?>
                                    <span class="text-danger text-xs ms-1">(<?= $activity['critical_findings'] ?> critical)</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="status-badge flag-<?= $activity['status'] === 'submitted' ? 'normal' : 'high' ?>"><?= strtoupper($activity['status']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recentActivity)): ?>
                            <tr><td colspan="5" class="text-center py-4 text-secondary italic">No recent audit activity.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php else: ?>
        <!-- SUB-MODULE LOADER -->
        <div class="module-view mt-4">
            <?php 
            $file = __DIR__ . '/' . $view . '.php';
            if (file_exists($file)) {
                include $file;
            } else {
                echo '<div class="glass-panel text-center py-5">
                        <i class="fas fa-search-location fa-3x text-secondary mb-3 opacity-20"></i>
                        <h3>Audit Module Loading...</h3>
                        <p class="text-secondary">Initializing ' . ucfirst(str_replace('_', ' ', $view)) . ' verification engine.</p>
                      </div>';
            }
            ?>
        </div>
    <?php endif; ?>
</div>
