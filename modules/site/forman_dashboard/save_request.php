<?php
// modules/site/forman_dashboard/save_request.php
require_once __DIR__ . '/../../../includes/AuthManager.php';
require_once __DIR__ . '/../../../includes/Database.php';

AuthManager::requireRole(['FORMAN', 'SYSTEM_ADMIN']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = Database::getInstance();
    $user_id = $_SESSION['user_id'];

    try {
        $sql = "INSERT INTO material_requests (site_id, requested_by, item_name, quantity, priority, reason, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'pending')";
        
        $db->prepare($sql)->execute([
            $_POST['site_id'],
            $user_id,
            $_POST['item_name'],
            $_POST['quantity'],
            $_POST['priority'],
            $_POST['reason']
        ]);
        
        header("Location: ../../../main.php?module=site/forman_dashboard/index&view=materials&success=1");
    } catch (Exception $e) {
        die("Error saving material request: " . $e->getMessage());
    }
}
?>
