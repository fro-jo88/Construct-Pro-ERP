<?php
// modules/transport/driver_manager_dashboard/save_assignment.php
require_once __DIR__ . '/../../../includes/AuthManager.php';
require_once __DIR__ . '/../../../includes/Database.php';

AuthManager::requireRole(['DRIVER_MANAGER', 'SYSTEM_ADMIN']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = Database::getInstance();
    $user_id = $_SESSION['user_id'];

    try {
        $db->beginTransaction();

        // 1. Create Transport Order
        $sql = "INSERT INTO transport_orders (
                    reference_type, reference_id, origin_store_id, 
                    destination_site_id, destination_store_id, 
                    requested_date, priority, driver_id, vehicle_id, 
                    status, assigned_at, load_type, notes
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'assigned', NOW(), ?, ?)";
        
        $dest_site = !empty($_POST['destination_site_id']) ? $_POST['destination_site_id'] : null;
        $dest_store = !empty($_POST['destination_store_id']) ? $_POST['destination_store_id'] : null;

        $db->prepare($sql)->execute([
            $_POST['reference_type'],
            $_POST['reference_id'],
            $_POST['origin_store_id'],
            $dest_site,
            $dest_store,
            $_POST['requested_date'],
            $_POST['priority'],
            $_POST['driver_id'],
            $_POST['vehicle_id'],
            $_POST['load_type'],
            $_POST['notes']
        ]);

        // 2. Update Vehicle Status
        $db->prepare("UPDATE vehicles SET status = 'on_trip' WHERE id = ?")->execute([$_POST['vehicle_id']]);

        // 3. Log initial status update
        $order_id = $db->lastInsertId();
        $db->prepare("INSERT INTO transport_status_updates (transport_order_id, status, updated_by, location_note) 
                      VALUES (?, 'assigned', ?, 'Assignment created by Manager')")
          ->execute([$order_id, $user_id]);

        $db->commit();
        header("Location: ../../../main.php?module=transport/driver_manager_dashboard/index&view=schedule&success=1");
    } catch (Exception $e) {
        $db->rollBack();
        die("Error saving assignment: " . $e->getMessage());
    }
}
?>
