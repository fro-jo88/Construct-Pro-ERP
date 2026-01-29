<?php
// modules/store/manager_dashboard/index.php

require_once __DIR__ . '/../../../includes/AuthManager.php';
require_once __DIR__ . '/../../../includes/Database.php';

AuthManager::requireRole(['STORE_MANAGER', 'SYSTEM_ADMIN']);

$db = Database::getInstance();
$user_id = $_SESSION['user_id'];
$view = $_GET['view'] ?? 'overview';

// --- KPI CALCULATIONS ---

// 1. Total Items in Stock (Distinct SKUs with qty > 0)
$totalItems = $db->query("SELECT COUNT(DISTINCT product_id) FROM stock_levels WHERE quantity > 0")->fetchColumn();

// 2. Low Stock Alerts (Qty < min_threshold)
$lowStock = $db->query("SELECT COUNT(*) FROM products p 
                        JOIN stock_levels sl ON p.id = sl.product_id 
                        WHERE sl.quantity <= p.min_threshold AND p.min_threshold > 0")->fetchColumn();

// 3. Pending Issue Requests (Validated by HR, pending Store Manager)
$pendingIssues = $db->query("SELECT COUNT(*) FROM material_requests 
                             WHERE hr_review_status = 'validated' 
                             AND store_manager_approval = 'pending'")->fetchColumn();

// 4. Pending Transfer Requests
$pendingTransfers = $db->query("SELECT COUNT(*) FROM stock_transfers WHERE manager_approval = 'pending'")->fetchColumn();

?>

<style>
    :root { --store-primary: #0ea5e9; --store-bg: #0f172a; --store-card: rgba(30, 41, 59, 0.5); }
    .store-dashboard { padding: 25px; color: #f1f5f9; }
    
    .kpi-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 35px; }
    .kpi-card { background: var(--store-card); border: 1px solid rgba(255,255,255,0.08); border-radius: 20px; padding: 25px; position: relative; overflow: hidden; transition: transform 0.3s ease; }
    .kpi-card:hover { transform: translateY(-5px); border-color: var(--store-primary); }
    .kpi-card i { position: absolute; right: -10px; bottom: -10px; font-size: 5rem; opacity: 0.05; transform: rotate(-15deg); }
    .kpi-val { font-size: 2.2rem; font-weight: 800; margin: 5px 0; color: #fff; }
    .kpi-lbl { font-size: 0.75rem; color: #94a3b8; text-transform: uppercase; font-weight: 700; letter-spacing: 1.5px; }
    
    .glass-panel { background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(12px); border-radius: 24px; border: 1px solid rgba(255,255,255,0.05); padding: 30px; }
    
    .status-badge { padding: 4px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
    .status-pending { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.2); }
    .status-approved { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); }
    .status-rejected { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }
    
    .nav-tabs-custom { border: none; gap: 10px; margin-bottom: 25px; }
    .nav-tabs-custom .nav-link { background: rgba(255,255,255,0.03); border: 1px solid transparent; color: #94a3b8; border-radius: 12px; padding: 12px 20px; font-weight: 600; transition: all 0.3s; }
    .nav-tabs-custom .nav-link:hover { background: rgba(255,255,255,0.08); color: #fff; }
    .nav-tabs-custom .nav-link.active { background: var(--store-primary); color: #fff; box-shadow: 0 4px 15px rgba(14, 165, 233, 0.3); }

    .table-custom { border-collapse: separate; border-spacing: 0 10px; }
    .table-custom tr { background: rgba(255,255,255,0.02); transition: all 0.2s; }
    .table-custom tr:hover { background: rgba(255,255,255,0.04); }
    .table-custom td { padding: 18px 15px; border-top: 1px solid rgba(255,255,255,0.03); border-bottom: 1px solid rgba(255,255,255,0.03); }
    .table-custom tr td:first-child { border-left: 1px solid rgba(255,255,255,0.03); border-radius: 15px 0 0 15px; }
    .table-custom tr td:last-child { border-right: 1px solid rgba(255,255,255,0.03); border-radius: 0 15px 15px 0; }
</style>

<div class="store-dashboard">
    <div class="d-flex justify-content-between align-items-end mb-5">
        <div>
            <h1 class="fw-extrabold mb-1" style="font-size: 2.5rem; letter-spacing: -1.5px;">Store Control Center</h1>
            <p class="text-secondary mb-0 fw-medium">Global Gatekeeper: Inventory Movement & Issue Approvals.</p>
        </div>
        <div class="text-end">
            <span class="badge bg-info text-dark px-3 py-2 fw-bold" style="letter-spacing: 1px;">HQ OVERSIGHT</span>
        </div>
    </div>

    <!-- KPI ROW -->
    <div class="kpi-row">
        <div class="kpi-card">
            <i class="fas fa-boxes"></i>
            <div class="kpi-lbl">Total Items In Stock</div>
            <div class="kpi-val text-info"><?= $totalItems ?></div>
            <div class="text-xs text-secondary">Active SKUs with Balance</div>
        </div>
        <div class="kpi-card">
            <i class="fas fa-exclamation-triangle"></i>
            <div class="kpi-lbl">Low Stock Alerts</div>
            <div class="kpi-val text-danger"><?= $lowStock ?></div>
            <div class="text-xs text-secondary">Below Reorder Threshold</div>
        </div>
        <div class="kpi-card">
            <i class="fas fa-clipboard-check"></i>
            <div class="kpi-lbl">Pending Issue Requests</div>
            <div class="kpi-val text-warning"><?= $pendingIssues ?></div>
            <div class="text-xs text-secondary">Approved by HR, Awaiting Store</div>
        </div>
        <div class="kpi-card">
            <i class="fas fa-exchange-alt"></i>
            <div class="kpi-lbl">Transfer Requests</div>
            <div class="kpi-val text-primary"><?= $pendingTransfers ?></div>
            <div class="text-xs text-secondary">Inter-Store Movements</div>
        </div>
    </div>

    <!-- MAIN VIEW AREA -->
    <?php if ($view === 'overview'): ?>
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="glass-panel h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="fw-bold mb-0">High Priority Approvals</h4>
                        <a href="?module=store/manager_dashboard/index&view=issues" class="text-primary text-sm fw-bold text-decoration-none">View All Requests →</a>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-custom text-white">
                            <thead class="text-secondary text-xs text-uppercase fw-bold">
                                <tr>
                                    <th>Ref</th>
                                    <th>Site / Store</th>
                                    <th>Material</th>
                                    <th>Qty</th>
                                    <th>Stock</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <?php
                                // Mock/Recent Data fetching for issues
                                $recentIssues = $db->query("SELECT mr.*, s.site_name, p.product_name, p.unit, sl.quantity as available
                                                            FROM material_requests mr
                                                            JOIN sites s ON mr.site_id = s.id
                                                            JOIN products p ON mr.item_name = p.product_name -- Assuming item_name links or just query
                                                            LEFT JOIN stock_levels sl ON p.id = sl.product_id
                                                            WHERE mr.hr_review_status = 'validated' 
                                                            AND mr.store_manager_approval = 'pending'
                                                            LIMIT 5")->fetchAll();
                                
                                foreach ($recentIssues as $ri):
                                ?>
                                <tr>
                                    <td class="font-monospace text-secondary">#MR-<?= $ri['id'] ?></td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($ri['site_name']) ?></div>
                                        <div class="text-xs text-secondary">Requested by: Forman</div>
                                    </td>
                                    <td><?= htmlspecialchars($ri['product_name']) ?></td>
                                    <td class="fw-bold"><?= $ri['quantity'] ?> <?= $ri['unit'] ?></td>
                                    <td>
                                        <span class="text-<?= ($ri['available'] >= $ri['quantity']) ? 'success' : 'danger' ?>">
                                            <?= $ri['available'] ?? 0 ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-primary px-3 rounded-pill" onclick="quickApprove(<?= $ri['id'] ?>)">Approve</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($recentIssues)): ?>
                                    <tr><td colspan="6" class="text-center py-5 text-secondary">No pending issue requests requiring priority approval.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="glass-panel h-100">
                    <h4 class="fw-bold mb-4">Critical Alerts</h4>
                    <div class="d-flex flex-column gap-3">
                        <?php
                        $criticalItems = $db->query("SELECT p.product_name, sl.quantity, p.min_threshold 
                                                      FROM products p 
                                                      JOIN stock_levels sl ON p.id = sl.product_id 
                                                      WHERE sl.quantity <= p.min_threshold AND p.min_threshold > 0
                                                      LIMIT 5")->fetchAll();
                        
                        foreach ($criticalItems as $ci):
                        ?>
                            <div class="p-3 rounded-xl border border-danger border-opacity-20 bg-danger bg-opacity-10">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="fw-bold text-white"><?= htmlspecialchars($ci['product_name']) ?></span>
                                    <span class="badge bg-danger">Critical</span>
                                </div>
                                <div class="text-xs text-secondary mb-2">Current: <?= $ci['quantity'] ?> | Min: <?= $ci['min_threshold'] ?></div>
                                <div class="progress" style="height: 4px; background: rgba(0,0,0,0.2);">
                                    <div class="progress-bar bg-danger" style="width: <?= ($ci['quantity']/$ci['min_threshold'])*100 ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($criticalItems)): ?>
                            <div class="text-center py-4 text-secondary italic">
                                <i class="fas fa-check-circle fa-2x mb-2 text-success opacity-20"></i>
                                <p>All stock levels are above reorder thresholds.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="module-container">
            <?php 
            $file = __DIR__ . '/' . $view . '.php';
            if (file_exists($file)) {
                include $file;
            } else {
                echo '<div class="glass-panel text-center py-5">
                        <i class="fas fa-microchip fa-3x text-secondary mb-3 opacity-20"></i>
                        <h3>Module Initialization...</h3>
                        <p class="text-secondary">Scaling Store Manager ' . ucfirst($view) . ' Sub-system.</p>
                      </div>';
            }
            ?>
        </div>
    <?php endif; ?>
</div>
