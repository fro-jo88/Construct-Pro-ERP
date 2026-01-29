<?php
// modules/transport/driver_manager_dashboard/assign_driver.php

$db = Database::getInstance();

$ref_id = $_GET['ref_id'] ?? null;
$type = $_GET['type'] ?? 'material_request';

// Fetch Request Details for Context
$request = null;
if ($ref_id) {
    if ($type === 'material_request') {
        $request = $db->query("SELECT mr.*, s.site_name, st.store_name as origin_store, st.id as origin_store_id, s.id as destination_site_id, p.product_name, p.unit
                                FROM material_requests mr
                                JOIN sites s ON mr.site_id = s.id
                                JOIN stores st ON mr.fulfilling_store_id = st.id
                                JOIN products p ON mr.item_name = p.product_name
                                WHERE mr.id = ?", [$ref_id])->fetch();
    } else {
        $request = $db->query("SELECT st.*, s1.store_name as origin_store, s2.store_name as destination_store, s1.id as origin_store_id, s2.id as destination_store_id, p.product_name, p.unit
                                FROM stock_transfers st
                                JOIN stores s1 ON st.from_store_id = s1.id
                                JOIN stores s2 ON st.to_store_id = s2.id
                                JOIN products p ON st.material_id = p.id
                                WHERE st.id = ?", [$ref_id])->fetch();
    }
}

// Fetch Drivers (Users with role DRIVER)
$drivers = $db->query("SELECT u.* FROM users u 
                       JOIN roles r ON u.role_id = r.id 
                       WHERE r.role_name = 'DRIVER' 
                       AND u.status = 'active'")->fetchAll();

// Fetch Available Vehicles
$vehicles = $db->query("SELECT * FROM vehicles WHERE status = 'available'")->fetchAll();

?>

<?php if (!$request && $ref_id): ?>
    <div class="alert alert-danger">Request Reference Not Found.</div>
<?php elseif ($ref_id): ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Assignment: #<?= ($type === 'material_request' ? 'MR-' : 'TR-') . $ref_id ?></h4>
        <a href="?module=transport/driver_manager_dashboard/index&view=pending_requests" class="btn btn-outline-secondary btn-sm">Cancel</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="glass-panel h-100">
                <h6 class="text-warning fw-bold mb-3 text-uppercase letter-spacing-1">Trip Breakdown</h6>
                <div class="bg-dark bg-opacity-30 p-4 rounded-3 border border-secondary border-opacity-20 mb-4">
                    <div class="mb-3 border-bottom border-secondary border-opacity-10 pb-3">
                        <label class="text-xs text-secondary d-block mb-1">Origin Location</label>
                        <div class="fw-bold h5 mb-0 text-white"><i class="fas fa-warehouse me-2 text-primary"></i> <?= htmlspecialchars($request['origin_store']) ?></div>
                    </div>
                    <div class="mb-3 border-bottom border-secondary border-opacity-10 pb-3">
                        <label class="text-xs text-secondary d-block mb-1">Destination</label>
                        <div class="fw-bold h5 mb-0 text-warning"><i class="fas fa-map-marker-alt me-2"></i> <?= htmlspecialchars($request['site_name'] ?? $request['destination_store']) ?></div>
                    </div>
                    <div>
                        <label class="text-xs text-secondary d-block mb-1">Materials / Load</label>
                        <div class="fw-medium"><?= htmlspecialchars($request['product_name']) ?></div>
                        <div class="text-xs text-secondary"><?= $request['quantity'] ?> <?= $request['unit'] ?></div>
                    </div>
                </div>
                
                <div class="p-3 bg-primary bg-opacity-10 rounded-3 border border-primary border-opacity-20">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle text-primary me-3 fa-lg"></i>
                        <div class="text-xs text-secondary">
                            Ensure the driver is equipped with proper PPE and the vehicle load limit is not exceeded.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="glass-panel">
                <form method="POST" action="modules/transport/driver_manager_dashboard/save_assignment.php">
                    <input type="hidden" name="reference_id" value="<?= $ref_id ?>">
                    <input type="hidden" name="reference_type" value="<?= $type ?>">
                    <input type="hidden" name="origin_store_id" value="<?= $request['origin_store_id'] ?>">
                    <input type="hidden" name="destination_site_id" value="<?= $request['destination_site_id'] ?? '' ?>">
                    <input type="hidden" name="destination_store_id" value="<?= $request['destination_store_id'] ?? '' ?>">
                    <input type="hidden" name="load_type" value="<?= htmlspecialchars($request['product_name'] . ' (' . $request['quantity'] . ' ' . $request['unit'] . ')') ?>">

                    <div class="mb-4">
                        <label class="form-label text-secondary fw-bold text-xs text-uppercase">1. Select Driver</label>
                        <select class="form-select bg-dark text-white border-secondary h-auto py-3" name="driver_id" required>
                            <option value="">-- Select Available Driver --</option>
                            <?php foreach ($drivers as $d): ?>
                                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['username']) ?> (Active)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-secondary fw-bold text-xs text-uppercase">2. Select Vehicle</label>
                        <select class="form-select bg-dark text-white border-secondary h-auto py-3" name="vehicle_id" required>
                            <option value="">-- Select Available Vehicle --</option>
                            <?php foreach ($vehicles as $v): ?>
                                <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['plate_number']) ?> - <?= htmlspecialchars($v['model']) ?> (<?= $v['vehicle_type'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-secondary fw-bold text-xs text-uppercase">3. Target Delivery Date</label>
                            <input type="date" class="form-control bg-dark text-white border-secondary py-3" name="requested_date" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary fw-bold text-xs text-uppercase">4. Priority Level</label>
                            <select class="form-select bg-dark text-white border-secondary py-3" name="priority">
                                <option value="normal">Normal</option>
                                <option value="urgent">Urgent</option>
                                <option value="emergency">Emergency</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="form-label text-secondary fw-bold text-xs text-uppercase">5. Additional Instructions</label>
                        <textarea class="form-control bg-dark text-white border-secondary" name="notes" rows="3" placeholder="Route details, contact person at site, etc..."></textarea>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-warning fw-bold text-dark py-3 rounded-xl shadow-lg">
                            <i class="fas fa-calendar-check me-2"></i> Confirm Assignment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- List View if no specific ID provides -->
    <div class="glass-panel text-center py-5">
        <i class="fas fa-truck-loading fa-4x text-secondary mb-4 opacity-10"></i>
        <h3>Select a request from the list first</h3>
        <p class="text-secondary">Navigate to 'Pending Requests' to choose a delivery for assignment.</p>
        <a href="?module=transport/driver_manager_dashboard/index&view=pending_requests" class="btn btn-primary mt-3">Go to Requests</a>
    </div>
<?php endif; ?>
