<?php
// modules/transport/driver_manager_dashboard/live_trips.php

$db = Database::getInstance();

// Fetch trips that are in_transit or assigned
$liveTrips = $db->query("SELECT t.*, u.username as driver_name, v.plate_number, 
                                s.site_name, st.store_name as dest_store,
                                (SELECT location_note FROM transport_status_updates 
                                 WHERE transport_order_id = t.id ORDER BY update_time DESC LIMIT 1) as last_update
                         FROM transport_orders t
                         JOIN users u ON t.driver_id = u.id
                         JOIN vehicles v ON t.vehicle_id = v.id
                         LEFT JOIN sites s ON t.destination_site_id = s.id
                         LEFT JOIN stores st ON t.destination_store_id = st.id
                         WHERE t.status IN ('assigned', 'in_transit')
                         ORDER BY t.priority DESC, t.updated_at DESC")->fetchAll();

?>

<div class="glass-panel">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="fas fa-satellite-dish text-info me-2"></i> Live Fleet Tracking</h4>
        <span class="badge bg-info text-dark rounded-pill px-3"><?= count($liveTrips) ?> Active Trips</span>
    </div>

    <div class="row g-4">
        <?php foreach ($liveTrips as $trip): ?>
        <div class="col-md-6 col-lg-4">
            <div class="p-4 rounded-3 border border-secondary border-opacity-20 bg-dark bg-opacity-20 position-relative">
                <?php if ($trip['priority'] === 'emergency'): ?>
                    <div class="position-absolute top-0 end-0 p-2">
                        <span class="badge bg-danger pulse">EMERGENCY</span>
                    </div>
                <?php endif; ?>

                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle bg-primary bg-opacity-20 p-3 me-3">
                        <i class="fas fa-truck text-primary"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-white"><?= htmlspecialchars($trip['driver_name']) ?></h6>
                        <span class="text-xs text-secondary"><?= htmlspecialchars($trip['plate_number']) ?></span>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="text-xs text-secondary text-uppercase fw-bold mb-2">Current Status</div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="status-badge <?= $trip['status'] === 'in_transit' ? 'status-active' : 'status-pending' ?>">
                            <?= strtoupper($trip['status']) ?>
                        </span>
                        <?php if ($trip['status'] === 'in_transit'): ?>
                            <div class="spinner-grow spinner-grow-sm text-info" role="status"></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="text-xs text-secondary text-uppercase fw-bold mb-1">Last Update</div>
                    <p class="text-sm mb-0 italic">"<?= htmlspecialchars($trip['last_update'] ?: 'Trip assigned') ?>"</p>
                    <span class="text-xs text-secondary"><?= date('H:i', strtotime($trip['updated_at'])) ?></span>
                </div>

                <div class="mt-auto pt-3 border-top border-secondary border-opacity-10 d-flex justify-content-between align-items-center">
                    <div class="text-xs fw-bold text-warning">TO: <?= htmlspecialchars($trip['site_name'] ?: $trip['dest_store']) ?></div>
                    <button class="btn btn-xs btn-outline-info">Route Info</button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if (empty($liveTrips)): ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-map-marked fa-4x text-secondary mb-3 opacity-10"></i>
                <h4 class="text-secondary">No Live Trips</h4>
                <p class="text-muted">Drivers are currently idle or trips have haven't started yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.pulse {
    animation: pulse-red 2s infinite;
    box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
}
@keyframes pulse-red {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}
</style>
