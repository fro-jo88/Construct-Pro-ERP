<?php
// includes/SiteManager.php
require_once __DIR__ . '/Database.php';

class SiteManager {
    public static function getSitesByProject($project_id) {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT s.*, u.username as foreman_name FROM sites s LEFT JOIN users u ON s.foreman_id = u.id WHERE s.project_id = ?");
        $stmt->execute([$project_id]);
        return $stmt->fetchAll();
    }

    public static function createSite($project_id, $site_name, $location, $foreman_id) {
        AuthManager::restrictDemoAction();
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO sites (project_id, site_name, location, foreman_id) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$project_id, $site_name, $location, $foreman_id]);
    }

    public static function getSiteDetails($site_id) {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT s.*, p.project_name, u.username as foreman_name 
                            FROM sites s 
                            JOIN projects p ON s.project_id = p.id 
                            LEFT JOIN users u ON s.foreman_id = u.id 
                            WHERE s.id = ?");
        $stmt->execute([$site_id]);
        return $stmt->fetch();
    }
    public static function getAllSites() {
        $db = Database::getInstance();
        return $db->query("SELECT * FROM sites ORDER BY site_name ASC")->fetchAll();
    }
}
?>
