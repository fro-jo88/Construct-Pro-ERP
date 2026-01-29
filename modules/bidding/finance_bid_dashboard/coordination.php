<?php
// modules/bidding/finance_bid_dashboard/coordination.php

$tender_id = $_GET['id'] ?? null;
$db = Database::getInstance();

if (!$tender_id) {
    // PREMIUM LIST VIEW
    $bids = $db->query("
        SELECT b.*, tb.status as tech_status, fb.status as fin_status 
        FROM bids b 
        LEFT JOIN technical_bids tb ON b.id = tb.bid_id 
        LEFT JOIN financial_bids fb ON b.id = fb.bid_id 
        WHERE b.status NOT IN ('WON', 'LOSS', 'DROPPED')
        ORDER BY b.created_at DESC
    ")->fetchAll();
    ?>
    <div class="premium-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <h3 style="margin: 0; font-size: 1.5rem; color: #fff;"><i class="fas fa-sync-alt text-gold"></i> Bid Coordination Board</h3>
                <p class="text-secondary" style="margin-top: 5px;">Synchronize financial estimates with technical requirements across the pipeline.</p>
            </div>
            <div style="display: flex; gap: 10px;">
                <div class="glass-tag"><i class="fas fa-filter"></i> All Regions</div>
                <div class="glass-tag"><i class="fas fa-sort-amount-down"></i> Recent</div>
            </div>
        </div>
        
        <table class="premium-table">
            <thead>
                <tr>
                    <th>Ref #</th>
                    <th>Project & Client</th>
                    <th>Technical Gateway</th>
                    <th>Financial Progress</th>
                    <th>Coordination</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($bids)): ?>
                    <tr><td colspan="6" style="text-align: center; color: rgba(255,255,255,0.2); padding: 50px;">No bids requiring coordination.</td></tr>
                <?php else: 
                    foreach ($bids as $b): 
                        $mismatch = false;
                        if ($b['tech_status'] == 'ready' && ($b['fin_status'] == 'draft' || !$b['fin_status'])) $mismatch = true; 
                    ?>
                <tr>
                    <td style="font-family: monospace; color: var(--gold); font-weight: 700;"><?= $b['tender_no'] ?></td>
                    <td>
                        <div style="font-weight: 600; color: #fff;"><?= htmlspecialchars($b['title']) ?></div>
                        <div style="font-size: 0.75rem; color: rgba(255,255,255,0.3);"><?= htmlspecialchars($b['client_name']) ?></div>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="width: 8px; height: 8px; border-radius: 50%; background: <?= $b['tech_status'] == 'ready' ? '#00ff64' : '#ffcc00' ?>; box-shadow: 0 0 10px <?= $b['tech_status'] == 'ready' ? 'rgba(0,255,100,0.3)' : 'rgba(255,204,0,0.3)' ?>;"></div>
                            <span class="badge-premium" style="background: rgba(255,255,255,0.05); color: #fff;"><?= strtoupper($b['tech_status'] ?: 'PENDING') ?></span>
                        </div>
                    </td>
                    <td>
                        <span class="badge-premium" style="background: rgba(255,255,255,0.05); color: #fff;"><?= strtoupper($b['fin_status'] ?: 'NOT STARTED') ?></span>
                    </td>
                    <td>
                        <?php if ($mismatch): ?>
                            <div style="color: #ff4444; font-size: 0.85rem; display: flex; align-items: center; gap: 6px;">
                                <i class="fas fa-exclamation-triangle"></i> Review Req.
                            </div>
                        <?php else: ?>
                            <div style="color: #00ff64; font-size: 0.85rem; display: flex; align-items: center; gap: 6px;">
                                <i class="fas fa-check-circle"></i> Calibrated
                            </div>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: right;">
                        <a href="?module=bidding/finance_bid_dashboard/index&view=coordination&id=<?= $b['id'] ?>" class="btn-primary-sm" style="text-decoration: none; padding: 8px 16px;">
                            Coordinate <i class="fas fa-chevron-right" style="font-size: 0.7rem; margin-left: 4px;"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <style>
        .glass-tag {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            padding: 6px 14px;
            border-radius: 12px;
            font-size: 0.8rem;
            color: rgba(255,255,255,0.6);
            cursor: pointer;
            transition: all 0.3s;
        }
        .glass-tag:hover {
            background: rgba(255,204,0,0.1);
            color: #ffcc00;
            border-color: rgba(255,204,0,0.3);
        }
        .btn-glass {
            text-decoration: none;
            font-size: 0.75rem;
            color: var(--gold);
            border: 1px solid rgba(255, 204, 0, 0.3);
            padding: 5px 15px;
            border-radius: 20px;
            background: rgba(255, 204, 0, 0.05);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-glass:hover {
            background: var(--gold);
            color: #000;
            box-shadow: 0 4px 15px rgba(255, 204, 0, 0.3);
            transform: translateY(-2px);
        }
    </style>
    <?php
} else {
    // PREMIUM DETAIL VIEW
    $stmt = $db->prepare("SELECT * FROM bids WHERE id = ?");
    $stmt->execute([$tender_id]);
    $tender = $stmt->fetch();

    $stmt = $db->prepare("SELECT * FROM technical_bids WHERE bid_id = ?");
    $stmt->execute([$tender_id]);
    $tech_bid = $stmt->fetch();

    $stmt = $db->prepare("SELECT * FROM financial_bids WHERE bid_id = ?");
    $stmt->execute([$tender_id]);
    $fin_bid = $stmt->fetch();
    
    $tech_qty = json_decode($tech_bid['resource_plan'] ?? '{}', true); 
    $fin_est = json_decode($fin_bid['cost_breakdown'] ?? '{}', true); 
    ?>
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem;">
        <div>
            <h2 style="margin: 0; font-size: 1.75rem; color: #fff;"><i class="fas fa-search-dollar text-gold"></i> Calibration: <span style="font-weight: 300;"><?= htmlspecialchars($tender['title']) ?></span></h2>
            <div style="display: flex; align-items: center; gap: 15px; margin-top: 8px;">
                <span class="badge-premium" style="background: rgba(255,204,0,0.1); color: #ffcc00;"><?= $tender['tender_no'] ?></span>
                <span class="text-secondary" style="font-size: 0.9rem;"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($tender['client_name']) ?></span>
            </div>
        </div>
        <a href="?module=bidding/finance_bid_dashboard/index&view=coordination" class="btn-secondary-sm" style="text-decoration: none; padding: 10px 20px;">
            <i class="fas fa-arrow-left"></i> Back to Queue
        </a>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
        <!-- TECHNICAL COLUMN (READ ONLY) -->
        <div class="premium-card" style="border-top: 4px solid var(--accent-blue);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h4 style="margin: 0; color: var(--accent-blue);"><i class="fas fa-microchip"></i> Technical Blueprint</h4>
                <div style="font-size: 0.7rem; color: rgba(255,255,255,0.3); text-transform: uppercase; letter-spacing: 1px;">Source: Technical Team</div>
            </div>

            <div style="background: rgba(255,255,255,0.03); padding: 15px; border-radius: 12px; margin-bottom: 1.5rem;">
                <div style="font-size: 0.7rem; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 5px;">Readiness State</div>
                <div style="font-weight: bold; color: #fff; font-size: 1.1rem;"><?= strtoupper($tech_bid['status'] ?? 'PENDING') ?></div>
            </div>
            
            <h5 style="font-size: 1rem; margin-bottom: 1rem; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px;">Resource Allocation</h5>
            <?php if (empty($tech_qty)): ?>
                <div style="padding: 40px; text-align: center; color: rgba(255,255,255,0.2); border: 1px dashed rgba(255,255,255,0.1); border-radius: 12px;">
                    <i class="fas fa-ghost fa-2x mb-3"></i>
                    <div>Awaiting resource mapping from Technical Department.</div>
                </div>
            <?php else: ?>
                <table class="premium-table">
                    <tr style="background: transparent;"><th style="padding: 0 0 10px 0;">Resource Category</th><th style="padding: 0 0 10px 0;">Required Qty</th></tr>
                    <?php foreach ($tech_qty as $k => $v): ?>
                    <tr>
                        <td><?= htmlspecialchars($k) ?></td>
                        <td style="font-family: monospace; font-weight: bold; color: #fff;"><?= htmlspecialchars($v) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>

            <div style="margin-top: 2rem; padding: 15px; background: rgba(0, 150, 255, 0.05); border-radius: 12px; border: 1px solid rgba(0, 150, 255, 0.1); font-size: 0.85rem; color: var(--accent-blue); display: flex; gap: 12px; align-items: flex-start;">
                <i class="fas fa-shield-alt" style="margin-top: 3px;"></i>
                <div>This data is cryptographically locked for the Finance role. Any discrepancies should be noted in the coordination log.</div>
            </div>
        </div>

        <!-- FINANCE COLUMN -->
        <div class="premium-card" style="border-top: 4px solid var(--gold);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h4 style="margin: 0; color: var(--gold);"><i class="fas fa-file-invoice-dollar"></i> Financial Model</h4>
                <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 4px;">
                    <div style="font-size: 0.7rem; color: rgba(255,255,255,0.3); text-transform: uppercase; letter-spacing: 1px;">Role: Finance Bid Manager</div>
                    <?php if ($fin_bid): ?>
                        <a href="?module=bidding/finance_bid_dashboard/index&view=preparation&id=<?= $tender_id ?>" class="btn-glass">
                            <i class="fas fa-edit"></i> Edit Model
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (!$fin_bid): ?>
                <div style="text-align: center; padding: 80px 40px;">
                    <div style="width: 80px; height: 80px; background: rgba(255,204,0,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px auto;">
                        <i class="fas fa-calculator fa-2x text-gold"></i>
                    </div>
                    <h5 style="margin-bottom: 10px;">No Financial Activity Detected</h5>
                    <p class="text-secondary" style="font-size: 0.9rem; margin-bottom: 25px;">The financial model for this bid has not been initialized. Proceed to the Force Estimation engine.</p>
                    <a href="?module=bidding/finance_bid_dashboard/index&view=preparation&id=<?= $tender_id ?>" class="btn-primary-sm" style="text-decoration: none; padding: 12px 30px; border-radius: 30px;">Initialize Estimates</a>
                </div>
            <?php else: ?>
                <div style="background: rgba(255,255,255,0.03); padding: 15px; border-radius: 12px; margin-bottom: 1.5rem;">
                    <div style="font-size: 0.7rem; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 5px;">Estimation Status</div>
                    <div style="font-weight: bold; color: var(--gold); font-size: 1.1rem;"><?= strtoupper($fin_bid['status']) ?></div>
                </div>
                
                <?php
                 $fin_json = json_decode($fin_bid['boq_json'] ?? '{}', true);
                 $is_manual_boq = !empty($fin_json['boq_structure']);
                 ?>

                 <?php if (!$is_manual_boq): ?>
                     <!-- LEGACY BREAKDOWN -->
                     <h5 style="font-size: 1rem; margin-bottom: 1rem; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px;">Cost Calibration (Legacy)</h5>
                     <table class="premium-table">
                        <tr style="background: transparent;"><th style="padding: 0 0 10px 0;">Cost Category</th><th style="padding: 0 0 10px 0;">Estimated Value</th><th style="padding: 0 0 10px 0; text-align: right;">Variance</th></tr>
                        <tr>
                            <td>Labor Infrastructure</td>
                            <td style="font-family: monospace; font-weight: bold; color: #fff;">$<?= number_format($fin_est['labor'] ?? 0, 2) ?></td>
                            <td style="text-align: right;"><i class="fas fa-check-circle text-green"></i></td>
                        </tr>
                        <tr>
                            <td>Material Procurement</td>
                            <td style="font-family: monospace; font-weight: bold; color: #fff;">$<?= number_format($fin_est['materials'] ?? 0, 2) ?></td>
                            <td style="text-align: right;">
                                <?php if (($fin_est['materials'] ?? 0) == 0 && !empty($tech_qty)): ?>
                                    <i class="fas fa-exclamation-circle text-red" title="Critical: Tech requested items not found in Budget!"></i>
                                <?php else: ?>
                                    <i class="fas fa-check-circle text-green"></i>
                                <?php endif; ?>
                            </td>
                        </tr>
                     </table>
                 <?php else: ?>
                     <!-- MANUAL BOQ SUMMARY -->
                     <h5 style="font-size: 1rem; margin-bottom: 1rem; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px;">BOQ Main Sections</h5>
                     <table class="premium-table">
                        <tr style="background: transparent;"><th style="padding: 0 0 10px 0;">Section Name</th><th style="padding: 0 0 10px 0; text-align: right;">Total Amount</th></tr>
                        <?php 
                        foreach ($fin_json['boq_structure'] as $sec): 
                            // Calculate section total recursively or read if saved? 
                            // We didn't save totals in JSON explicitly, need to sum.
                            // Actually, calculating on the fly is safer.
                            $secTotal = 0;
                            if (!empty($sec['children'])) {
                                foreach ($sec['children'] as $child) {
                                    if ($child['type'] === 'subsection' && !empty($child['children'])) {
                                        foreach ($child['children'] as $item) $secTotal += $item['amount'] ?? 0;
                                    } elseif ($child['type'] === 'item') {
                                        $secTotal += $child['amount'] ?? 0;
                                    }
                                }
                            }
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($sec['name']) ?></td>
                            <td style="font-family: monospace; font-weight: bold; color: #fff; text-align: right;">$<?= number_format($secTotal, 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr style="border-top: 1px solid rgba(255,255,255,0.1);">
                            <td style="padding-top: 10px; color: var(--gold);">Grand Total (Inc. Tax)</td>
                            <td style="padding-top: 10px; font-family: monospace; font-weight: 800; color: var(--gold); text-align: right;">$<?= number_format($fin_bid['total_amount'], 2) ?></td>
                        </tr>
                     </table>
                 <?php endif; ?>
                 
                 <div style="margin-top: 2.5rem;">
                    <label style="display: block; font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 10px; text-transform: uppercase; letter-spacing: 1px;">Collaboration Log (Finance ↔ Tech)</label>
                    <textarea style="width: 100%; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; color: #fff; padding: 15px; font-size: 0.9rem; resize: none;" rows="4" placeholder="Note discrepancies, request clarifications or log synchronization events..."></textarea>
                    <div style="display: flex; justify-content: flex-end; margin-top: 12px;">
                        <button class="btn-primary-sm" style="padding: 10px 24px; border-radius: 30px;">Secure Save Comment</button>
                    </div>
                 </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
?>

