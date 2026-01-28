<?php
// modules/dashboards/widgets/financial_audit_log.php
require_once __DIR__ . '/../../../includes/Database.php';

$db = Database::getInstance();
$logs = [];

try {
    $logs = $db->query("SELECT l.*, u.username 
                        FROM audit_logs l 
                        JOIN users u ON l.user_id = u.id 
                        WHERE l.table_affected IN ('budgets', 'expenses', 'financial_bids')
                        ORDER BY l.created_at DESC LIMIT 15")->fetchAll();
} catch (Exception $e) { /* Ignore */ }

?>
<div class="widget glass-card">
    <div class="widget-header">
        <h3><i class="fas fa-history text-gold"></i> Financial Audit Trail</h3>
    </div>
    <div class="widget-content">
        <div class="audit-timeline" style="max-height: 400px; overflow-y: auto; padding-right: 5px;">
            <?php if (empty($logs)): ?>
                <p class="text-dim small p-3 text-center">No system modifications logged.</p>
            <?php else: ?>
                <?php foreach ($logs as $l): ?>
                    <div class="log-entry mb-3 pb-2" style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="badge badge-outline-gold" style="font-size: 0.6rem;"><?= strtoupper($l['action']) ?></span>
                            <span class="text-dim" style="font-size: 0.65rem;"><?= date('M d, H:i', strtotime($l['created_at'])) ?></span>
                        </div>
                        <div style="font-size: 0.8rem; font-weight: bold; color: #fff;">
                            <?= htmlspecialchars($l['table_affected']) ?> #<?= $l['record_id'] ?>
                        </div>
                        <div style="font-size: 0.75rem; color: var(--text-dim);">
                            Changed by: <span class="text-white"><?= htmlspecialchars($l['username']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.badge-outline-gold {
    border: 1px solid var(--gold);
    color: var(--gold);
    background: transparent;
}
.audit-timeline::-webkit-scrollbar {
    width: 4px;
}
.audit-timeline::-webkit-scrollbar-thumb {
    background: rgba(255, 204, 0, 0.2);
    border-radius: 2px;
}
</style>
