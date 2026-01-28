<?php
// includes/AuditManager.php
require_once __DIR__ . '/Database.php';

class AuditManager {
    public static function getAuditReports($project_id = null) {
        $db = Database::getInstance();
        $sql = "SELECT ca.*, p.project_name, s.site_name, u.username as auditor_name 
                FROM construction_audits ca 
                JOIN projects p ON ca.project_id = p.id 
                LEFT JOIN sites s ON ca.site_id = s.id 
                JOIN users u ON ca.audited_by = u.id";
        
        if ($project_id) {
            $stmt = $db->prepare($sql . " WHERE ca.project_id = ? ORDER BY ca.audit_date DESC");
            $stmt->execute([$project_id]);
        } else {
            $stmt = $db->query($sql . " ORDER BY ca.audit_date DESC");
        }
        return $stmt->fetchAll();
    }

    public static function createAuditReport($data) {
        AuthManager::restrictDemoAction();
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO construction_audits (project_id, site_id, audit_date, findings, material_variance, progress_variance, audited_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['project_id'],
            $data['site_id'],
            $data['audit_date'],
            $data['findings'],
            $data['material_variance'],
            $data['progress_variance'],
            $_SESSION['user_id']
        ]);
    }

    public static function getVarianceStats() {
        $db = Database::getInstance();
        // Aggregated variance stats for the Audit Dashboard
        return $db->query("SELECT AVG(material_variance) as avg_mat_variance, AVG(progress_variance) as avg_prog_variance FROM construction_audits")->fetch();
    }
}
?>
