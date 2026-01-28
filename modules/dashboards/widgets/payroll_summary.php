<?php
// modules/dashboards/widgets/payroll_summary.php
require_once __DIR__ . '/../../../includes/Database.php';

$db = Database::getInstance();
$total_payroll = "0.00";
$pending_batches = "0";

try {
    $total_payroll = $db->query("SELECT SUM(net_pay) FROM payroll WHERE status = 'draft'")->fetchColumn() ?: "0.00";
    $pending_batches = $db->query("SELECT COUNT(DISTINCT month_year) FROM payroll WHERE status = 'draft'")->fetchColumn() ?: "0";
} catch (Exception $e) { /* Ignore */ }

?>
<div class="widget glass-card">
    <div class="widget-header">
        <h3><i class="fas fa-file-invoice-dollar"></i> Payroll Summary</h3>
    </div>
    <div class="widget-content">
        <div class="d-flex align-items-center mb-4">
            <div class="circle-icon bg-gold mr-3"><i class="fas fa-hand-holding-usd text-black"></i></div>
            <div>
                <div class="text-dim">Current Draft Total</div>
                <div class="value" style="font-size: 1.5rem;">$<?= number_format($total_payroll, 2) ?></div>
            </div>
        </div>
        <div class="status-badge pending"><?= $pending_batches ?> Pending Batches</div>
    </div>
</div>
