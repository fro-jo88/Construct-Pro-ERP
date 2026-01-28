<?php
// modules/gm/logs.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/GMManager.php';

AuthManager::requireRole('GM');

$db = Database::getInstance();

// Comprehensive Approval History
$approvals = $db->query("SELECT h.*, u.username as approver_name 
                        FROM approval_history h 
                        JOIN users u ON h.approver_id = u.id 
                        ORDER BY h.created_at DESC LIMIT 50")->fetchAll();

// General Activity Logs
$activity = GMManager::getSystemLogs(100);

?>

<div class="gm-logs">
    <div class="section-header mb-4">
        <h2><i class="fas fa-database"></i> System Integrity & Traceability Center</h2>
        <p class="text-dim">Comprehensive historical record of every critical decision and system event.</p>
    </div>

    <div style="display:grid; grid-template-columns: 1fr; gap: 1.5rem;">
        <!-- Approval History -->
        <section class="glass-card">
            <h3 style="color:var(--gold);"><i class="fas fa-file-signature"></i> Executive Decision History (Approvals)</h3>
            <table class="data-table mt-3">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Module</th>
                        <th>Ref #</th>
                        <th>Approver</th>
                        <th>Decision</th>
                        <th>Justification</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($approvals)): ?>
                        <tr><td colspan="6" class="text-center py-4">No approval history recorded.</td></tr>
                    <?php else: ?>
                        <?php foreach ($approvals as $a): ?>
                        <tr>
                            <td class="text-dim"><?= date('Y-m-d H:i', strtotime($a['created_at'])) ?></td>
                            <td><span style="font-size:0.75rem; color:var(--gold); font-weight:bold;"><?= $a['module'] ?></span></td>
                            <td>#<?= $a['reference_id'] ?></td>
                            <td><?= $a['approver_name'] ?></td>
                            <td><span class="status-badge" style="color:<?= $a['decision'] === 'approved' ? '#00ff64' : ($a['decision'] === 'rejected' ? '#ff4444' : '#ffcc00') ?>; background:transparent; border:1px solid currentColor;"><?= strtoupper($a['decision']) ?></span></td>
                            <td class="text-dim" style="font-size:0.8rem;"><?= htmlspecialchars($a['reason']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <!-- General Activity Stream -->
        <section class="glass-card">
            <h3 style="color:var(--gold);"><i class="fas fa-stream"></i> System-Wide Activity Stream</h3>
            <div style="max-height:500px; overflow-y:auto; padding-right:10px;">
                <table class="data-table mt-3">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>User</th>
                            <th>Operation</th>
                            <th>Detail Trace</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activity as $act): ?>
                        <tr>
                            <td class="text-dim"><?= date('H:i:s M d', strtotime($act['created_at'])) ?></td>
                            <td><strong><?= $act['username'] ?></strong></td>
                            <td><code><?= strtoupper($act['action_type']) ?></code></td>
                            <td style="font-size:0.8rem; color:var(--text-dim);"><?= htmlspecialchars($act['details']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3 text-center" style="color:var(--text-dim); font-size:0.7rem;">
                <i class="fas fa-info-circle"></i> Showing last 100 system events. Direct database modification is prohibited.
            </div>
        </section>
    </div>
</div>
