<?php
// modules/transport/driver_manager_dashboard/schedule.php

$db = Database::getInstance();

$filter_date = $_GET['date'] ?? date('Y-m-d');

$orders = $db->query("SELECT t.*, u.username as driver_name, v.plate_number, v.model, 
                             s.site_name, st.store_name as dest_store, o.store_name as origin_store
                      FROM transport_orders t
                      LEFT JOIN users u ON t.driver_id = u.id
                      LEFT JOIN vehicles v ON t.vehicle_id = v.id
                      LEFT JOIN sites s ON t.destination_site_id = s.id
                      LEFT JOIN stores st ON t.destination_store_id = st.id
                      LEFT JOIN stores o ON t.origin_store_id = o.id
                      WHERE t.requested_date = ?
                      ORDER BY t.priority DESC, t.created_at ASC", [$filter_date])->fetchAll();

?>

<div class="glass-panel">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Daily Transport Schedule</h4>
            <div class="text-secondary text-sm">Managing fleet distribution for <?= date('M d, Y', strtotime($filter_date)) ?></div>
        </div>
        <div class="d-flex gap-2">
            <input type="date" class="form-control bg-dark text-white border-secondary form-control-sm" 
                   value="<?= $filter_date ?>" 
                   onchange="window.location.href='?module=transport/driver_manager_dashboard/index&view=schedule&date=' + this.value">
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-custom text-white">
            <thead class="text-secondary text-xs text-uppercase fw-bold">
                <tr>
                    <th>Ref</th>
                    <th>Status</th>
                    <th>Driver & Vehicle</th>
                    <th>Execution Route</th>
                    <th>Load Info</th>
                    <th class="text-end">Priority</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php foreach ($orders as $o): ?>
                <tr class="align-middle">
                    <td class="font-monospace text-secondary">#TO-<?= $o['id'] ?></td>
                    <td>
                        <?php 
                        $statusCls = 'status-pending';
                        if ($o['status'] === 'in_transit') $statusCls = 'status-active';
                        if ($o['status'] === 'delivered') $statusCls = 'status-approved';
                        ?>
                        <span class="status-badge <?= $statusCls ?>"><?= str_replace('_', ' ', $o['status']) ?></span>
                    </td>
                    <td>
                        <div class="fw-bold"><?= htmlspecialchars($o['driver_name']) ?></div>
                        <div class="text-xs text-info"><?= htmlspecialchars($o['plate_number']) ?> (<?= $o['model'] ?>)</div>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="text-xs text-secondary">From: <?= htmlspecialchars($o['origin_store']) ?></span>
                            <span class="fw-bold text-warning">To: <?= htmlspecialchars($o['site_name'] ?: $o['dest_store']) ?></span>
                        </div>
                    </td>
                    <td>
                         <div class="text-xs text-secondary italic"><?= htmlspecialchars($o['load_type']) ?></div>
                    </td>
                    <td class="text-end">
                        <span class="badge <?= $o['priority'] == 'urgent' ? 'bg-danger' : ($o['priority'] == 'emergency' ? 'bg-danger shadow-lg border border-white' : 'bg-dark border border-secondary') ?>">
                            <?= strtoupper($o['priority']) ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="6" class="text-center py-5 text-secondary">No deliveries scheduled for this date.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
