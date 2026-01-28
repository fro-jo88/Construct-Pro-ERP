<?php
// modules/bidding/finance_dashboard/save_financial_bid.php
require_once __DIR__ . '/../../../includes/AuthManager.php';
require_once __DIR__ . '/../../../includes/BidManager.php';

AuthManager::requireRole(['TENDER_FINANCE', 'FINANCE_BID_MANAGER', 'FINANCE_HEAD', 'GM']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bid_id = $_POST['bid_id'];
    $boq_json = $_POST['boq_json'] ?? '';
    $boq = json_decode($boq_json, true);

    // Aggregate values for the legacy columns
    // In a real system, we'd map Sub Structure A items to specific buckets.
    // Here we'll map the categories to approximate buckets.
    $data = [
        'labor_cost' => ($boq['totals']['sub'] ?? 0) * 0.4, // Estimate
        'material_cost' => ($boq['totals']['super'] ?? 0) * 0.6, // Estimate
        'equipment_cost' => 0,
        'overhead_cost' => 0,
        'tax' => $boq['totals']['vat'] ?? 0,
        'profit_margin' => 0, // Already built into the BOQ rates usually
        'total_amount' => $boq['totals']['grand'] ?? 0,
        'boq_json' => $boq_json
    ];

    try {
        $db = Database::getInstance();
        
        // 1. Update financial_bids
        $stmt = $db->prepare("UPDATE financial_bids SET 
            total_amount = ?,
            tax = ?,
            boq_json = ?,
            status = 'submitted',
            updated_at = NOW()
            WHERE bid_id = ?");
        
        $stmt->execute([
            $data['total_amount'],
            $data['tax'],
            $data['boq_json'],
            $bid_id
        ]);

        // 2. Advance Main Bid Status
        $status = ($_POST['action'] === 'submit_gm') ? 'FINANCE_FINAL_REVIEW' : 'GM_PRE_APPROVED';
        $stmt = $db->prepare("UPDATE bids SET status = ? WHERE id = ?");
        $stmt->execute([$status, $bid_id]);

        $success = ($_POST['action'] === 'submit_gm') ? 'submitted' : 'saved';
        header("Location: main.php?module=bidding/finance_dashboard&success=$success");
    } catch (Exception $e) {
        die("Error saving bid: " . $e->getMessage());
    }
}
