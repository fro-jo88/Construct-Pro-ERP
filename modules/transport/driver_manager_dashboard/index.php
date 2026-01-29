<?php
// modules/transport/driver_manager_dashboard/index.php

require_once __DIR__ . '/../../../includes/AuthManager.php';
require_once __DIR__ . '/../../../includes/Database.php';

AuthManager::requireRole(['DRIVER_MANAGER', 'SYSTEM_ADMIN']);

$db = Database::getInstance();
$user_id = $_SESSION['user_id'];
$view = $_GET['view'] ?? 'overview';

// --- KPI DATA ---
$activeTrips = $db->query("SELECT COUNT(*) FROM transport_orders WHERE status = 'in_transit'")->fetchColumn();
$pendingAssignments = $db->query("SELECT COUNT(*) FROM transport_orders WHERE status = 'pending_assignment'")->fetchColumn();

$today = date('Y-m-d');
$completedToday = $db->query("SELECT COUNT(*) FROM transport_orders WHERE status = 'delivered' AND DATE(delivered_at) = ?", [$today])->fetchColumn();

// Delayed trips: Pending or Assigned with requested_date < today
$delayedTrips = $db->query("SELECT COUNT(*) FROM transport_orders WHERE (status = 'pending_assignment' OR status = 'assigned') AND requested_date < ?", [$today])->fetchColumn();

?>

<style>
    :root { --driver-primary: #f59e0b; --driver-bg: #0f172a; --driver-accent: #3b82f6; }
    .driver-dashboard { padding: 25px; color: #f8fafc; }
    
    .kpi-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .kpi-card { background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(255,255,255,0.05); border-radius: 20px; padding: 25px; position: relative; overflow: hidden; }
    .kpi-card:hover { transform: translateY(-5px); transition: 0.3s; border-color: var(--driver-primary); }
    .kpi-card i { position: absolute; right: -15px; bottom: -15px; font-size: 5rem; opacity: 0.05; transform: rotate(-15deg); }
    .kpi-val { font-size: 2.2rem; font-weight: 800; margin: 5px 0; color: #fff; }
    .kpi-lbl { font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; font-weight: 700; letter-spacing: 1.5px; }

    .nav-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
    .nav-card { background: rgba(30, 41, 59, 0.4); border: 1px solid rgba(255,255,255,0.08); border-radius: 20px; padding: 25px; text-decoration: none; color: inherit; transition: 0.3s; }
    .nav-card:hover { background: rgba(245, 158, 11, 0.1); border-color: var(--driver-primary); box-shadow: 0 10px 20px rgba(0,0,0,0.2); }
    
    .glass-panel { background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(12px); border-radius: 24px; border: 1px solid rgba(255,255,255,0.05); padding: 30px; }
    .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
    .status-pending { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }
    .status-active { background: rgba(59, 130, 246, 0.1); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.2); }
</style>

<div class="driver-dashboard">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <span class="badge bg-warning text-dark mb-2 px-3 fw-bold">LOGISTICS COMMAND</span>
            <h1 class="fw-extrabold mb-1" style="font-size: 2.5rem; letter-spacing: -1.5px;">Transport Manager</h1>
            <p class="text-secondary mb-0 fw-medium">Scheduling, Driver Assignments & Live Trip Coordination.</p>
        </div>
        <div class="text-end">
            <div class="h2 fw-bold mb-0 text-white"><?= date('H:i') ?></div>
            <div class="text-secondary text-sm"><?= date('l, M d') ?></div>
        </div>
    </div>

    <!-- KPI ROW -->
    <div class="kpi-row">
        <div class="kpi-card">
            <i class="fas fa-truck-moving"></i>
            <div class="kpi-lbl">Active Trips</div>
            <div class="kpi-val text-primary"><?= $activeTrips ?></div>
            <div class="text-xs text-secondary">Currently on Route</div>
        </div>
        <div class="kpi-card" style="border-left: 4px solid #ef4444;">
            <i class="fas fa-clock"></i>
            <div class="kpi-lbl">Pending Assignments</div>
            <div class="kpi-val text-danger"><?= $pendingAssignments ?></div>
            <div class="text-xs text-secondary">Awaiting Driver & Vehicle</div>
        </div>
        <div class="kpi-card">
            <i class="fas fa-calendar-check"></i>
            <div class="kpi-lbl">Completed Today</div>
            <div class="kpi-val text-success"><?= $completedToday ?></div>
            <div class="text-xs text-secondary">Successfully Delivered</div>
        </div>
        <div class="kpi-card">
            <i class="fas fa-exclamation-circle"></i>
            <div class="kpi-lbl">Delayed Trips</div>
            <div class="kpi-val" style="color: #fca5a5;"><?= $delayedTrips ?></div>
            <div class="text-xs text-secondary">Past Requested Date</div>
        </div>
    </div>

    <?php if ($view === 'overview'): ?>
        <div class="nav-grid">
            <a href="?module=transport/driver_manager_dashboard/index&view=pending_requests" class="nav-card">
                <i class="fas fa-clipboard-list fa-2x text-warning mb-3"></i>
                <h5 class="fw-bold text-white">Pending Requests</h5>
                <p class="text-sm text-secondary mb-0">Authorized material issues that require transport scheduling.</p>
            </a>
            <a href="?module=transport/driver_manager_dashboard/index&view=assign_driver" class="nav-card">
                <i class="fas fa-user-plus fa-2x text-primary mb-3"></i>
                <h5 class="fw-bold text-white">Assign Drivers</h5>
                <p class="text-sm text-secondary mb-0">Match available drivers and vehicles to pending transport orders.</p>
            </a>
            <a href="?module=transport/driver_manager_dashboard/index&view=schedule" class="nav-card">
                <i class="fas fa-calendar-alt fa-2x text-success mb-3"></i>
                <h5 class="fw-bold text-white">Operations Calendar</h5>
                <p class="text-sm text-secondary mb-0">Full visibility of planned deliveries across sites and stores.</p>
            </a>
            <a href="?module=transport/driver_manager_dashboard/index&view=live_trips" class="nav-card">
                <i class="fas fa-map-marked-alt fa-2x text-info mb-3"></i>
                <h5 class="fw-bold text-white">Live Monitoring</h5>
                <p class="text-sm text-secondary mb-0">Real-time status updates from drivers as they navigate routes.</p>
            </a>
            <a href="?module=transport/driver_manager_dashboard/index&view=assets" class="nav-card">
                <i class="fas fa-id-card fa-2x mb-3 text-secondary"></i>
                <h5 class="fw-bold text-white">Drivers & Assets</h5>
                <p class="text-sm text-secondary mb-0">Manage driver profiles and track vehicle availability/maintenance.</p>
            </a>
        </div>

        <!-- RECENT ACTIVITY PREVIEW -->
        <div class="glass-panel mt-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0">Recent Trip Assignments</h5>
                <span class="text-secondary text-xs text-uppercase fw-bold letter-spacing-1">Last 5 Activities</span>
            </div>
            <div class="table-responsive">
                <table class="table text-white mb-0">
                    <thead class="text-secondary text-xs border-0">
                        <tr>
                            <th>ORDER ID</th>
                            <th>DRIVER</th>
                            <th>DESTINATION</th>
                            <th>STATUS</th>
                            <th class="text-end">TIME</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $recentTrips = $db->query("SELECT t.*, u.username as driver_name, s.site_name, st.store_name 
                                                   FROM transport_orders t 
                                                   LEFT JOIN users u ON t.driver_id = u.id 
                                                   LEFT JOIN sites s ON t.destination_site_id = s.id 
                                                   LEFT JOIN stores st ON t.destination_store_id = st.id 
                                                   ORDER BY t.created_at DESC LIMIT 5")->fetchAll();
                        
                        foreach ($recentTrips as $trip):
                        ?>
                        <tr class="align-middle">
                            <td class="font-monospace text-warning">#TO-<?= $trip['id'] ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($trip['driver_name'] ?? 'Unassigned') ?></td>
                            <td>
                                <div class="text-xs text-secondary"><?= $trip['destination_site_id'] ? 'Site' : 'Store' ?></div>
                                <div class="fw-medium"><?= htmlspecialchars($trip['site_name'] ?: ($trip['store_name'] ?: '--')) ?></div>
                            </td>
                            <td><span class="status-badge status-<?= $trip['status'] === 'pending_assignment' ? 'pending' : 'active' ?>"><?= str_replace('_', ' ', $trip['status']) ?></span></td>
                            <td class="text-end text-secondary text-xs"><?= date('H:i', strtotime($trip['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recentTrips)): ?>
                            <tr><td colspan="5" class="text-center py-4 text-secondary italic">No transport activity recorded.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php else: ?>
        <!-- SUB-MODULE LOADER -->
        <div class="module-view mt-4">
            <?php 
            $file = __DIR__ . '/' . $view . '.php';
            if (file_exists($file)) {
                include $file;
            } else {
                echo '<div class="glass-panel text-center py-5">
                        <i class="fas fa-shipping-fast fa-3x text-secondary mb-3 opacity-20"></i>
                        <h3>Route Mapping...</h3>
                        <p class="text-secondary">Scaling specialized ' . ucfirst($view) . ' control logic.</p>
                      </div>';
            }
            ?>
        </div>
    <?php endif; ?>
</div>
