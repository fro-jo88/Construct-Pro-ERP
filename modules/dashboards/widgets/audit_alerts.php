<?php
// modules/dashboards/widgets/audit_alerts.php
require_once __DIR__ . '/../../../includes/Database.php';

$db = Database::getInstance();
$alerts = [];

try {
    // Detect Budget Overruns
    $overruns = $db->query("SELECT p.project_name, b.total_amount, SUM(e.amount) as actual 
                            FROM projects p 
                            JOIN budgets b ON p.id = b.project_id 
                            JOIN expenses e ON p.id = e.project_id 
                            GROUP BY p.id 
                            HAVING actual > b.total_amount")->fetchAll();
    
    foreach ($overruns as $o) {
        $alerts[] = [
            'type' => 'critical',
            'msg' => "Budget Overrun: " . $o['project_name'] . " exceeded by ETB " . number_format($o['actual'] - $o['total_amount'], 2),
            'icon' => 'exclamation-circle'
        ];
    }
    
    // Detect Spikes (e.g. any expense > 500k)
    $spikes = $db->query("SELECT p.project_name, e.amount, e.description FROM expenses e JOIN projects p ON e.project_id = p.id WHERE e.amount > 500000")->fetchAll();
    foreach ($spikes as $s) {
        $alerts[] = [
            'type' => 'warning',
            'msg' => "High Value Entry: ETB " . number_format($s['amount'], 2) . " on " . $s['project_name'],
            'icon' => 'search-dollar'
        ];
    }

} catch (Exception $e) { /* Ignore */ }

?>
<div class="widget glass-card">
    <div class="widget-header">
        <h3><i class="fas fa-bell text-gold"></i> Audit Alerts & Flags</h3>
    </div>
    <div class="widget-content">
        <?php if (empty($alerts)): ?>
            <div class="text-center p-4">
                <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                <p class="text-dim">No critical financial anomalies detected.</p>
            </div>
        <?php else: ?>
            <?php foreach ($alerts as $a): ?>
                <div class="alert-item d-flex align-items-start mb-3 p-2 rounded" style="background: rgba(255,255,255,0.02); border-left: 3px solid <?= $a['type'] === 'critical' ? '#ff4444' : '#ffaa00' ?>;">
                    <i class="fas fa-<?= $a['icon'] ?> mt-1 mr-3 text-<?= $a['type'] === 'critical' ? 'danger' : 'warning' ?>"></i>
                    <div style="font-size: 0.85rem;"><?= htmlspecialchars($a['msg']) ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
