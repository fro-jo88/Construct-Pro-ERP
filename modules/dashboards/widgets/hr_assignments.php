<?php
// modules/dashboards/widgets/hr_assignments.php
require_once __DIR__ . '/../../../includes/Database.php';

$db = Database::getInstance();
$unassigned_sites = [];
try {
    $unassigned_sites = $db->query("SELECT s.*, p.project_name FROM sites s 
                                   JOIN projects p ON s.project_id = p.id 
                                   WHERE s.foreman_id IS NULL OR s.foreman_id = 0 LIMIT 5")->fetchAll();
} catch (Exception $e) { }

?>
<div class="glass-card hr-assignments-widget">
    <div class="widget-header">
        <h3><i class="fas fa-users-cog text-gold"></i> Site Assignments</h3>
    </div>
    <div class="widget-content">
        <h4 class="section-title">Sites Needs Foreman</h4>
        <?php if (empty($unassigned_sites)): ?>
            <p class="text-dim small">All sites currently have assigned foreman.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Site</th>
                        <th>Project</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($unassigned_sites as $site): ?>
                        <tr>
                            <td><?= htmlspecialchars($site['site_name']) ?></td>
                            <td class="small text-dim"><?= htmlspecialchars($site['project_name']) ?></td>
                            <td>
                                <button class="btn-secondary-sm" onclick="location.href='main.php?module=hr/assignments&site_id=<?= $site['id'] ?>&role=foreman'">
                                    Assign
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <div style="margin-top:1rem; text-align:right;">
            <a href="main.php?module=hr/assignments" class="small text-gold" style="text-decoration:none;">View All Assignments <i class="fas fa-external-link-alt"></i></a>
        </div>
    </div>
</div>
