<?php
// modules/dashboards/widgets/schedule_overview.php
require_once __DIR__ . '/../../../includes/Database.php';

$db = Database::getInstance();
$type = $config['type'] ?? 'all';
$projects = [];

try {
    $projects = $db->query("SELECT p.*, (SELECT COUNT(*) FROM sites WHERE project_id = p.id) as site_count FROM projects p ORDER BY p.created_at DESC LIMIT 5")->fetchAll();
} catch (Exception $e) { /* Ignore */ }

?>
<div class="widget glass-card">
    <div class="widget-header">
        <h3><i class="fas fa-calendar-alt"></i> Planning & Schedules</h3>
    </div>
    <div class="widget-content">
        <?php if (empty($projects)): ?>
            <p class="text-dim">No schedules initialized.</p>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($projects as $p): ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span><?= htmlspecialchars($p['project_name']) ?></span>
                            <span class="text-gold"><?= $p['status'] ?></span>
                        </div>
                        <div class="progress-mini"><div class="progress-bar" style="width: 30%"></div></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
