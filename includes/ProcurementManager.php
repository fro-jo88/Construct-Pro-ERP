<?php
// includes/ProcurementManager.php
require_once __DIR__ . '/Database.php';

class ProcurementManager {
    public static function getAllVendors() {
        $db = Database::getInstance();
        return $db->query("SELECT * FROM vendors")->fetchAll();
    }

    public static function createPurchaseRequest($project_id, $site_id, $items) {
        AuthManager::restrictDemoAction();
        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("INSERT INTO purchase_requests (project_id, site_id, requested_by, status) VALUES (?, ?, ?, 'pending')");
            $stmt->execute([$project_id, $site_id, $_SESSION['user_id']]);
            $pr_id = $db->lastInsertId();

            // items would be stored in a linked table pr_items (needs to be created)
            // For now, keeping it simple as per schema rules
            $db->commit();
            return $pr_id;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function getAllPurchaseRequests() {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT pr.*, p.project_name, s.site_name, u.username as requester 
                            FROM purchase_requests pr 
                            JOIN projects p ON pr.project_id = p.id 
                            LEFT JOIN sites s ON pr.site_id = s.id 
                            JOIN users u ON pr.requested_by = u.id 
                            ORDER BY pr.created_at DESC");
        return $stmt->fetchAll();
    }

    public static function approvePR($pr_id, $role) {
        AuthManager::restrictDemoAction();
        $db = Database::getInstance();
        $status = ($role === 'Finance') ? 'finance_approved' : 'gm_approved';
        $stmt = $db->prepare("UPDATE purchase_requests SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $pr_id]);
    }
}
?>
