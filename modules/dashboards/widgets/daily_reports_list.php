<?php
// modules/dashboards/widgets/daily_reports_list.php
require_once __DIR__ . '/../../../includes/Database.php';

$db = Database::getInstance();
$scope = $config['scope'] ?? 'global';
$reports = [];

try {
    if ($scope === 'site' && isset($_SESSION['site_id'])) {
        $stmt = $db->prepare("SELECT r.*, s.site_name FROM daily_site_reports r JOIN sites s ON r.site_id = s.id WHERE r.site_id = ? ORDER BY r.report_date DESC LIMIT 5");
        $stmt->execute([$_SESSION['site_id']]);
        $reports = $stmt->fetchAll();
    } else {
        $reports = $db->query("SELECT r.*, s.site_name FROM daily_site_reports r JOIN sites s ON r.site_id = s.id ORDER BY r.report_date DESC LIMIT 5")->fetchAll();
    }
} catch (Exception $e) { /* Table may be empty or missing */ }

?>
<div class="widget glass-card">
    <div class="widget-header">
        <h3><i class="fas fa-file-alt"></i> Daily Site Reports</h3>
    </div>
    <div class="widget-content">
        <?php if (empty($reports)): ?>
            <p class="text-dim">No recent reports submitted.</p>
        <?php else: ?>
            <div class="timeline">
                <?php foreach ($reports as $r): ?>
                    <div class="timeline-item mb-2" style="border-left: 2px solid var(--gold); padding-left: 1rem; position: relative;">
                        <div style="font-weight: bold;"><?= $r['site_name'] ?></div>
                        <div style="font-size: 0.8rem; color: var(--text-dim);"><?= date('M d, Y', strtotime($r['report_date'])) ?></div>
                        <div style="font-size: 0.8rem;"><?= substr(htmlspecialchars($r['work_summary'] ?? ''), 0, 80) ?>...</div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
