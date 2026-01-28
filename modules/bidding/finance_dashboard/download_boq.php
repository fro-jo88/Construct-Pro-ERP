<?php
// modules/bidding/finance_dashboard/download_boq.php
require_once __DIR__ . '/../../../includes/AuthManager.php';
require_once __DIR__ . '/../../../includes/TenderManager.php';

AuthManager::requireRole(['TENDER_FINANCE', 'FINANCE_BID_MANAGER', 'FINANCE_HEAD', 'GM']);

$bid_id = $_GET['id'] ?? null;
if (!$bid_id) die("Bid ID required.");

$tender = TenderManager::getTenderWithBids($bid_id);
if (!$tender) die("Tender not found.");

$fb = $tender['financial_bid'];
$project_name = $tender['title'];
$boq_json = $fb['boq_json'] ?? '';

$sanitized_name = preg_replace('/[^A-Za-z0-9]/', '_', $project_name);
$filename = "Financial_Bid_" . $sanitized_name . ".xlsx";
$filepath = __DIR__ . "/../../../uploads/bids/" . $filename;

// Ensure uploads dir exists
if (!file_exists(__DIR__ . "/../../../uploads/bids")) {
    mkdir(__DIR__ . "/../../../uploads/bids", 0777, true);
}

// Call Python script to generate
$python_script = realpath(__DIR__ . "/../../../scripts/generate_boq.py");
// Escape JSON for shell
$escaped_json = escapeshellarg($boq_json);
$cmd = "python \"$python_script\" \"" . addslashes($project_name) . "\" \"$filepath\" $escaped_json";
exec($cmd, $output, $return_var);

if ($return_var === 0 && file_exists($filepath)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="'.basename($filepath).'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filepath));
    readfile($filepath);
    
    // Optionally delete after download to save space, depends on policy
    // unlink($filepath); 
    exit;
} else {
    echo "Error generating BOQ. Command: $cmd <br> Output: " . implode("<br>", $output);
}
