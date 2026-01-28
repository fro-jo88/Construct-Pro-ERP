<?php
// includes/LogisticsManager.php
require_once __DIR__ . '/Database.php';

class LogisticsManager {
    public static function getAllVehicles() {
        $db = Database::getInstance();
        return $db->query("SELECT * FROM vehicles")->fetchAll();
    }

    public static function getTripLogs() {
        $db = Database::getInstance();
        return $db->query("SELECT tl.*, v.plate_number, u.username as driver_name, p.project_name 
                            FROM trip_logs tl 
                            JOIN vehicles v ON tl.vehicle_id = v.id 
                            JOIN users u ON tl.driver_id = u.id 
                            JOIN projects p ON tl.project_id = p.id 
                            ORDER BY tl.start_time DESC")->fetchAll();
    }

    public static function startTrip($vehicle_id, $driver_id, $project_id, $kms_start) {
        AuthManager::restrictDemoAction();
        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("INSERT INTO trip_logs (vehicle_id, driver_id, project_id, start_time, kms_start) VALUES (?, ?, ?, NOW(), ?)");
            $stmt->execute([$vehicle_id, $driver_id, $project_id, $kms_start]);
            
            $stmt = $db->prepare("UPDATE vehicles SET status = 'on_trip' WHERE id = ?");
            $stmt->execute([$vehicle_id]);
            
            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
?>
