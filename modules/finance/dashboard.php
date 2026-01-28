<?php
// modules/finance/dashboard.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/Database.php';

AuthManager::requireRole(['FINANCE_HEAD', 'FINANCE_TEAM', 'GM']);
$db = Database::getInstance();

// Quick Stats
$count_pending = $db->query("SELECT COUNT(*) FROM bids WHERE status = 'GM_PRE_APPROVED'")->fetchColumn();
$count_review = $db->query("SELECT COUNT(*) FROM bids WHERE status = 'FINANCE_FINAL_REVIEW'")->fetchColumn();
?>

<div class="finance-overview">
    <div class="section-header mb-4">
        <h2><i class="fas fa-wallet text-gold"></i> Finance Department</h2>
    </div>

    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
        <!-- Card 1: Bidding -->
        <div class="glass-card hover-glow" onclick="location.href='main.php?module=bidding/financial_dashboard'" style="cursor:pointer; position:relative; overflow:hidden;">
            <div style="font-size: 3rem; position:absolute; right:-20px; top:-10px; opacity:0.1; color:var(--gold);"><i class="fas fa-file-contract"></i></div>
            <h3 class="text-gold mb-2">Financial Bids</h3>
            <p class="text-dim mb-4">Pricing & Profit Analysis</p>
            <div style="display:flex; justify-content:space-between;">
                <div><strong class="text-white text-lg"><?= $count_pending ?></strong><br><small>Pending</small></div>
                <div class="text-right"><strong class="text-white text-lg"><?= $count_review ?></strong><br><small>In Review</small></div>
            </div>
        </div>

        <!-- Card 2: Budgets (Placeholder) -->
        <div class="glass-card" style="opacity:0.7;">
            <h3 class="mb-2">Project Budgets</h3>
            <p class="text-dim">Cost Control & Allocation</p>
            <div class="mt-3"><span class="status-badge">Module Inactive</span></div>
        </div>

        <!-- Card 3: Expenses (Placeholder) -->
        <div class="glass-card" style="opacity:0.7;">
            <h3 class="mb-2">Expense Claims</h3>
            <p class="text-dim">Reimbursements & Petty Cash</p>
            <div class="mt-3"><span class="status-badge">Module Inactive</span></div>
        </div>
    </div>
</div>

<style>
.hover-glow:hover {
    border-color: var(--gold);
    box-shadow: 0 0 20px rgba(255,204,0,0.1);
    transform: translateY(-2px);
    transition: all 0.3s;
}
</style>
