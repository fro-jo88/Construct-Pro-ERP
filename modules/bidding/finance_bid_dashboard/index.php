<?php
// modules/bidding/finance_bid_dashboard/index.php

require_once __DIR__ . '/../../../includes/AuthManager.php';
require_once __DIR__ . '/../../../includes/Database.php';

AuthManager::requireRole(['FINANCE_BID_MANAGER', 'SYSTEM_ADMIN']);

$db = Database::getInstance();
$view = $_GET['view'] ?? 'overview';

// --- KPI QUERIES ---
// Total Active Bids (Not won/lost/dropped)
$countActiveBids = $db->query("SELECT COUNT(*) FROM bids WHERE status NOT IN ('WON', 'LOSS', 'DROPPED')")->fetchColumn();

// Financial Bids in Draft
$countDrafts = $db->query("SELECT COUNT(*) FROM financial_bids WHERE status = 'draft'")->fetchColumn();

// Awaiting Technical Confirmation
$countAwaitingTech = $db->query("SELECT COUNT(*) FROM financial_bids WHERE status = 'awaiting_tech'")->fetchColumn();

// Submitted to GM
$countSubmittedGM = $db->query("SELECT COUNT(*) FROM financial_bids WHERE status = 'submitted_to_gm'")->fetchColumn();

// Approved / Rejected counts
$countApproved = $db->query("SELECT COUNT(*) FROM bids WHERE status = 'approved'")->fetchColumn();
$countRejected = $db->query("SELECT COUNT(*) FROM bids WHERE status = 'rejected'")->fetchColumn();
$countDecided = $countApproved + $countRejected;
?>
<style>
    /* Premium Dashboard Styles */
    :root {
        --glass-bg: rgba(255, 255, 255, 0.03);
        --glass-border: rgba(255, 255, 255, 0.08);
        --gold: #ffcc00;
        --gold-glow: rgba(255, 204, 0, 0.3);
        --accent-blue: #0096ff;
        --accent-green: #00ff64;
    }

    .finance-bid-dashboard {
        padding: 1rem;
        background: radial-gradient(circle at top right, rgba(255, 204, 0, 0.05), transparent 400px);
        min-height: 100vh;
    }

    .dashboard-container { 
        display: flex; 
        gap: 24px; 
        margin-top: 1.5rem;
    }

    /* Modern Side Menu */
    .side-menu { 
        width: 280px; 
        background: rgba(15, 15, 15, 0.6);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border);
        border-radius: 20px; 
        padding: 24px 16px; 
        display: flex; 
        flex-direction: column; 
        gap: 8px; 
        height: fit-content;
        position: sticky;
        top: 20px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.4);
    }

    .menu-item { 
        padding: 14px 18px; 
        border-radius: 12px; 
        color: rgba(255,255,255,0.5); 
        text-decoration: none !important; 
        display: flex; 
        align-items: center; 
        gap: 16px; 
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
        font-weight: 500;
        font-size: 0.95rem;
    }

    .menu-item i { 
        font-size: 1.1rem;
        width: 24px;
        text-align: center;
        transition: transform 0.3s;
    }

    .menu-item:hover { 
        background: rgba(255, 255, 255, 0.05); 
        color: rgba(255,255,255,0.9); 
    }

    .menu-item:hover i {
        transform: scale(1.1) translateX(2px);
    }

    .menu-item.active { 
        background: linear-gradient(135deg, rgba(255, 204, 0, 0.15), rgba(255, 204, 0, 0.05));
        color: var(--gold); 
        border: 1px solid rgba(255, 204, 0, 0.2);
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    }

    .menu-item.active i {
        color: var(--gold);
        text-shadow: 0 0 10px var(--gold-glow);
    }

    /* Main Content Area */
    .main-content { 
        flex: 1; 
        overflow-y: auto; 
    }

    .header h1 {
        font-size: 2.25rem;
        font-weight: 800;
        letter-spacing: -1px;
        background: linear-gradient(to right, #fff, rgba(255,255,255,0.6));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 0.5rem;
    }

    /* Modern KPI Cards */
    .kpi-grid { 
        display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); 
        gap: 20px; 
        margin-bottom: 30px; 
    }

    .kpi-card { 
        background: rgba(20, 20, 20, 0.6);
        backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border);
        padding: 24px; 
        border-radius: 20px; 
        transition: all 0.4s;
        position: relative;
        overflow: hidden;
    }

    .kpi-card:hover {
        transform: translateY(-5px);
        border-color: rgba(255, 255, 255, 0.15);
        background: rgba(30, 30, 30, 0.7);
    }

    .kpi-card::after {
        content: '';
        position: absolute;
        top: 0; right: 0;
        width: 100px; height: 100px;
        background: radial-gradient(circle at top right, rgba(255,255,255,0.03), transparent);
        pointer-events: none;
    }

    .kpi-value { 
        font-size: 2.25rem; 
        font-weight: 800; 
        margin: 12px 0 4px 0; 
        line-height: 1;
        letter-spacing: -0.5px;
    }

    .kpi-label { 
        font-size: 0.7rem; 
        color: rgba(255,255,255,0.4); 
        text-transform: uppercase; 
        letter-spacing: 1.5px; 
        font-weight: 700;
    }

    .kpi-footer {
        font-size: 0.75rem;
        color: rgba(255,255,255,0.3);
        margin-top: 12px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Status Colors & Badges */
    .text-gold { color: var(--gold); }
    .text-blue { color: var(--accent-blue); }
    .text-purple { color: #9b51e0; }
    .text-green { color: var(--accent-green); }
    .text-red { color: #ff4444; }

    .badge-premium {
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Table Styles */
    .premium-card {
        background: rgba(15, 15, 15, 0.4);
        backdrop-filter: blur(15px);
        border: 1px solid var(--glass-border);
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.2);
    }

    .premium-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 8px;
    }

    .premium-table th {
        color: rgba(255,255,255,0.4);
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 0 16px 12px 16px;
    }

    .premium-table tr td {
        background: rgba(255,255,255,0.02);
        padding: 16px;
        transition: all 0.3s;
    }

    .premium-table tr td:first-child { border-radius: 12px 0 0 12px; }
    .premium-table tr td:last-child { border-radius: 0 12px 12px 0; }

    .premium-table tr:hover td {
        background: rgba(255,255,255,0.05);
        transform: scale(1.002);
    }

    /* Scrollbar */
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
    ::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }
</style>

<div class="finance-bid-dashboard">
    <div class="header">
        <h1>Finance Bid Manager <span class="text-gold">Command Center</span></h1>
        <p class="text-secondary" style="font-size: 1.1rem;">Precision financial modeling & bid coordination hub.</p>
    </div>

    <div class="dashboard-container" style="display: block;">
        <!-- MAIN CONTENT AREA -->
        <div class="main-content">
            <?php if ($view === 'overview'): ?>
                <!-- KPI GRID -->
                <div class="kpi-grid">
                    <div class="kpi-card">
                        <div class="kpi-label">Active Bids</div>
                        <div class="kpi-value"><?= $countActiveBids ?></div>
                        <div class="kpi-footer"><i class="fas fa-circle text-green" style="font-size: 6px;"></i> Live in Pipeline</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-label">Work in Progress</div>
                        <div class="kpi-value text-gold"><?= $countDrafts ?></div>
                        <div class="kpi-footer"><i class="fas fa-pen-fancy"></i> Financial Drafts</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-label">Awaiting Tech</div>
                        <div class="kpi-value text-blue"><?= $countAwaitingTech ?></div>
                        <div class="kpi-footer"><i class="fas fa-sync-alt spin"></i> Dependency Pending</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-label">GM Review</div>
                        <div class="kpi-value text-purple"><?= $countSubmittedGM ?></div>
                        <div class="kpi-footer"><i class="fas fa-gavel"></i> Awaiting Decision</div>
                    </div>
                </div>

                <!-- RECENT ACTIVITY -->
                <div class="premium-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <h3 style="margin: 0; font-size: 1.25rem;">Recent Financial Engagements</h3>
                        <div style="background: rgba(255,255,255,0.05); padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; color: var(--gold);">
                            <i class="fas fa-clock"></i> Live Updates
                        </div>
                    </div>
                    
                    <table class="premium-table">
                        <thead>
                            <tr>
                                <th>Ref #</th>
                                <th>Project Spec</th>
                                <th>Workflow Stage</th>
                                <th>Timeline</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Real Data Fetch -->
                            <?php
                            $recentBids = $db->query("
                                SELECT b.*, fb.status as fin_status 
                                FROM bids b 
                                LEFT JOIN financial_bids fb ON b.id = fb.bid_id 
                                WHERE b.status NOT IN ('WON', 'LOSS') 
                                ORDER BY b.created_at DESC LIMIT 5
                            ")->fetchAll();
                            
                            if (empty($recentBids)): ?>
                                <tr><td colspan="5" style="text-align: center; color: rgba(255,255,255,0.2); padding: 40px;">No active bid assignments found.</td></tr>
                            <?php else: 
                                foreach ($recentBids as $rb): 
                                    $finStatus = $rb['fin_status'] ?: 'not_started';
                                ?>
                                <tr>
                                    <td style="font-family: monospace; color: var(--gold); font-weight: 700;"><?= $rb['tender_no'] ?></td>
                                    <td>
                                        <div style="font-weight: 600; color: #fff;"><?= htmlspecialchars($rb['title']) ?></div>
                                        <div style="font-size: 0.75rem; color: rgba(255,255,255,0.3);"><?= htmlspecialchars($rb['client_name']) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge-premium" style="background: rgba(255,255,255,0.05); color: #fff; border: 1px solid rgba(255,255,255,0.1);">
                                            <?= str_replace('_', ' ', strtoupper($rb['status'])) ?>
                                        </span>
                                    </td>
                                    <td style="font-size: 0.85rem; color: rgba(255,255,255,0.5);">
                                        <?= date('M d, Y', strtotime($rb['created_at'])) ?>
                                    </td>
                                    <td style="text-align: right; white-space: nowrap;">
                                        <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                            <a href="?module=bidding/finance_bid_dashboard/index&view=preparation&id=<?= $rb['id'] ?>" class="btn-primary-sm" style="text-decoration: none; padding: 6px 16px; font-size: 0.75rem; background: rgba(255, 255, 255, 0.05); color: #fff; border: 1px solid rgba(255,255,255,0.1);">
                                                <i class="fas fa-file-invoice-dollar me-1"></i> Create Financial Document
                                            </a>
                                            <a href="?module=bidding/finance_bid_dashboard/index&view=coordination&id=<?= $rb['id'] ?>" class="btn-primary-sm" style="text-decoration: none; padding: 6px 16px; font-size: 0.75rem;">Manage</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($view === 'coordination'): ?>
                <?php include __DIR__ . '/coordination.php'; ?>
            
            <?php elseif ($view === 'preparation'): ?>
                <?php include __DIR__ . '/preparation.php'; ?>

            <?php elseif ($view === 'verification'): ?>
                <?php include __DIR__ . '/verification.php'; ?>

            <?php elseif ($view === 'submission'): ?>
                <?php include __DIR__ . '/submission.php'; ?>

            <?php elseif ($view === 'history'): ?>
                <?php include __DIR__ . '/history.php'; ?>
            
            <?php endif; ?>
        </div>
    </div>
</div>

