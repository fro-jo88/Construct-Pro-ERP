<?php
// modules/dashboards/widgets/project_list.php
require_once __DIR__ . '/../../../includes/Database.php';

$db = Database::getInstance();
$projects = [];

try {
    $projects = $db->query("SELECT p.*, b.client_name FROM projects p JOIN bids b ON p.tender_id = b.id ORDER BY p.created_at DESC LIMIT 5")->fetchAll();
} catch (Exception $e) { /* Ignore */ }

?>
<div class="widget glass-card">
    <div class="widget-header">
        <h3><i class="fas fa-project-diagram"></i> Recent Projects</h3>
    </div>
    <div class="widget-content">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Project</th>
                    <th>Client</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($projects)): ?>
                    <tr><td colspan="3" class="text-center">No active projects yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($projects as $p): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($p['project_name']) ?></strong><br><small><?= $p['project_code'] ?></small></td>
                            <td><?= htmlspecialchars($p['client_name']) ?></td>
                            <td><span class="status-badge <?= $p['status'] ?>"><?= strtoupper($p['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
