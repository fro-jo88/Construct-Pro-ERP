<?php
// modules/dashboards/widgets/bid_pipeline.php
require_once __DIR__ . '/../../../includes/Database.php';

$db = Database::getInstance();
$type = $config['type'] ?? 'all';
$bids = [];

try {
    $sql = "SELECT b.*, u.username as creator FROM bids b JOIN users u ON b.created_by = u.id";
    if ($type === 'financial_pending') {
        $sql .= " WHERE b.status IN ('TECHNICAL_COMPLETED', 'GM_PRE_APPROVED')";
    } elseif ($type === 'technical') {
        $sql .= " WHERE b.status = 'DRAFT'";
    } elseif ($type === 'financial_drafts') {
        $sql .= " WHERE b.status = 'TECHNICAL_COMPLETED'";
    } elseif ($type === 'gm_review') {
        $sql .= " WHERE b.status IN ('TECHNICAL_COMPLETED', 'FINANCIAL_COMPLETED', 'FINANCE_FINAL_REVIEW')";
    }
    $sql .= " ORDER BY b.created_at DESC LIMIT 5";
    $bids = $db->query($sql)->fetchAll();
} catch (Exception $e) { /* Ignore */ }

?>
<div class="widget glass-card">
    <div class="widget-header">
        <h3><i class="fas fa-file-invoice"></i> Bid Pipeline <?= $type !== 'all' ? '('.ucwords(str_replace('_', ' ', $type)).')' : '' ?></h3>
    </div>
    <div class="widget-content">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Tender #</th>
                    <th>Project</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($bids)): ?>
                    <tr><td colspan="4" class="text-center">No bids in this stage.</td></tr>
                <?php else: ?>
                    <?php foreach ($bids as $b): ?>
                        <tr>
                            <td><?= $b['tender_no'] ?></td>
                            <td><?= htmlspecialchars($b['title']) ?></td>
                            <td><span class="status-badge status-<?= strtolower($b['status']) ?>"><?= $b['status'] ?></span></td>
                            <td>
                                <?php if ($b['status'] === 'WON' && ($config['role_code'] ?? '') === 'HR_MANAGER'): ?>
                                    <button class="btn-primary-sm" onclick="location.href='main.php?module=hr/sites&action=new&bid_id=<?= $b['id'] ?>'" title="Initialize Site">
                                        <i class="fas fa-building"></i> Create Site
                                    </button>
                                <?php elseif (($config['role_code'] ?? '') === 'GM' && !in_array($b['status'], ['WON', 'LOSS'])): ?>
                                    <button class="btn-secondary-sm" onclick="location.href='main.php?module=bidding/gm_review&id=<?= $b['id'] ?>'" title="Review Bid">
                                        <i class="fas fa-id-card"></i> Review
                                    </button>
                                <?php else: ?>
                                    <span class="text-dim">--</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
