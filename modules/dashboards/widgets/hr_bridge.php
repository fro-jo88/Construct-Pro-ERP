<?php
// modules/dashboards/widgets/hr_bridge.php
require_once __DIR__ . '/../../../includes/Database.php';

$db = Database::getInstance();
$pending_forwarding = [];
try {
    $pending_forwarding = $db->query("SELECT mr.*, s.site_name, u.username FROM material_requests mr 
                                     JOIN sites s ON mr.site_id = s.id 
                                     JOIN users u ON mr.requested_by = u.id 
                                     WHERE mr.hr_review_status = 'pending' LIMIT 5")->fetchAll();
} catch (Exception $e) { }

?>
<div class="glass-card hr-bridge-widget">
    <div class="widget-header">
        <h3><i class="fas fa-network-wired text-gold"></i> Bridge: Site & Store</h3>
    </div>
    <div class="widget-content">
        <div class="bridge-section">
            <h4 class="section-title">Forwarding Queue (Materials)</h4>
            <?php if (empty($pending_forwarding)): ?>
                <p class="text-dim small">No pending materials to forward.</p>
            <?php else: ?>
                <ul class="activity-feed">
                    <?php foreach ($pending_forwarding as $mr): ?>
                        <li class="feed-item">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <div>
                                    <strong><?= $mr['site_name'] ?></strong>
                                    <div class="small text-dim">Req by: <?= $mr['username'] ?></div>
                                </div>
                                <button class="btn-primary-sm" onclick="location.href='main.php?module=hr/materials&action=forward&id=<?= $mr['id'] ?>'">
                                    Forward <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        
        <div class="bridge-section" style="margin-top:1.5rem;">
            <h4 class="section-title">Planning Handovers</h4>
            <div class="empty-state">
                <i class="fas fa-comments text-dim"></i>
                <p class="small text-dim">No active planning messages.</p>
            </div>
        </div>
    </div>
</div>

<style>
.section-title {
    font-size: 0.75rem;
    text-transform: uppercase;
    color: var(--gold);
    margin-bottom: 0.8rem;
    border-bottom: 1px solid rgba(255, 204, 0, 0.1);
    padding-bottom: 5px;
}
.small { font-size: 0.75rem; }
.empty-state {
    text-align: center;
    padding: 10px;
    background: rgba(0,0,0,0.1);
    border-radius: 8px;
}
</style>
