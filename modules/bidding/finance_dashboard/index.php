<?php
// modules/bidding/finance_dashboard/index.php
require_once __DIR__ . '/../../../includes/AuthManager.php';
require_once __DIR__ . '/../../../includes/BidManager.php';

AuthManager::requireRole(['TENDER_FINANCE', 'FINANCE_HEAD', 'GM']);

$db = Database::getInstance();

// 1. Dashboard KPIs (Live)
$kpis = [
    'gm_approved' => $db->query("SELECT COUNT(*) FROM bids WHERE status = 'GM_PRE_APPROVED'")->fetchColumn(),
    'submitted' => $db->query("SELECT COUNT(*) FROM bids WHERE status = 'FINANCE_FINAL_REVIEW'")->fetchColumn(),
    'won' => $db->query("SELECT COUNT(*) FROM bids WHERE status = 'WON'")->fetchColumn(),
    'lost' => $db->query("SELECT COUNT(*) FROM bids WHERE status = 'LOSS'")->fetchColumn()
];

// 2. Fetch Bids for List
$bids = $db->query("SELECT b.*, fb.status as fb_status FROM bids b 
                     LEFT JOIN financial_bids fb ON b.id = fb.bid_id 
                     WHERE b.status IN ('GM_PRE_APPROVED', 'FINANCE_FINAL_REVIEW', 'WON', 'LOSS') 
                     ORDER BY b.updated_at DESC")->fetchAll();

?>

<div class="finance-dashboard">
    <div class="section-header mb-4">
        <h2 style="color:var(--gold);"><i class="fas fa-chart-line"></i> Financial Bid Command Center</h2>
        <p class="text-dim">Executive oversight of bid valuations and commercial decisions.</p>
    </div>

    <!-- KPIs -->
    <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:1.5rem; margin-bottom:2rem;">
        <div class="glass-card">
            <div class="text-dim" style="font-size:0.8rem; text-transform:uppercase;">GM Approved</div>
            <div style="font-size:2rem; font-weight:bold; color:var(--gold);"><?= $kpis['gm_approved'] ?></div>
        </div>
        <div class="glass-card">
            <div class="text-dim" style="font-size:0.8rem; text-transform:uppercase;">Submitted</div>
            <div style="font-size:2rem; font-weight:bold; color:#00d4ff;"><?= $kpis['submitted'] ?></div>
        </div>
        <div class="glass-card">
            <div class="text-dim" style="font-size:0.8rem; text-transform:uppercase;">Won Bids</div>
            <div style="font-size:2rem; font-weight:bold; color:#00ff64;"><?= $kpis['won'] ?></div>
        </div>
        <div class="glass-card">
            <div class="text-dim" style="font-size:0.8rem; text-transform:uppercase;">Lost Bids</div>
            <div style="font-size:2rem; font-weight:bold; color:#ff4444;"><?= $kpis['lost'] ?></div>
        </div>
    </div>

    <!-- Bids Table -->
    <div class="glass-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Tender #</th>
                    <th>Project / Client</th>
                    <th>Deadline</th>
                    <th>Bid Status</th>
                    <th>Financial Status</th>
                    <th style="text-align:right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bids as $b): ?>
                <tr>
                    <td style="font-family:monospace; color:var(--gold);"><?= $b['tender_no'] ?></td>
                    <td>
                        <strong><?= htmlspecialchars($b['title']) ?></strong><br>
                        <small class="text-dim"><?= htmlspecialchars($b['client_name']) ?></small>
                    </td>
                    <td><?= date('M d, Y', strtotime($b['deadline'])) ?></td>
                    <td>
                        <?php
                        $color = 'var(--gold)';
                        if ($b['status'] === 'WON') $color = '#00ff64';
                        if ($b['status'] === 'LOSS') $color = '#ff4444';
                        if ($b['status'] === 'FINANCE_FINAL_REVIEW') $color = '#00d4ff';
                        ?>
                        <span class="status-badge" style="background:<?= $color ?>; color:black; font-weight:bold;"><?= $b['status'] ?></span>
                    </td>
                    <td>
                        <span class="status-badge" style="background:rgba(255,255,255,0.1);"><?= strtoupper($b['fb_status'] ?? 'PENDING') ?></span>
                    </td>
                    <td style="text-align:right;">
                        <?php if ($b['status'] === 'GM_PRE_APPROVED'): ?>
                            <a href="main.php?module=bidding/finance_dashboard/create_financial_bid&id=<?= $b['id'] ?>" class="btn-primary-sm">Prepare Bid</a>
                        <?php else: ?>
                            <a href="main.php?module=bidding/finance_dashboard/view_financial_bid&id=<?= $b['id'] ?>" class="btn-secondary-sm">View Details</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
