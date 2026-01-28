<?php
// modules/hr/attendance.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/HRManager.php';

AuthManager::requireRole(['HR_MANAGER', 'FORMAN']);

$site_id = $_GET['site_id'] ?? null;
$date = $_GET['date'] ?? date('Y-m-d');
$sites = HRManager::getSites();
$attendance = [];

if ($site_id) {
    $attendance = HRManager::getSiteAttendance($site_id, $date);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'review') {
    HRManager::reviewAttendance($_POST['attendance_id'], $_SESSION['user_id']);
    header("Location: main.php?module=hr/attendance&site_id=$site_id&date=$date&success=reviewed");
    exit();
}
?>

<div class="attendance-module">
    <div class="section-header mb-4" style="display:flex; justify-content:space-between; align-items:center;">
        <h2><i class="fas fa-clock"></i> Site Attendance Control</h2>
        <form method="GET" style="display:flex; gap:1rem; align-items:center;">
            <input type="hidden" name="module" value="hr/attendance">
            <select name="site_id" required style="background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); color:white; padding:0.5rem; border-radius:8px;">
                <option value="">Select Site...</option>
                <?php foreach ($sites as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= $site_id == $s['id'] ? 'selected' : '' ?>><?= $s['site_name'] ?></option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="date" value="<?= $date ?>" style="background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); color:white; padding:0.5rem; border-radius:8px;">
            <button type="submit" class="btn-primary-sm">View Log</button>
        </form>
    </div>

    <div class="glass-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Status</th>
                    <th>OT Hours</th>
                    <th>Foreman Submit</th>
                    <th>HR Review</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($attendance)): ?>
                    <tr><td colspan="6" style="text-align:center; padding:3rem;" class="text-dim">Select a site and date to view attendance records.</td></tr>
                <?php else: ?>
                    <?php foreach ($attendance as $a): ?>
                        <tr>
                            <td><?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?></td>
                            <td><span class="status-badge <?= $a['status'] ?>"><?= strtoupper($a['status']) ?></span></td>
                            <td><?= $a['overtime_hours'] ?> hrs</td>
                            <td><i class="fas fa-check-circle text-success"></i> Submitted</td>
                            <td>
                                <?php if ($a['hr_reviewed_by']): ?>
                                    <span class="status-badge approved">Reviewed</span>
                                <?php else: ?>
                                    <span class="status-badge pending">Pending Review</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!$a['hr_reviewed_by']): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="review">
                                        <input type="hidden" name="attendance_id" value="<?= $a['id'] ?>">
                                        <button type="submit" class="btn-primary-sm" style="font-size:0.75rem;">Mark Reviewed</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Payroll Link Notice -->
    <div class="alert alert-warning mt-4 glass-card" style="border-left: 5px solid var(--gold);">
        <i class="fas fa-link"></i> <strong>Payroll Integration:</strong> Verified attendance records are automatically factored into monthly payroll calculations for site-based employees.
    </div>
</div>

<style>
.status-badge.present { background: rgba(0, 255, 100, 0.2); color: #00ff64; }
.status-badge.absent { background: rgba(255, 68, 68, 0.2); color: #ff4444; }
.status-badge.late { background: rgba(255, 204, 0, 0.2); color: var(--gold); }
</style>
