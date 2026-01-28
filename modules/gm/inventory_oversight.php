<?php
// modules/gm/inventory_oversight.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/GMManager.php';

AuthManager::requireRole('GM');

$oversight = GMManager::getInventoryOversight();
?>

<div class="gm-inventory">
    <div class="page-header mb-4">
        <h2><i class="fas fa-warehouse"></i> Global Material & Stock Oversight</h2>
        <p class="text-dim">Real-time stock level monitoring across all central stores and site-level emergency releases.</p>
    </div>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.5rem;">
        <!-- Low Stock Alerts -->
        <section class="glass-card">
            <h3 style="color:#ff4444;"><i class="fas fa-exclamation-triangle"></i> Critical Stock Exceptions</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Material</th>
                        <th>Store</th>
                        <th>Current Qty</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($oversight['low_stock'])): ?>
                        <tr><td colspan="4" class="text-center py-4">All stock levels within safe thresholds.</td></tr>
                    <?php else: ?>
                        <?php foreach ($oversight['low_stock'] as $ls): ?>
                        <tr>
                            <td><strong><?= $ls['product_name'] ?></strong></td>
                            <td><?= $ls['store_name'] ?></td>
                            <td style="color:#ff4444; font-weight:bold;"><?= $ls['quantity'] ?></td>
                            <td><button class="btn-primary-sm" style="background:var(--gold); color:black;">Authorize PO</button></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <!-- Emergency Releases -->
        <section class="glass-card">
            <h3 style="color:var(--gold);"><i class="fas fa-truck-loading"></i> Pending Site Material Releases</h3>
            <div class="release-list">
                <?php if (empty($oversight['pending_releases'])): ?>
                    <p class="text-dim text-center py-5">No emergency releases awaiting GM sign-off.</p>
                <?php else: ?>
                    <?php foreach ($oversight['pending_releases'] as $pr): ?>
                        <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,204,0,0.2); border-radius:12px; padding:1.2rem; margin-bottom:1rem;">
                            <div style="display:flex; justify-content:space-between; align-items:start;">
                                <div>
                                    <div style="font-weight:bold; font-size:1.1rem;"><?= $pr['site_name'] ?></div>
                                    <div style="font-size:0.8rem; color:var(--text-dim);">Requester: <?= $pr['requester'] ?></div>
                                </div>
                                <span class="status-badge pending">URGENT</span>
                            </div>
                            <div class="mt-3" style="display:flex; gap:0.5rem;">
                                <a href="main.php?module=gm/approvals" class="btn-primary-sm" style="background:#00ff64; color:black; display:block; text-align:center; text-decoration:none;">Go to Approval Hub</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <!-- Company Wide Store Summary -->
    <section class="glass-card mt-4">
        <h3><i class="fas fa-boxes"></i> Distribution Centers</h3>
        <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:1rem; margin-top:1rem;">
            <div style="background:rgba(255,255,255,0.02); padding:1.5rem; border-radius:12px; text-align:center; border:1px solid rgba(255,255,255,0.05);">
                <i class="fas fa-warehouse fa-2x text-dim mb-2"></i>
                <div style="font-weight:bold;">Central Depot A</div>
                <div style="font-size:0.8rem; color:#00ff64;">92% Stock Health</div>
            </div>
            <div style="background:rgba(255,255,255,0.02); padding:1.5rem; border-radius:12px; text-align:center; border:1px solid rgba(255,255,255,0.05);">
                <i class="fas fa-warehouse fa-2x text-dim mb-2"></i>
                <div style="font-weight:bold;">Western Hub</div>
                <div style="font-size:0.8rem; color:#ffcc00;">65% Stock Health</div>
            </div>
            <div style="background:rgba(255,255,255,0.02); padding:1.5rem; border-radius:12px; text-align:center; border:1px solid rgba(255,255,255,0.05);">
                <i class="fas fa-warehouse fa-2x text-dim mb-2"></i>
                <div style="font-weight:bold;">Eastern Yard</div>
                <div style="font-size:0.8rem; color:#00ff64;">88% Stock Health</div>
            </div>
            <div style="background:rgba(255,255,255,0.02); padding:1.5rem; border-radius:12px; text-align:center; border:1px solid rgba(255,255,255,0.05);">
                <i class="fas fa-plus fa-2x text-dim mb-2"></i>
                <div style="font-weight:bold;">Add Logic</div>
                <div style="font-size:0.8rem; color:var(--text-dim);">Expand Store Map</div>
            </div>
        </div>
    </section>
</div>
