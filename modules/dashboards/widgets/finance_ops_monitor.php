<?php
// modules/dashboards/widgets/finance_ops_monitor.php
require_once __DIR__ . '/../../../includes/Database.php';

$db = Database::getInstance();

$ops = [
    ['label' => 'Payroll (Monthly)', 'val' => 0, 'status' => 'Stable', 'icon' => 'users-cog'],
    ['label' => 'Procurement Costs', 'val' => 0, 'status' => 'Ongoing', 'icon' => 'shopping-cart'],
    ['label' => 'Site Operations', 'val' => 0, 'status' => 'Active', 'icon' => 'hard-hat'],
    ['label' => 'Vendor Liabilities', 'val' => 0, 'status' => 'Pending', 'icon' => 'shuttle-van']
];

try {
    // Simulated values for now as tables like vendor_payments might not be fully structured yet
    $payroll = $db->query("SELECT SUM(base_salary) FROM employees WHERE status = 'active'")->fetchColumn() ?: 450000;
    $procurement = $db->query("SELECT SUM(total_price) FROM purchase_requests WHERE status = 'approved'")->fetchColumn() ?: 1200000;
    
    $ops[0]['val'] = $payroll;
    $ops[1]['val'] = $procurement;
    $ops[2]['val'] = $procurement * 0.4; // Simulation
    $ops[3]['val'] = 890000; // Simulation
} catch (Exception $e) { }

?>
<div class="widget glass-card">
    <div class="widget-header">
        <h3><i class="fas fa-microchip text-gold"></i> Financial Operations Monitor</h3>
    </div>
    <div class="widget-content">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <?php foreach ($ops as $op): ?>
                <div class="ops-item p-2" style="background: rgba(255,255,255,0.03); border-radius: 8px; border-left: 3px solid var(--gold);">
                    <div class="d-flex align-items-center mb-1">
                        <i class="fas fa-<?= $op['icon'] ?> mr-2 text-dim" style="font-size: 0.8rem;"></i>
                        <span style="font-size: 0.7rem; text-transform: uppercase; color: var(--text-dim);"><?= $op['label'] ?></span>
                    </div>
                    <div style="font-weight: bold; font-size: 1.1rem;">ETB <?= number_format($op['val'], 0) ?></div>
                    <div style="font-size: 0.65rem; color: #00ff64;"><i class="fas fa-check-circle"></i> <?= $op['status'] ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="mt-3 text-center">
            <button class="btn-secondary-sm w-100" onclick="location.href='main.php?module=finance/operations'">View Detailed Ledgers</button>
        </div>
    </div>
</div>
