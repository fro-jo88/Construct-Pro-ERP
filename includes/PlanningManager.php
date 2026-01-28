<?php
// includes/PlanningManager.php
require_once __DIR__ . '/Database.php';

class PlanningManager {
    public static function getSchedulesByProject($project_id) {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT s.*, sit.site_name FROM schedules s LEFT JOIN sites sit ON s.site_id = sit.id WHERE s.project_id = ? ORDER BY s.uploaded_at DESC");
        $stmt->execute([$project_id]);
        return $stmt->fetchAll();
    }

    public static function uploadSchedule($project_id, $site_id, $type, $file) {
        AuthManager::restrictDemoAction();
        $db = Database::getInstance();
        
        // Ensure uploads directory exists
        $upload_dir = __DIR__ . '/../uploads/schedules/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $filename = time() . '_' . basename($file['name']);
        $target_path = $upload_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            // Get current version for this type/site
            $stmt = $db->prepare("SELECT MAX(version) FROM schedules WHERE project_id = ? AND site_id = ? AND schedule_type = ?");
            $stmt->execute([$project_id, $site_id, $type]);
            $current_version = $stmt->fetchColumn() ?: 0;
            $new_version = $current_version + 1;

            $stmt = $db->prepare("INSERT INTO schedules (project_id, site_id, schedule_type, file_path, version) VALUES (?, ?, ?, ?, ?)");
            return $stmt->execute([$project_id, $site_id, $type, 'uploads/schedules/' . $filename, $new_version]);
        }
        return false;
    }
}
?>
