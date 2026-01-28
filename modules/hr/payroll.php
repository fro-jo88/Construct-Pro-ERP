<?php
// modules/hr/payroll.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/HRManager.php';

AuthManager::requireRole(['HR_MANAGER', 'FINANCE_HEAD', 'FINANCE_TEAM', 'GM']);

$is_finance = $_SESSION['role'] === 'finance';
$payrolls = []; // This should call HRManager::getPayrolls()

// For Demo/MVP, we'll fetch from the payroll table
$db = Database::getInstance();
$payrolls = $db->query("SELECT p.*, e.first_name, e.last_name, e.employee_code 
                        FROM payroll p 
                        JOIN employees e ON p.employee_id = e.id 
                        ORDER BY p.month_year DESC, e.last_name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && !$is_finance) {
    if ($_POST['action'] === 'generate') {
        HRManager::generatePayrollBatch($_POST['month_year'], $_SESSION['user_id']);
    }
    header("Location: main.php?module=hr/payroll&success=1");
    exit();
}
?>

<div class="payroll-module">
    <div class="section-header mb-4" style="display:flex; justify-content:space-between; align-items:center;">
        <h2><i class="fas fa-file-invoice-dollar"></i> Payroll Processing</h2>
        <?php if (!$is_finance): ?>
            <button class="btn-primary-sm" onclick="document.getElementById('payrollModal').style.display='flex'">+ Generate Monthly Payroll</button>
        <?php endif; ?>
    </div>

    <?php if ($is_finance): ?>
        <div class="alert alert-info glass-card mb-4" style="border-left: 5px solid #00c3ff;">
            <i class="fas fa-eye"></i> <strong>Finance View:</strong> You have read-only access to payroll data. Edits must be requested from the HR department.
        </div>
    <?php endif; ?>

    <div class="glass-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Emp Code</th>
                    <th>Employee Name</th>
                    <th>Period</th>
                    <th>Base Salary</th>
                    <th>Net Pay</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payrolls)): ?>
                    <tr><td colspan="7" style="text-align:center; padding:3rem;" class="text-dim">No payroll records found for the selected period.</td></tr>
                <?php else: ?>
                    <?php foreach ($payrolls as $p): ?>
                        <tr>
                            <td style="font-family: monospace; color: var(--gold);"><?= $p['employee_code'] ?></td>
                            <td><?= $p['first_name'] . ' ' . $p['last_name'] ?></td>
                            <td><?= $p['month_year'] ?></td>
                            <td>$<?= number_format($p['base_salary'], 2) ?></td>
                            <td style="font-weight:bold; color:var(--gold);">$<?= number_format($p['net_pay'], 2) ?></td>
                            <td><span class="status-badge <?= $p['status'] ?>"><?= strtoupper($p['status']) ?></span></td>
                            <td>
                                <button class="btn-secondary-sm"><i class="fas fa-print"></i> Payslip</button>
                                <?php if ($p['status'] === 'draft' && !$is_finance): ?>
                                    <button class="btn-primary-sm" style="font-size:0.75rem;">Approve</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div id="payrollModal" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:2000; justify-content:center; align-items:center;">
    <div class="glass-card" style="width:400px; padding:2rem;">
        <h3>Generate Batch Payroll</h3>
        <form method="POST">
            <input type="hidden" name="action" value="generate">
            <div class="form-group">
                <label>Target Month-Year</label>
                <input type="month" name="month_year" required value="<?= date('Y-m') ?>" style="width:100%; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); color:white; padding:0.8rem; border-radius:8px;">
            </div>
            <p style="font-size:0.8rem; color:var(--text-dim); margin-top:1rem;">
                <i class="fas fa-info-circle"></i> This will generate draft payroll entries for all ACTIVE employees based on their base salary and recorded attendance for the selected month.
            </p>
            <div style="display:flex; justify-content:flex-end; gap:1rem; margin-top:2rem;">
                <button type="button" class="btn-secondary-sm" onclick="document.getElementById('payrollModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn-primary-sm">Run Generation</button>
            </div>
        </form>
    </div>
</div>

<style>
.status-badge.paid { background: rgba(0, 255, 100, 0.2); color: #00ff64; }
.status-badge.approved { background: rgba(0, 195, 255, 0.2); color: #00c3ff; }
.status-badge.draft { background: rgba(255, 255, 255, 0.1); color: #ccc; }
</style>
