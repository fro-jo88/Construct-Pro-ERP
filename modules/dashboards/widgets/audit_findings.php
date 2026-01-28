<?php
// modules/dashboards/widgets/audit_findings.php
require_once __DIR__ . '/../../../includes/Database.php';

$db = Database::getInstance();
$logs = [];

try {
    $logs = $db->query("SELECT * FROM hr_activity_logs ORDER BY created_at DESC LIMIT 8")->fetchAll();
} catch (Exception $e) { /* Ignore */ }

?>
<div class="widget glass-card">
    <div class="widget-header">
        <h3><i class="fas fa-history"></i> Recent Activity Logs</h3>
    </div>
    <div class="widget-content">
        <div class="activity-feed">
            <?php if (empty($logs)): ?>
                <p class="text-dim">No historical logs found.</p>
            <?php else: ?>
                <?php foreach ($logs as $log): ?>
                    <div class="feed-item mb-2" style="font-size: 0.85rem; padding-bottom: 0.5rem; border-bottom: 1px solid rgba(255,255,255,0.03);">
                        <span class="text-gold" style="font-family: monospace;"><?= date('H:i', strtotime($log['created_at'])) ?>:</span>
                        <span class="ml-1"><?= htmlspecialchars($log['action_type']) ?></span>
                        <span class="text-dim ml-1"><?= htmlspecialchars(substr($log['details'] ?? '', 0, 50)) ?>...</span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
