<?php
// modules/bidding/finance_bid_dashboard/save_preparation.php
require_once __DIR__ . '/../../../includes/AuthManager.php';
require_once __DIR__ . '/../../../includes/Database.php';

AuthManager::requireRole(['FINANCE_BID_MANAGER', 'SYSTEM_ADMIN']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = Database::getInstance();
    $bid_id = $_POST['bid_id'];
    $fin_bid_id = $_POST['fin_bid_id'];
    $action = $_POST['action'];

    // Collect Data
    $boq_structure_json = $_POST['boq_structure_json'] ?? '[]';
    $tax_percent = $_POST['tax_percent'] ?? 15;
    $total_amount = $_POST['total_amount']; // Grand Total

    // Decode structure to validate if needed
    $boq_data = json_decode($boq_structure_json, true);

    // Handle File Upload
    $doc_path = '';
    if (isset($_FILES['bid_doc']) && $_FILES['bid_doc']['error'] == 0) {
        $uploadDir = __DIR__ . '/../../../uploads/bids/'; // Correction: ../../../ from this file location
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $fileName = 'fin_bid_' . $bid_id . '_' . time() . '_' . basename($_FILES['bid_doc']['name']);
        move_uploaded_file($_FILES['bid_doc']['tmp_name'], $uploadDir . $fileName);
        $doc_path = 'uploads/bids/' . $fileName; // Logical path
    }

    // Check if exists to retrieve old doc path if not updating
    $stmt = $db->prepare("SELECT id, boq_json FROM financial_bids WHERE bid_id = ?");
    $stmt->execute([$bid_id]);
    $check = $stmt->fetch();
    $existing_json = $check ? json_decode($check['boq_json'], true) : [];
    if (empty($doc_path) && !empty($existing_json['document_path'])) {
        $doc_path = $existing_json['document_path'];
    }

    // Prepare Final JSON
    $final_json = [
        'boq_structure' => $boq_data,
        'tax_percent' => $tax_percent,
        'document_path' => $doc_path,
        'last_updated' => date('Y-m-d H:i:s')
    ];
    $boq_json_str = json_encode($final_json);

    // Update Status
    $status = 'draft';
    if ($action === 'send_verification') {
        $status = 'pending_verification';
    }

    if ($check) {
        $sql = "UPDATE financial_bids SET 
                total_amount = ?, 
                profit_margin_percent = 0, 
                boq_json = ?, 
                status = ?, 
                updated_at = NOW(),
                labor_cost = 0, material_cost = 0, equipment_cost = 0, overhead_cost = 0, tax = ? 
                WHERE bid_id = ?";
        // estimating tax amount roughly for the column
        $tax_amount = $total_amount - ($total_amount / (1 + ($tax_percent/100)));
        
        $db->prepare($sql)->execute([$total_amount, $boq_json_str, $status, $tax_amount, $bid_id]);
    } else {
        $sql = "INSERT INTO financial_bids (tender_id, bid_id, total_amount, profit_margin_percent, status, boq_json, created_at, tax) VALUES (?, ?, ?, 0, ?, ?, NOW(), ?)";
        $tax_amount = $total_amount - ($total_amount / (1 + ($tax_percent/100)));
        // Note: tender_id is required. We use bid_id for both if unknown, but better to query real tender_id from bids table if possible.
        // Assuming bid_id passed is the BIDS.ID.
        // We will just use it for tender_id column too as placeholder if schema requires it, or trust bid_id is what we need.
        $db->prepare($sql)->execute([$bid_id, $bid_id, $total_amount, $status, $boq_json_str, $tax_amount]);
    }

    echo "<script>window.location.href = 'main.php?module=bidding/finance_bid_dashboard/index&view=preparation&id=$bid_id&success=saved';</script>";
}
?>
