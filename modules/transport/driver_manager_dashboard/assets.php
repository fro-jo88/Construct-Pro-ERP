<?php
// modules/transport/driver_manager_dashboard/assets.php

$db = Database::getInstance();

// 1. Fetch Drivers
$drivers = $db->query("SELECT u.*, 
                       (SELECT COUNT(*) FROM transport_orders WHERE driver_id = u.id AND status = 'in_transit') as currently_active
                       FROM users u 
                       JOIN roles r ON u.role_id = r.id 
                       WHERE r.role_name = 'DRIVER'")->fetchAll();

// 2. Fetch Vehicles
$vehicles = $db->query("SELECT * FROM vehicles")->fetchAll();

?>

<div class="row g-4">
    <!-- Drivers List -->
    <div class="col-lg-6">
        <div class="glass-panel">
            <h4 class="fw-bold mb-4">Driver Registry</h4>
            <div class="table-responsive">
                <table class="table table-custom text-white">
                    <thead class="text-secondary text-xs text-uppercase">
                        <tr>
                            <th>Driver Name</th>
                            <th>Status</th>
                            <th class="text-end">Trips Active</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        <?php foreach ($drivers as $d): ?>
                        <tr>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($d['username']) ?></div>
                                <div class="text-xs text-secondary"><?= $d['status'] ?></div>
                            </td>
                            <td>
                                <span class="badge bg-<?= $d['status'] === 'active' ? 'success' : 'danger' ?> bg-opacity-10 text-<?= $d['status'] === 'active' ? 'success' : 'danger' ?> border border-<?= $d['status'] === 'active' ? 'success' : 'danger' ?> border-opacity-30">
                                    <?= strtoupper($d['status']) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <span class="fw-bold"><?= $d['currently_active'] ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Vehicles List -->
    <div class="col-lg-6">
        <div class="glass-panel">
            <h4 class="fw-bold mb-4">Fleet Inventory</h4>
            <div class="table-responsive">
                <table class="table table-custom text-white">
                    <thead class="text-secondary text-xs text-uppercase">
                        <tr>
                            <th>Plate #</th>
                            <th>Vehicle Specs</th>
                            <th class="text-end">Operational Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        <?php foreach ($vehicles as $v): ?>
                        <tr>
                            <td class="fw-bold text-info font-monospace"><?= htmlspecialchars($v['plate_number']) ?></td>
                            <td>
                                <div class="fw-medium"><?= htmlspecialchars($v['model']) ?></div>
                                <div class="text-xs text-secondary"><?= htmlspecialchars($v['vehicle_type']) ?></div>
                            </td>
                            <td class="text-end">
                                <?php
                                $vStatusCls = 'bg-success';
                                if ($v['status'] === 'on_trip') $vStatusCls = 'bg-primary';
                                if ($v['status'] === 'maintenance') $vStatusCls = 'bg-danger';
                                ?>
                                <span class="badge <?= $vStatusCls ?> border border-white border-opacity-10">
                                    <?= strtoupper($v['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
