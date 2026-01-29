<?php
// modules/transport/driver_manager_dashboard/pending_requests.php

$db = Database::getInstance();

// 1. Fetch Material Requests that are "Issued" but not yet assigned transport
$materialRequests = $db->query("SELECT mr.*, s.site_name, st.store_name as origin_store, p.product_name, p.unit
                                FROM material_requests mr
                                JOIN sites s ON mr.site_id = s.id
                                JOIN stores st ON mr.fulfilling_store_id = st.id
                                JOIN products p ON mr.item_name = p.product_name
                                LEFT JOIN transport_orders to_tr ON (to_tr.reference_id = mr.id AND to_tr.reference_type = 'material_request')
                                WHERE mr.status = 'issued' 
                                AND to_tr.id IS NULL
                                ORDER BY mr.created_at DESC")->fetchAll();

// 2. Fetch Stock Transfers that are "Approved" (and possibly dispatched from origin)
$transferRequests = $db->query("SELECT st.*, s1.store_name as from_store, s2.store_name as to_store, p.product_name, p.unit
                                FROM stock_transfers st
                                JOIN stores s1 ON st.from_store_id = s1.id
                                JOIN stores s2 ON st.to_store_id = s2.id
                                JOIN products p ON st.material_id = p.id
                                LEFT JOIN transport_orders to_tr ON (to_tr.reference_id = st.id AND to_tr.reference_type = 'stock_transfer')
                                WHERE st.manager_approval = 'approved'
                                AND st.transfer_status = 'in_transit' -- Keeper dispatched it
                                AND to_tr.id IS NULL
                                ORDER BY st.created_at DESC")->fetchAll();

?>

<div class="glass-panel">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Unassigned Transport Requests</h4>
        <div class="text-secondary text-sm">Awaiting Driver & Vehicle Mapping</div>
    </div>

    <div class="alert alert-info py-2 text-xs border-0 bg-opacity-10 bg-info">
        <i class="fas fa-info-circle me-1"></i> These items have been physically issued by the Store Keeper and are ready for pickup.
    </div>

    <div class="table-responsive">
        <table class="table table-custom text-white">
            <thead class="text-secondary text-xs text-uppercase fw-bold">
                <tr>
                    <th>Ref</th>
                    <th>Type</th>
                    <th>Origin → Destination</th>
                    <th>Material Summary</th>
                    <th>Priority</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <!-- Material Requests -->
                <?php foreach ($materialRequests as $mr): ?>
                <tr class="align-middle">
                    <td class="font-monospace text-secondary">#MR-<?= $mr['id'] ?></td>
                    <td><span class="badge bg-dark border border-secondary">MATERIAL ISSUE</span></td>
                    <td>
                        <div class="fw-bold"><?= htmlspecialchars($mr['origin_store']) ?></div>
                        <i class="fas fa-long-arrow-alt-right mx-2 text-secondary opacity-30"></i>
                        <span class="text-warning"><?= htmlspecialchars($mr['site_name']) ?></span>
                    </td>
                    <td>
                        <div class="fw-medium text-white"><?= htmlspecialchars($mr['product_name']) ?></div>
                        <div class="text-xs text-secondary"><?= $mr['quantity'] ?> <?= $mr['unit'] ?></div>
                    </td>
                    <td>
                        <span class="badge border <?= $mr['priority'] == 'urgent' ? 'border-danger text-danger' : 'border-secondary' ?>">
                            <?= strtoupper($mr['priority'] ?? 'NORMAL') ?>
                        </span>
                    </td>
                    <td class="text-end">
                        <a href="?module=transport/driver_manager_dashboard/index&view=assign_driver&ref_id=<?= $mr['id'] ?>&type=material_request" class="btn btn-primary btn-sm rounded-pill px-3">Assign Driver</a>
                    </td>
                </tr>
                <?php endforeach; ?>

                <!-- Stock Transfers -->
                <?php foreach ($transferRequests as $st): ?>
                <tr class="align-middle">
                    <td class="font-monospace text-secondary">#TR-<?= $st['id'] ?></td>
                    <td><span class="badge bg-primary bg-opacity-20 text-primary border border-primary border-opacity-30">STOCK TRANSFER</span></td>
                    <td>
                        <div class="fw-bold"><?= htmlspecialchars($st['from_store']) ?></div>
                        <i class="fas fa-long-arrow-alt-right mx-2 text-secondary opacity-30"></i>
                        <span class="text-info"><?= htmlspecialchars($st['to_store']) ?></span>
                    </td>
                    <td>
                        <div class="fw-medium text-white"><?= htmlspecialchars($st['product_name']) ?></div>
                        <div class="text-xs text-secondary"><?= $st['quantity'] ?> <?= $st['unit'] ?></div>
                    </td>
                    <td><span class="badge border border-secondary">NORMAL</span></td>
                    <td class="text-end">
                        <a href="?module=transport/driver_manager_dashboard/index&view=assign_driver&ref_id=<?= $st['id'] ?>&type=stock_transfer" class="btn btn-primary btn-sm rounded-pill px-3">Assign Driver</a>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($materialRequests) && empty($transferRequests)): ?>
                    <tr><td colspan="6" class="text-center py-5 text-secondary italic">No pending transport requests at this moment.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
