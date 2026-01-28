<?php
// includes/ForemanManager.php
require_once __DIR__ . '/Database.php';

class ForemanManager {
    
    // --- SITE ISOLATION ---
    public static function getAssignedSite($foreman_id) {
        $db = Database::getInstance();
        // Strict: ONE site per foreman for this dashboard
        $stmt = $db->prepare("SELECT * FROM sites WHERE foreman_id = ? LIMIT 1");
        $stmt->execute([$foreman_id]);
        return $stmt->fetch();
    }

    public static function getSiteStats($site_id) {
        $db = Database::getInstance();
        $stats = [];
        // Pending Requests
        $stats['pending_materials'] = $db->query("SELECT COUNT(*) FROM material_requests WHERE site_id = $site_id AND status = 'pending'")->fetchColumn();
        $stats['pending_labor'] = $db->query("SELECT COUNT(*) FROM labor_requests WHERE site_id = $site_id AND status = 'pending'")->fetchColumn();
        // Today's Report Status
        $today = date('Y-m-d');
        $stats['report_submitted'] = $db->query("SELECT COUNT(*) FROM daily_site_reports WHERE site_id = $site_id AND report_date = '$today'")->fetchColumn() > 0;
        return $stats;
    }

    // --- DAILY OPERATIONS ---
    public static function submitDailyReport($site_id, $foreman_id, $data) {
        $db = Database::getInstance();
        
        // Fetch planned work snapshot for the record
        $planned = self::getPlannedWorkSnapshot($site_id);

        $stmt = $db->prepare("INSERT INTO daily_site_reports (
            site_id, foreman_id, report_date, 
            planned_work, actual_work, progress_percent, 
            labor_count, equipment_used, material_used, 
            blockers, safety_notes, weather_condition, status
        ) VALUES (?, ?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?, ?, 'submitted')");

        // Process Dynamic Material Rows
        $materials = [];
        if (isset($data['materials']['item']) && is_array($data['materials']['item'])) {
            for ($i = 0; $i < count($data['materials']['item']); $i++) {
                if (!empty($data['materials']['item'][$i])) {
                    $materials[] = [
                        'item' => $data['materials']['item'][$i],
                        'qty'  => $data['materials']['qty'][$i]
                    ];
                }
            }
        }
        $materials_json = json_encode($materials);

        return $stmt->execute([
            $site_id, 
            $foreman_id, 
            $planned,
            $data['actual_work'], 
            $data['progress_percent'],
            $data['labor_count'],
            $data['equipment_used'],
            $materials_json,
            $data['blockers'], 
            $data['safety_notes'],
            $data['weather']
        ]);
    }

    public static function getPlannedWorkSnapshot($site_id) {
        // Placeholder for Planning Module Integration
        // Ideally fetches from weekly_plans table
        $db = Database::getInstance();
        $today = date('Y-m-d');
        $stmt = $db->prepare("SELECT goals FROM weekly_plans WHERE site_id = ? AND ? BETWEEN week_start_date AND week_end_date LIMIT 1");
        $stmt->execute([$site_id, $today]);
        $plan = $stmt->fetch();
        return $plan ? $plan['goals'] : "No weekly plan found.";
    }

    public static function getReportHistory($site_id) {
        $db = Database::getInstance();
        return $db->query("SELECT * FROM daily_site_reports WHERE site_id = $site_id ORDER BY report_date DESC LIMIT 30")->fetchAll();
    }

    public static function getAllReports() {
        $db = Database::getInstance();
        return $db->query("
            SELECT r.*, s.site_name, u.username as foreman_name 
            FROM daily_site_reports r 
            JOIN sites s ON r.site_id = s.id 
            JOIN users u ON r.foreman_id = u.id 
            ORDER BY r.report_date DESC, s.site_name ASC
            LIMIT 50
        ")->fetchAll();
    }

    // --- REQUESTS & INCIDENTS ---
    public static function createMaterialRequest($site_id, $user_id, $item, $qty, $priority, $reason) {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO material_requests (site_id, requested_by, item_name, quantity, priority, reason) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$site_id, $user_id, $item, $qty, $priority, $reason]);
    }

    public static function reportIncident($site_id, $user_id, $title, $description, $severity) {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO site_incidents (site_id, reported_by, title, description, severity, status) VALUES (?, ?, ?, ?, ?, 'open')");
        return $stmt->execute([$site_id, $user_id, $title, $description, $severity]);
    }

    public static function getRecentActivity($site_id) {
         $db = Database::getInstance();
         // Combine recent reports and incidents for a feed
         // Simplified: Just last 5 daily reports
         return $db->query("SELECT * FROM daily_site_reports WHERE site_id = $site_id ORDER BY report_date DESC LIMIT 5")->fetchAll();
    }
}
?>
