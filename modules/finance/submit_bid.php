<?php
// modules/finance/submit_bid.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/BidManager.php';
require_once __DIR__ . '/../../core/RoleGuard.php';

RoleGuard::requireAccess('finance/*');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bid_id = $_POST['bid_id'];
    $finance_data = [
        'total_amount' => $_POST['total_amount']
    ];
    
    try {
        BidManager::submitFinancial($bid_id, $finance_data, $_SESSION['user_id']);
        header("Location: main.php?module=bidding/finance_bid_dashboard/index&success=submitted");
    } catch (Exception $e) {
        die("Submission failed: " . $e->getMessage());
    }
}
?>
