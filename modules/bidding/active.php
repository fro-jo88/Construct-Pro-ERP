<?php
// modules/bidding/active.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/BidManager.php';
require_once __DIR__ . '/../../includes/TenderManager.php';

AuthManager::requireRole(['TECH_BID_MANAGER', 'FINANCE_BID_MANAGER', 'GM']);

$tenders = TenderManager::getAllTenders(); // Get all active bids

?>

<div class="active-bids-module">
    <div class="section-header mb-4">
        <h2 style="color:var(--gold);"><i class="fas fa-gavel"></i> Ongoing Tenders & Bids</h2>
        <p class="text-dim">Real-time status tracking of all live enterprise bidding opportunities.</p>
    </div>

    <div class="glass-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Tender #</th>
                    <th>Project Name</th>
                    <th>Client</th>
                    <th>Deadline</th>
                    <th>Workflow Stage</th>
                    <th style="text-align:right;">Access</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tenders as $at): ?>
                <tr>
                    <td style="font-family:monospace; color:var(--gold);"><?= $at['tender_no'] ?></td>
                    <td><strong><?= htmlspecialchars($at['title']) ?></strong></td>
                    <td><?= htmlspecialchars($at['client_name']) ?></td>
                    <td><?= date('M d, Y', strtotime($at['deadline'])) ?></td>
                    <td>
                        <span class="status-badge" style="background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2);"><?= $at['status'] ?></span>
                    </td>
                    <td style="text-align:right;">
                        <div style="display:flex; gap:0.5rem; justify-content:flex-end;">
                            <a href="main.php?module=bidding/technical_dashboard&id=<?= $at['id'] ?>" class="btn-secondary-sm" title="Technical Workspace"><i class="fas fa-microchip"></i></a>
                            <a href="main.php?module=bidding/finance_dashboard&id=<?= $at['id'] ?>" class="btn-secondary-sm" title="Financial Workspace"><i class="fas fa-file-invoice-dollar"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
