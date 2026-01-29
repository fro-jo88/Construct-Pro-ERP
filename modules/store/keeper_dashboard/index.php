<?php
// modules/store/keeper_dashboard/index.php

require_once __DIR__ . '/../../../includes/AuthManager.php';
require_once __DIR__ . '/../../../includes/Database.php';

AuthManager::requireRole(['STORE_KEEPER', 'SYSTEM_ADMIN']);

$db = Database::getInstance();
$user_id = $_SESSION['user_id'];
$view = $_GET['view'] ?? 'overview';

// --- STORE CONTEXT ---
// Get store assigned to this keeper
$store = $db->query("SELECT * FROM stores WHERE keeper_id = ?", [$user_id])->fetch();
$store_id = $store['id'] ?? null;

// --- KPI DATA (Assigned Store Only) ---
$totalItems = 0;
$availableQty = 0;
$pendingIssues = 0;
$completedToday = 0;

if ($store_id) {
    // 1. Total unique items in this store
    $totalItems = $db->query("SELECT COUNT(*) FROM stock_levels WHERE store_id = ? AND quantity > 0", [$store_id])->fetchColumn();
    
    // 2. Sum of all quantities
    $availableQty = $db->query("SELECT SUM(quantity) FROM stock_levels WHERE store_id = ?", [$store_id])->fetchColumn() ?: 0;
    
    // 3. Pending issues (Store Manager Approved, but not executed)
    $pendingIssues = $db->query("SELECT COUNT(*) FROM material_requests 
                                 WHERE store_manager_approval = 'approved' 
                                 AND fulfilling_store_id = ? 
                                 AND (status != 'delivered' AND status != 'issued')", [$store_id])->fetchColumn();
    
    // 4. Completed Issues Today
    $today = date('Y-m-d');
    $completedToday = $db->query("SELECT COUNT(*) FROM inventory_movements 
                                  WHERE store_id = ? AND movement_type = 'issue' 
                                  AND DATE(created_at) = ?", [$store_id, $today])->fetchColumn();
}

?>

<style>
    :root { --keeper-primary: #10b981; --keeper-bg: #0f172a; --keeper-card: rgba(16, 185, 129, 0.05); }
    .keeper-dashboard { padding: 25px; color: #e2e8f0; }
    
    .store-header { background: linear-gradient(135deg, #064e3b 0%, #065f46 100%); padding: 30px; border-radius: 24px; border: 1px solid rgba(255,255,255,0.1); margin-bottom: 30px; }
    
    .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 35px; }
    .kpi-card { background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(255,255,255,0.05); border-radius: 20px; padding: 25px; transition: transform 0.3s ease; }
    .kpi-card:hover { transform: translateY(-5px); border-color: var(--keeper-primary); }
    .kpi-val { font-size: 2rem; font-weight: 800; color: #fff; margin-bottom: 5px; }
    .kpi-lbl { font-size: 0.75rem; color: #94a3b8; text-transform: uppercase; font-weight: 700; letter-spacing: 1px; }

    .nav-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
    .nav-box { background: rgba(30, 41, 59, 0.4); border: 1px solid rgba(255,255,255,0.08); border-radius: 20px; padding: 25px; text-decoration: none; color: inherit; transition: all 0.3s; }
    .nav-box:hover { background: rgba(16, 185, 129, 0.1); border-color: var(--keeper-primary); transform: scale(1.02); }
    
    .glass-panel { background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(10px); border-radius: 24px; border: 1px solid rgba(255,255,255,0.05); padding: 30px; }
</style>

<div class="keeper-dashboard">
    <?php if (!$store): ?>
        <div class="glass-panel text-center py-5 shadow-2xl">
            <i class="fas fa-warehouse fa-4x text-warning mb-4 opacity-50"></i>
            <h2 class="fw-bold">No Store Assigned</h2>
            <p class="text-secondary">You are not currently assigned as a Keeper to any warehouse location.<br>Please contact your Store Manager.</p>
        </div>
    <?php else: ?>
        <div class="store-header shadow-xl d-flex justify-content-between align-items-center">
            <div>
                <span class="badge bg-white text-success mb-2 px-3 py-2 fw-bold" style="letter-spacing: 1px;">OPERATIONAL ACCESS</span>
                <h1 class="display-5 fw-bold text-white mb-1"><?= htmlspecialchars($store['store_name']) ?></h1>
                <p class="text-white opacity-70 mb-0"><i class="fas fa-map-marker-alt me-2"></i> <?= htmlspecialchars($store['location']) ?></p>
            </div>
            <div class="text-end text-white">
                <div class="h3 fw-bold mb-0"><?= date('H:i') ?></div>
                <div class="text-sm opacity-70"><?= date('D, M d, Y') ?></div>
            </div>
        </div>

        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-lbl">Stock Items</div>
                <div class="kpi-val"><?= $totalItems ?></div>
                <span class="text-xs text-secondary">Unique SKUs in Store</span>
            </div>
            <div class="kpi-card">
                <div class="kpi-lbl">Total Availability</div>
                <div class="kpi-val text-success"><?= number_format($availableQty) ?></div>
                <span class="text-xs text-secondary">Cumulative Units</span>
            </div>
            <div class="kpi-card" style="border-left: 4px solid #f59e0b;">
                <div class="kpi-lbl">Pending Issue Orders</div>
                <div class="kpi-val text-warning"><?= $pendingIssues ?></div>
                <span class="text-xs text-secondary">Awaiting Fulfillment</span>
            </div>
            <div class="kpi-card">
                <div class="kpi-lbl">Issued Today</div>
                <div class="kpi-val"><?= $completedToday ?></div>
                <span class="text-xs text-secondary">Completed Transactions</span>
            </div>
        </div>

        <?php if ($view === 'overview'): ?>
            <div class="nav-grid">
                <a href="?module=store/keeper_dashboard/index&view=issues" class="nav-box">
                    <i class="fas fa-truck-loading fa-2x text-warning mb-3"></i>
                    <h5 class="fw-bold">Approved Issue Orders</h5>
                    <p class="text-sm text-secondary mb-0">Execute material releases authorized by the Store Manager.</p>
                </a>
                <a href="?module=store/keeper_dashboard/index&view=updates" class="nav-box">
                    <i class="fas fa-edit fa-2x text-primary mb-3"></i>
                    <h5 class="fw-bold">Stock Count & Returns</h5>
                    <p class="text-sm text-secondary mb-0">Update stock levels for returns or physical inventory corrections.</p>
                </a>
                <a href="?module=store/keeper_dashboard/index&view=transfers" class="nav-box">
                    <i class="fas fa-exchange-alt fa-2x text-info mb-3"></i>
                    <h5 class="fw-bold">Internal Transfers</h5>
                    <p class="text-sm text-secondary mb-0">Dispatch or receive materials from other company stores.</p>
                </a>
                <a href="?module=store/keeper_dashboard/index&view=history" class="nav-box">
                    <i class="fas fa-history fa-2x text-secondary mb-3"></i>
                    <h5 class="fw-bold">Store Movement History</h5>
                    <p class="text-sm text-secondary mb-0">Review all historical stock movements for this specific store.</p>
                </a>
            </div>
        <?php else: ?>
            <div class="mt-4">
                <?php 
                $file = __DIR__ . '/' . $view . '.php';
                if (file_exists($file)) {
                    include $file;
                } else {
                    echo '<div class="glass-panel text-center py-5">
                            <i class="fas fa-tools fa-3x text-secondary mb-3 opacity-20"></i>
                            <h3>Component Initializing...</h3>
                            <p class="text-secondary text-sm">Building out ' . $view . ' operational logic.</p>
                          </div>';
                }
                ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
