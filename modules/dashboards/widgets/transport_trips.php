<?php
// modules/dashboards/widgets/transport_trips.php
require_once __DIR__ . '/../../../includes/Database.php';

$db = Database::getInstance();
$trips = [];

try {
    $trips = $db->query("SELECT t.*, v.plate_number, u.username as driver 
                       FROM transport_trips t 
                       JOIN vehicles v ON t.vehicle_id = v.id 
                       JOIN users u ON t.driver_id = u.id 
                       WHERE t.status != 'completed' ORDER BY t.created_at DESC LIMIT 5")->fetchAll();
} catch (Exception $e) { /* Ignore */ }

?>
<div class="widget glass-card">
    <div class="widget-header">
        <h3><i class="fas fa-route"></i> Active Trips</h3>
    </div>
    <div class="widget-content">
        <?php if (empty($trips)): ?>
            <p class="text-dim">No active transport missions.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Plate</th>
                        <th>Route</th>
                        <th>Driver</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($trips as $t): ?>
                        <tr>
                            <td><?= $t['plate_number'] ?></td>
                            <td><?= htmlspecialchars($t['destination']) ?></td>
                            <td><?= htmlspecialchars($t['driver']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
