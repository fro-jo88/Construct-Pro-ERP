<?php
// modules/dashboards/widgets/audit_queue.php
require_once __DIR__ . '/../../../includes/Database.php';

$db = Database::getInstance();
$sites = [];

try {
    $sites = $db->query("SELECT s.*, p.project_name FROM sites s JOIN projects p ON s.project_id = p.id LIMIT 5")->fetchAll();
} catch (Exception $e) { /* Ignore */ }

?>
<div class="widget glass-card">
    <div class="widget-header">
        <h3><i class="fas fa-microscope"></i> Sites for Audit</h3>
    </div>
    <div class="widget-content">
        <div class="list-group">
            <?php if (empty($sites)): ?>
                <p class="text-dim">No sites available for auditing.</p>
            <?php else: ?>
                <?php foreach ($sites as $site): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2 p-2" style="background: rgba(255,255,255,0.02); border-radius: 8px;">
                        <div>
                            <strong><?= htmlspecialchars($site['site_name']) ?></strong><br>
                            <small class="text-dim"><?= htmlspecialchars($site['project_name']) ?></small>
                        </div>
                        <button class="btn-primary-sm">Start Audit</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
