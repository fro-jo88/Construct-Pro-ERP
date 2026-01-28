<?php
// includes/ProjectManager.php
require_once __DIR__ . '/Database.php';

class ProjectManager {
    public static function getAllProjects() {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT p.*, b.tender_no FROM projects p LEFT JOIN bids b ON p.tender_id = b.id");
        return $stmt->fetchAll();
    }

    public static function createProjectFromTender($tender_id, $project_code, $project_name, $start_date, $end_date, $budget) {
        AuthManager::restrictDemoAction();
        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            // 1. Create Project
            $stmt = $db->prepare("INSERT INTO projects (tender_id, project_code, project_name, start_date, end_date, budget, status) VALUES (?, ?, ?, ?, ?, ?, 'planned')");
            $stmt->execute([$tender_id, $project_code, $project_name, $start_date, $end_date, $budget]);
            $project_id = $db->lastInsertId();

            // 2. Update Bid Status to 'WON' if not already
            $stmt = $db->prepare("UPDATE bids SET status = 'WON' WHERE id = ?");
            $stmt->execute([$tender_id]);

            // 3. Create Initial Budget Record
            $stmt = $db->prepare("INSERT INTO budgets (project_id, total_amount, remaining_amount) VALUES (?, ?, ?)");
            $stmt->execute([$project_id, $budget, $budget]);

            $db->commit();
            return $project_id;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function getProjectDetails($project_id) {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT p.*, b.tender_no, bgt.total_amount, bgt.spent_amount, bgt.remaining_amount 
                            FROM projects p 
                            LEFT JOIN bids b ON p.tender_id = b.id 
                            LEFT JOIN budgets bgt ON p.id = bgt.project_id 
                            WHERE p.id = ?");
        $stmt->execute([$project_id]);
        return $stmt->fetch();
    }
}
?>
