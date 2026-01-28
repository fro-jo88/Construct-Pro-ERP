<?php
// modules/hr/dashboard.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/HRManager.php';

AuthManager::requireRole('HR_MANAGER');

$stats = HRManager::getDashboardStats();
$announcements = HRManager::getAnnouncements();
?>

<div class="hr-dashboard">
    <!-- Quick Stats -->
    <div class="widget-grid mb-4">
        <div class="widget glass-card">
            <div class="widget-header">
                <h3>Total Employees</h3>
                <i class="fas fa-users text-gold"></i>
            </div>
            <div class="widget-content">
                <div class="value"><?= $stats['total_employees'] ?></div>
                <div class="status-badge active">Active Staff</div>
            </div>
        </div>
        
        <div class="widget glass-card">
            <div class="widget-header">
                <h3>Active Bids (HR Origin)</h3>
                <i class="fas fa-gavel text-gold"></i>
            </div>
            <div class="widget-content">
                <div class="value"><?= $stats['active_tenders'] ?></div>
                <a href="main.php?module=hr/tenders" class="btn-secondary-sm">Manage Bids</a>
            </div>
        </div>

        <div class="widget glass-card">
            <div class="widget-header">
                <h3>Attendance Today</h3>
                <i class="fas fa-calendar-check text-gold"></i>
            </div>
            <div class="widget-content">
                <div class="value"><?= $stats['attendance_today'] ?></div>
                <div class="status-badge pending">Site Logins</div>
            </div>
        </div>

        <div class="widget glass-card">
            <div class="widget-header">
                <h3>Pending Actions</h3>
                <i class="fas fa-exclamation-circle text-gold"></i>
            </div>
            <div class="widget-content">
                <div class="list-group">
                    <div class="d-flex justify-content-between">
                        <span>Leaves:</span>
                        <span class="badge badge-gold"><?= $stats['pending_leaves'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Material Req:</span>
                        <span class="badge badge-gold"><?= $stats['pending_materials'] ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row" style="display: flex; gap: 1.5rem;">
        <!-- Left Column: Operations -->
        <div style="flex: 2; display: flex; flex-direction: column; gap: 1.5rem;">
            <!-- Bid Management -->
            <section class="glass-card">
                <div class="section-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                    <h3><i class="fas fa-file-signature"></i> Recent Tenders</h3>
                    <a href="main.php?module=hr/tenders" class="btn-primary-sm">Open Module</a>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tender No</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Deadline</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $tenders = array_slice(HRManager::getTenders(), 0, 5);
                        foreach ($tenders as $t): ?>
                            <tr>
                                <td><?= $t['tender_no'] ?></td>
                                <td><?= $t['title'] ?></td>
                                <td><span class="status-badge"><?= $t['status'] ?></span></td>
                                <td><?= date('M d, Y', strtotime($t['deadline'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

            <!-- Recruitment Widget -->
            <section class="glass-card">
                <div class="section-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                    <h3><i class="fas fa-user-plus"></i> Recruitment Tracker</h3>
                    <a href="main.php?module=hr/recruitment" class="btn-primary-sm">View Jobs</a>
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                    <div class="mini-stat">
                        <span class="label">Open Job Requests</span>
                        <span class="val">4</span>
                    </div>
                    <div class="mini-stat">
                        <span class="label">New Applicants</span>
                        <span class="val">12</span>
                    </div>
                </div>
            </section>
        </div>

        <!-- Right Column: Communication & Announcements -->
        <div style="flex: 1; display: flex; flex-direction: column; gap: 1.5rem;">
            <section class="glass-card">
                <h3><i class="fas fa-bullhorn"></i> Internal Announcements</h3>
                <div class="announcement-list">
                    <?php if (empty($announcements)): ?>
                        <p class="text-dim">No recent announcements.</p>
                    <?php else: ?>
                        <?php foreach ($announcements as $a): ?>
                            <div class="announcement-item" style="border-bottom: 1px solid rgba(255,255,255,0.05); padding: 0.5rem 0;">
                                <div style="font-weight:bold; color:var(--gold);"><?= htmlspecialchars($a['title']) ?></div>
                                <div style="font-size:0.8rem; color:var(--text-dim);"><?= substr(htmlspecialchars($a['content']), 0, 50) ?>...</div>
                                <div style="font-size:0.7rem; color:var(--text-dim); margin-top:0.2rem;"><?= date('M d, H:i', strtotime($a['created_at'])) ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <button class="btn-secondary-sm mt-3" style="width:100%;">Create Announcement</button>
            </section>

            <section class="glass-card">
                <h3><i class="fas fa-link"></i> Quick Module Links</h3>
                <div class="nav-links">
                    <ul style="list-style:none; padding:0;">
                        <li style="margin-bottom:0.5rem;"><a href="main.php?module=hr/employees" class="text-dim"><i class="fas fa-user-tie"></i> Employee Lifecycle</a></li>
                        <li style="margin-bottom:0.5rem;"><a href="main.php?module=hr/payroll" class="text-dim"><i class="fas fa-file-invoice-dollar"></i> Payroll Control</a></li>
                        <li style="margin-bottom:0.5rem;"><a href="main.php?module=hr/attendance" class="text-dim"><i class="fas fa-clock"></i> Attendance Review</a></li>
                        <li style="margin-bottom:0.5rem;"><a href="main.php?module=hr/materials" class="text-dim"><i class="fas fa-forward"></i> Material Validation</a></li>
                        <li style="margin-bottom:0.5rem;"><a href="main.php?module=hr/sites" class="text-dim"><i class="fas fa-map-marker-alt"></i> Site Initialization</a></li>
                    </ul>
                </div>
            </section>
        </div>
    </div>
</div>

<style>
.hr-dashboard .mini-stat {
    background: rgba(0,0,0,0.2);
    padding: 1rem;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
}
.hr-dashboard .mini-stat .label { font-size: 0.75rem; color: var(--text-dim); }
.hr-dashboard .mini-stat .val { font-size: 1.5rem; font-weight: bold; color: var(--gold); }
.badge-gold { background: var(--gold); color: black; padding: 2px 6px; border-radius: 4px; font-weight: bold; font-size: 0.8rem; }
</style>
