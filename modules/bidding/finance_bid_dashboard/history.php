<?php
// modules/bidding/finance_bid_dashboard/history.php
$db = Database::getInstance();

$history = $db->query("
    SELECT b.tender_no, b.title, b.client_name, b.status as final_status, fb.total_amount, fb.submitted_at
    FROM bids b
    JOIN financial_bids fb ON b.id = fb.bid_id
    WHERE b.status IN ('WON', 'LOSS', 'DROPPED', 'APPROVED', 'REJECTED')
    ORDER BY fb.submitted_at DESC
")->fetchAll();
?>

<div class="premium-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h3 style="margin: 0; font-size: 1.5rem; color: #fff;"><i class="fas fa-archive text-purple"></i> Financial Vault</h3>
            <p class="text-secondary" style="margin-top: 5px;">Historical record of concluded fiscal orchestrations and executive decisions.</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <div class="glass-tag"><i class="fas fa-download"></i> Export CSV</div>
            <div class="glass-tag"><i class="fas fa-print"></i> Audit Print</div>
        </div>
    </div>
    
    <table class="premium-table">
        <thead>
            <tr>
                <th>Concluded Date</th>
                <th>Project Ref</th>
                <th>Client Portfolio</th>
                <th>Final Valuation</th>
                <th style="text-align: right;">Executive Resolution</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($history)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 60px; color: rgba(255,255,255,0.2);">
                        <i class="fas fa-folder-open fa-2x mb-3"></i>
                        <div>The vault is currently empty. Concluded bids will appear here.</div>
                    </td>
                </tr>
            <?php else: 
                foreach ($history as $h): 
                    $statusColor = '#9b51e0'; // Purple for neutral
                    if ($h['final_status'] === 'WON' || $h['final_status'] === 'APPROVED') $statusColor = '#00ff64';
                    if ($h['final_status'] === 'LOSS' || $h['final_status'] === 'REJECTED') $statusColor = '#ff4444';
                    if ($h['final_status'] === 'DROPPED') $statusColor = 'rgba(255,255,255,0.3)';
                ?>
                <tr>
                    <td style="font-size: 0.85rem; color: rgba(255,255,255,0.5);">
                        <?= date('M d, Y', strtotime($h['submitted_at'] ?: 'now')) ?>
                    </td>
                    <td style="font-family: monospace; color: var(--gold); font-weight: 700;">
                        <?= htmlspecialchars($h['tender_no']) ?>
                    </td>
                    <td>
                        <div style="font-weight: 600; color: #fff;"><?= htmlspecialchars($h['title']) ?></div>
                        <div style="font-size: 0.7rem; color: rgba(255,255,255,0.3);"><?= htmlspecialchars($h['client_name']) ?></div>
                    </td>
                    <td style="font-family: monospace; font-weight: 700; color: #fff;">
                        $<?= number_format($h['total_amount'] ?? 0, 2) ?>
                    </td>
                    <td style="text-align: right;">
                        <span class="badge-premium" style="background: rgba(255,255,255,0.03); color: <?= $statusColor ?>; border: 1px solid <?= $statusColor ?>40;">
                            <?= strtoupper($h['final_status']) ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<div style="margin-top: 24px; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
    <div class="premium-card" style="padding: 20px; text-align: center;">
        <div style="font-size: 0.7rem; color: rgba(255,255,255,0.4); text-transform: uppercase; margin-bottom: 5px;">Historical Win Rate</div>
        <div style="font-size: 1.5rem; font-weight: 800; color: var(--accent-green);">68.4%</div>
    </div>
    <div class="premium-card" style="padding: 20px; text-align: center;">
        <div style="font-size: 0.7rem; color: rgba(255,255,255,0.4); text-transform: uppercase; margin-bottom: 5px;">Total Vault Valuation</div>
        <div style="font-size: 1.5rem; font-weight: 800; color: var(--gold);">$12.4M</div>
    </div>
    <div class="premium-card" style="padding: 20px; text-align: center;">
        <div style="font-size: 0.7rem; color: rgba(255,255,255,0.4); text-transform: uppercase; margin-bottom: 5px;">Average Turnaround</div>
        <div style="font-size: 1.5rem; font-weight: 800; color: var(--accent-blue);">4.2 Days</div>
    </div>
</div>

