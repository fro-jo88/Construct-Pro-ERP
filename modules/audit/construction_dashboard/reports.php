<?php
// modules/audit/construction_dashboard/reports.php

$db = Database::getInstance();
$user_id = $_SESSION['user_id'];

// Fetch all audit reports for this auditor
$reports = $db->query("SELECT ar.*, s.site_name, p.project_name, u.username as reviewer_name
                       FROM audit_reports ar
                       LEFT JOIN sites s ON ar.site_id = s.id
                       LEFT JOIN projects p ON ar.project_id = p.id
                       LEFT JOIN users u ON ar.reviewed_by = u.id
                       WHERE ar.auditor_id = ?
                       ORDER BY ar.created_at DESC",
                      [$user_id])->fetchAll();

?>

<div class="glass-panel">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="fas fa-file-alt text-info me-2"></i> Audit Reports Archive</h4>
        <div class="text-secondary text-sm">Historical Submissions & Status</div>
    </div>

    <div class="table-responsive">
        <table class="table table-custom text-white">
            <thead class="text-secondary text-xs text-uppercase fw-bold">
                <tr>
                    <th>REPORT ID</th>
                    <th>PERIOD</th>
                    <th>SITE / PROJECT</th>
                    <th>CATEGORY</th>
                    <th class="text-center">FINDINGS</th>
                    <th class="text-center">CRITICAL</th>
                    <th class="text-center">STATUS</th>
                    <th class="text-end">ACTION</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php foreach ($reports as $report): ?>
                <tr class="align-middle">
                    <td class="font-monospace text-warning">#AR-<?= str_pad($report['id'], 4, '0', STR_PAD_LEFT) ?></td>
                    <td class="text-xs">
                        <?= date('M d', strtotime($report['report_period_start'])) ?> - 
                        <?= date('M d, Y', strtotime($report['report_period_end'])) ?>
                    </td>
                    <td>
                        <div class="fw-medium"><?= htmlspecialchars($report['site_name'] ?? 'N/A') ?></div>
                        <div class="text-xs text-secondary"><?= htmlspecialchars($report['project_name'] ?? '') ?></div>
                    </td>
                    <td class="text-xs"><?= htmlspecialchars($report['work_category'] ?? 'General') ?></td>
                    <td class="text-center">
                        <span class="badge bg-secondary"><?= $report['total_findings'] ?></span>
                    </td>
                    <td class="text-center">
                        <?php if ($report['critical_findings'] > 0): ?>
                            <span class="badge bg-danger"><?= $report['critical_findings'] ?></span>
                        <?php else: ?>
                            <span class="text-secondary">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php
                        $statusClass = 'flag-normal';
                        $statusText = strtoupper($report['status']);
                        
                        if ($report['status'] === 'draft') {
                            $statusClass = 'flag-high';
                        } elseif ($report['status'] === 'reviewed') {
                            $statusClass = 'flag-normal';
                        }
                        ?>
                        <span class="status-badge <?= $statusClass ?>"><?= $statusText ?></span>
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-info rounded-start" onclick="viewReport(<?= $report['id'] ?>)">
                                <i class="fas fa-eye"></i>
                            </button>
                            <?php if ($report['status'] === 'submitted'): ?>
                                <button class="btn btn-outline-success rounded-end" disabled title="Locked after submission">
                                    <i class="fas fa-lock"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($reports)): ?>
                    <tr><td colspan="8" class="text-center py-5 text-secondary">No audit reports submitted yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (!empty($reports)): ?>
    <div class="mt-4 p-4 bg-dark bg-opacity-30 rounded-3">
        <div class="row g-3 text-center">
            <div class="col-md-3">
                <div class="text-xs text-secondary mb-1">TOTAL REPORTS</div>
                <div class="h3 fw-bold text-white mb-0"><?= count($reports) ?></div>
            </div>
            <div class="col-md-3">
                <div class="text-xs text-secondary mb-1">SUBMITTED</div>
                <div class="h3 fw-bold text-success mb-0">
                    <?= count(array_filter($reports, fn($r) => $r['status'] === 'submitted')) ?>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-xs text-secondary mb-1">REVIEWED</div>
                <div class="h3 fw-bold text-info mb-0">
                    <?= count(array_filter($reports, fn($r) => $r['status'] === 'reviewed')) ?>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-xs text-secondary mb-1">DRAFT</div>
                <div class="h3 fw-bold text-warning mb-0">
                    <?= count(array_filter($reports, fn($r) => $r['status'] === 'draft')) ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function viewReport(reportId) {
    alert('View Report #' + reportId + '\n\nThis will open a detailed view of the audit report with all findings, recommendations, and review status.');
}
</script>
