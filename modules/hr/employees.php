<?php
// modules/hr/employees.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/HRManager.php';

AuthManager::requireRole(['HR_MANAGER', 'GM']);

$employees = HRManager::getAllEmployees();
?>

<div class="employees-module">
    <div class="section-header mb-4" style="display:flex; justify-content:space-between; align-items:center;">
        <h2><i class="fas fa-user-tie"></i> Workforce Directory</h2>
        <a href="main.php?module=hr/add_employee" class="btn-primary-sm" style="text-decoration:none;">+ New Employee</a>
    </div>

    <div class="glass-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Full Name</th>
                    <th>Designation</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>GM Approval</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employees as $emp): ?>
                    <tr>
                        <td style="font-family: monospace; color: var(--gold);"><?= $emp['employee_code'] ?></td>
                        <td>
                            <div style="font-weight:bold;"><?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?></div>
                            <div style="font-size:0.75rem; color:var(--text-dim);"><?= htmlspecialchars($emp['username'] ?? '') ?></div>
                        </td>
                        <td><?= htmlspecialchars($emp['position'] ?? '') ?></td>
                        <td><?= htmlspecialchars($emp['department'] ?? '') ?></td>
                        <td>
                            <span class="status-badge <?= $emp['status'] ?>"><?= strtoupper($emp['status']) ?></span>
                        </td>
                        <td>
                            <?php if (isset($emp['gm_approval_status'])): ?>
                                <span class="status-badge <?= $emp['gm_approval_status'] ?>"><?= strtoupper($emp['gm_approval_status']) ?></span>
                            <?php else: ?>
                                <span class="status-badge">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="main.php?module=hr/employee_details&id=<?= $emp['id'] ?>" class="btn-secondary-sm"><i class="fas fa-eye"></i> Details</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.status-badge.active { background: rgba(0, 255, 100, 0.2); color: #00ff64; }
.status-badge.pending { background: rgba(255, 204, 0, 0.2); color: var(--gold); }
.status-badge.terminated { background: rgba(255, 68, 68, 0.2); color: #ff4444; }
.status-badge.approved { background: rgba(0, 255, 100, 0.2); color: #00ff64; }
.status-badge.rejected { background: rgba(255, 68, 68, 0.2); color: #ff4444; }
</style>
