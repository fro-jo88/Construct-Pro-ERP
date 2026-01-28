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
                    <div class="timeline-item mb-3" style="border-left: 2px solid var(--gold); padding-left: 1rem; position: relative;">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div style="font-weight: bold; color: var(--gold);"><?= $r['site_name'] ?></div>
                                <div style="font-size: 0.8rem; color: var(--text-dim);">
                                    <?= date('M d', strtotime($r['report_date'])) ?> &bull; Progress: <?= $r['progress_percent'] ?? 0 ?>%
                                </div>
                            </div>
                            <?php if (($config['role_code'] ?? '') === 'GM'): ?>
                                <button class="btn-primary-sm" style="font-size: 0.6rem; padding: 2px 6px;" onclick="location.href='main.php?module=gm/site_reports&action=flag&id=<?= $r['id'] ?>'" title="Forward to Audit">
                                    <i class="fas fa-shield-alt"></i> Flag
                                </button>
                            <?php endif; ?>
                        </div>
                        <div style="font-size: 0.8rem; margin-top: 4px;"><?= substr(htmlspecialchars($r['work_summary'] ?? $r['actual_work'] ?? ''), 0, 100) ?>...</div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
