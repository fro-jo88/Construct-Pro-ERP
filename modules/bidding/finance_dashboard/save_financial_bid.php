<?php
// modules/bidding/finance_dashboard/save_financial_bid.php
require_once __DIR__ . '/../../../includes/AuthManager.php';
require_once __DIR__ . '/../../../includes/BidManager.php';

AuthManager::requireRole(['TENDER_FINANCE', 'FINANCE_HEAD', 'GM']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bid_id = $_POST['bid_id'];
    $data = [
        'labor_cost' => $_POST['labor_cost'],
        'material_cost' => $_POST['material_cost'],
        'equipment_cost' => $_POST['equipment_cost'],
        'overhead_cost' => $_POST['overhead_cost'],
        'tax' => $_POST['tax'],
        'profit_margin' => $_POST['profit_margin'],
        'total_amount' => $_POST['total_amount']
    ];

    try {
        BidManager::submitFinancialBreakdown($bid_id, $data, $_SESSION['user_id']);
        
        $success = ($_POST['action'] === 'submit_gm') ? 'submitted' : 'saved';
        header("Location: main.php?module=bidding/finance_dashboard&success=$success");
    } catch (Exception $e) {
        die("Error saving bid: " . $e->getMessage());
    }
}
