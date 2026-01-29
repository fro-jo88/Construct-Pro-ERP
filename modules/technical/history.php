<?php
// modules/technical/history.php
// Technical Bid Review History for TECH_BID_MANAGER
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/Database.php';

AuthManager::requireRole(['TECH_BID_MANAGER', 'GM', 'SUPER_ADMIN']);
$db = Database::getInstance();

// Get all technical bids with history
$history = $db->query("
    SELECT b.*, 
           tb.compliance_score, 
           tb.status as tech_status, 
           tb.gm_comments,
           tb.created_at as tech_created,
           tb.updated_at as tech_updated,
           u.username as creator_name
    FROM bids b 
    LEFT JOIN technical_bids tb ON tb.bid_id = b.id
    LEFT JOIN users u ON b.created_by = u.id
    ORDER BY b.created_at DESC
")->fetchAll();

// Get activity logs related to technical bids
$logs = $db->query("
    SELECT * FROM hr_activity_logs 
    WHERE action_type LIKE '%tech%' OR action_type LIKE '%bid%'
    ORDER BY created_at DESC
    LIMIT 50
")->fetchAll();

// Status filter
$filterStatus = $_GET['status'] ?? 'all';
?>

<div class="tech-history-module">
    <div class="section-header mb-4" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2><i class="fas fa-history text-gold"></i> Review History</h2>
            <p class="text-dim">Complete audit trail of all technical bid activities</p>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <a href="?module=technical/history&status=all" class="btn-secondary-sm <?= $filterStatus === 'all' ? 'active' : '' ?>" style="text-decoration: none;">All</a>
            <a href="?module=technical/history&status=draft" class="btn-secondary-sm <?= $filterStatus === 'draft' ? 'active' : '' ?>" style="text-decoration: none;">Draft</a>
            <a href="?module=technical/history&status=submitted" class="btn-secondary-sm <?= $filterStatus === 'submitted' ? 'active' : '' ?>" style="text-decoration: none;">Submitted</a>
            <a href="?module=technical/history&status=approved" class="btn-secondary-sm <?= $filterStatus === 'approved' ? 'active' : '' ?>" style="text-decoration: none;">Approved</a>
        </div>
    </div>

    <!-- Summary Stats -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
        <?php
        $totalBids = count($history);
        $draftBids = count(array_filter($history, fn($b) => ($b['tech_status'] ?? 'draft') === 'draft'));
        $submittedBids = count(array_filter($history, fn($b) => ($b['tech_status'] ?? '') === 'submitted'));
        $approvedBids = count(array_filter($history, fn($b) => ($b['tech_status'] ?? '') === 'approved'));
        ?>
        <div class="glass-card" style="text-align: center; padding: 1.25rem;">
            <div style="font-size: 2rem; font-weight: bold; color: var(--gold);"><?= $totalBids ?></div>
            <div class="text-dim" style="font-size: 0.85rem;">Total Bids</div>
        </div>
        <div class="glass-card" style="text-align: center; padding: 1.25rem;">
            <div style="font-size: 2rem; font-weight: bold; color: #ccc;"><?= $draftBids ?></div>
            <div class="text-dim" style="font-size: 0.85rem;">In Draft</div>
        </div>
        <div class="glass-card" style="text-align: center; padding: 1.25rem;">
            <div style="font-size: 2rem; font-weight: bold; color: #0096ff;"><?= $submittedBids ?></div>
            <div class="text-dim" style="font-size: 0.85rem;">Submitted</div>
        </div>
        <div class="glass-card" style="text-align: center; padding: 1.25rem;">
            <div style="font-size: 2rem; font-weight: bold; color: #00ff64;"><?= $approvedBids ?></div>
            <div class="text-dim" style="font-size: 0.85rem;">Approved</div>
        </div>
    </div>

    <!-- History Table -->
    <div class="glass-card mb-4">
        <h3 class="mb-3"><i class="fas fa-table text-gold"></i> Bid Records</h3>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>Tender #</th>
                    <th>Project</th>
                    <th>Client</th>
                    <th>Compliance</th>
                    <th>Tech Status</th>
                    <th>Bid Status</th>
                    <th>Created</th>
                    <th>Updated</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $filtered = $history;
                if ($filterStatus !== 'all') {
                    $filtered = array_filter($history, fn($b) => ($b['tech_status'] ?? 'draft') === $filterStatus);
                }
                ?>
                <?php if (empty($filtered)): ?>
                    <tr><td colspan="8" style="text-align: center; padding: 2rem;" class="text-dim">No records found.</td></tr>
                <?php else: ?>
                    <?php foreach ($filtered as $bid): ?>
                        <tr>
                            <td style="font-family: monospace; color: var(--gold);"><?= htmlspecialchars($bid['tender_no']) ?></td>
                            <td>
                                <strong><?= htmlspecialchars(substr($bid['title'], 0, 25)) ?><?= strlen($bid['title']) > 25 ? '...' : '' ?></strong>
                            </td>
                            <td class="text-dim"><?= htmlspecialchars($bid['client_name']) ?></td>
                            <td>
                                <?php $score = $bid['compliance_score'] ?? 0; ?>
                                <span style="color: <?= $score >= 80 ? '#00ff64' : ($score >= 50 ? 'var(--gold)' : '#ff4444') ?>; font-weight: bold;">
                                    <?= $score ?>%
                                </span>
                            </td>
                            <td>
                                <?php 
                                $techStatus = $bid['tech_status'] ?? 'draft';
                                $statusColors = [
                                    'draft' => ['bg' => 'rgba(255,255,255,0.1)', 'color' => '#ccc'],
                                    'ready' => ['bg' => 'rgba(255,204,0,0.15)', 'color' => 'var(--gold)'],
                                    'submitted' => ['bg' => 'rgba(0,150,255,0.15)', 'color' => '#0096ff'],
                                    'approved' => ['bg' => 'rgba(0,255,100,0.15)', 'color' => '#00ff64'],
                                    'rejected' => ['bg' => 'rgba(255,68,68,0.15)', 'color' => '#ff4444'],
                                ];
                                $sc = $statusColors[$techStatus] ?? $statusColors['draft'];
                                ?>
                                <span style="background: <?= $sc['bg'] ?>; color: <?= $sc['color'] ?>; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.7rem; text-transform: uppercase;">
                                    <?= $techStatus ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge <?= $bid['status'] ?>"><?= $bid['status'] ?></span>
                            </td>
                            <td class="text-dim" style="font-size: 0.8rem;"><?= date('M d, Y', strtotime($bid['tech_created'] ?? $bid['created_at'])) ?></td>
                            <td class="text-dim" style="font-size: 0.8rem;"><?= $bid['tech_updated'] ? date('M d, Y', strtotime($bid['tech_updated'])) : '-' ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Activity Log -->
    <div class="glass-card">
        <h3 class="mb-3"><i class="fas fa-stream text-gold"></i> Activity Log</h3>
        
        <div style="max-height: 300px; overflow-y: auto;">
            <?php if (empty($logs)): ?>
                <p class="text-dim" style="text-align: center; padding: 2rem;">No activity recorded yet.</p>
            <?php else: ?>
                <?php foreach ($logs as $log): ?>
                    <div style="display: flex; gap: 1rem; padding: 0.75rem; border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <div style="width: 8px; height: 8px; background: var(--gold); border-radius: 50%; margin-top: 6px; flex-shrink: 0;"></div>
                        <div style="flex: 1;">
                            <div style="font-size: 0.85rem;"><?= htmlspecialchars($log['details']) ?></div>
                            <div class="text-dim" style="font-size: 0.75rem; margin-top: 0.25rem;">
                                <?= date('M d, Y H:i', strtotime($log['created_at'])) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.btn-secondary-sm.active { background: var(--gold); color: black; border-color: var(--gold); }
.status-badge { padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.7rem; font-weight: bold; text-transform: uppercase; }
.status-badge.DRAFT { background: rgba(255,255,255,0.1); color: #ccc; }
.status-badge.TECHNICAL_COMPLETED { background: rgba(255,204,0,0.2); color: var(--gold); }
.status-badge.WON { background: rgba(0,255,100,0.2); color: #00ff64; }
.status-badge.LOSS { background: rgba(255,68,68,0.2); color: #ff4444; }
</style>
