<?php
// modules/bidding/download.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/BidManager.php';
require_once __DIR__ . '/../../core/Logger.php';

if (!AuthManager::isLoggedIn()) {
    die("Access Denied: Authentication Required.");
}

$file_id = $_GET['file_id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$file_id) {
    die("Error: File not specified.");
}

$db = Database::getInstance();
$stmt = $db->prepare("SELECT * FROM bid_files WHERE id = ?");
$stmt->execute([$file_id]);
$file = $stmt->fetch();

if (!$file) {
    die("Error: File not found.");
}

$bid_id = $file['bid_id'];

// --- SECURITY CHECK ---
if (!BidManager::canUserViewBidFile($user_id, $bid_id)) {
    Logger::error('Security', "Unauthorized file access attempt", ['user_id' => $user_id, 'file_id' => $file_id, 'bid_id' => $bid_id]);
    die("Access Denied: You do not have permission to view this document.");
}

$file_path = __DIR__ . '/../../' . $file['file_path'];

// Path Traversal Security
$real_path = realpath($file_path);
$base_uploads = realpath(__DIR__ . '/../../uploads/');
if ($real_path === false || strpos($real_path, $base_uploads) !== 0) {
    die("Security Error: Invalid file path.");
}

if (!file_exists($file_path)) {
    die("Error: Physical file missing on server.");
}

// --- LOGGING ---
Logger::info('Bidding', "User downloaded bid file", ['user_id' => $user_id, 'file_id' => $file_id, 'bid_id' => $bid_id]);

// --- SERVE FILE ---
$mime_types = [
    'pdf'  => 'application/pdf',
    'doc'  => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'xls'  => 'application/vnd.ms-excel',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
];

$ext = strtolower($file['file_type']);
$content_type = $mime_types[$ext] ?? 'application/octet-stream';

header('Content-Description: File Transfer');
header('Content-Type: ' . $content_type);
header('Content-Disposition: attachment; filename="' . basename($file['file_name']) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));

readfile($file_path);
exit;
