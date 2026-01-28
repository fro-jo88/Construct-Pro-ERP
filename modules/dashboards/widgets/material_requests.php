<?php
// modules/dashboards/widgets/material_requests.php
require_once __DIR__ . '/../../../includes/Database.php';

$db = Database::getInstance();
$scope = $config['scope'] ?? 'global';
$items = [];

try {
    $sql = "SELECT mr.*, s.site_name, u.username FROM material_requests mr 
            JOIN sites s ON mr.site_id = s.id 
            JOIN users u ON mr.requested_by = u.id ";
    if ($scope === 'site' && isset($_SESSION['site_id'])) {
        $stmt = $db->prepare($sql . " WHERE mr.site_id = ? ORDER BY mr.created_at DESC LIMIT 5");
        $stmt->execute([$_SESSION['site_id']]);
        $items = $stmt->fetchAll();
    } else {
        $items = $db->query($sql . " ORDER BY mr.created_at DESC LIMIT 5")->fetchAll();
    }
} catch (Exception $e) { /* Ignore */ }

?>
<div class="widget glass-card">
    <div class="widget-header">
        <h3><i class="fas fa-truck-loading"></i> Material Requests</h3>
    </div>
    <div class="widget-content">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Site</th>
                    <th>Requester</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                    <tr><td colspan="3" class="text-center">No pending material requests.</td></tr>
                <?php else: ?>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['site_name']) ?></td>
                            <td><?= htmlspecialchars($item['username']) ?></td>
                            <td><span class="status-badge <?= $item['gm_approval_status'] ?>"><?= strtoupper($item['gm_approval_status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
