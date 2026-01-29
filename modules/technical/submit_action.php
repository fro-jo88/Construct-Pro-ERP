<?php
// modules/technical/submit_action.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/BidManager.php';
require_once __DIR__ . '/../../core/RoleGuard.php';

RoleGuard::requireAccess('technical/*');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bid_id = $_POST['bid_id'];
    $tech_data = [
        'compliance_score' => $_POST['compliance_score']
    ];
    
    try {
        BidManager::submitTechnical($bid_id, $tech_data, $_SESSION['user_id']);
        header("Location: main.php?module=technical/history&success=submitted");
    } catch (Exception $e) {
        die("Submission failed: " . $e->getMessage());
    }
}
?>
