<?php
// modules/site/forman_dashboard/save_report.php
require_once __DIR__ . '/../../../includes/AuthManager.php';
require_once __DIR__ . '/../../../includes/Database.php';

AuthManager::requireRole(['FORMAN', 'SYSTEM_ADMIN']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = Database::getInstance();
    $report_id = $_POST['report_id'] ?? null;
    $site_id = $_POST['site_id'];
    $user_id = $_SESSION['user_id'];
    $status = $_POST['status']; // 'draft' or 'submitted_to_gm'

    // Validation: Check if locked
    if ($report_id) {
        $existing = $db->query("SELECT status FROM daily_site_reports WHERE id = ?", [$report_id])->fetch();
        if ($existing && $existing['status'] !== 'draft') {
            die("Error: Submitted reports are locked and cannot be edited.");
        }
    }

    $params = [
        $site_id,
        $user_id,
        $_POST['report_date'],
        $_POST['labor_count'],
        $_POST['progress_percent'],
        $_POST['actual_work'],
        $_POST['equipment_used'],
        $_POST['material_used'], // Store as string for simplicity or JSON if needed
        $_POST['blockers'],
        $status
    ];

    try {
        if ($report_id) {
            $sql = "UPDATE daily_site_reports SET 
                    site_id = ?, foreman_id = ?, report_date = ?, labor_count = ?, 
                    progress_percent = ?, actual_work = ?, equipment_used = ?, 
                    material_used = ?, blockers = ?, status = ? 
                    WHERE id = ?";
            $params[] = $report_id;
            $db->prepare($sql)->execute($params);
        } else {
            $sql = "INSERT INTO daily_site_reports (site_id, foreman_id, report_date, labor_count, progress_percent, actual_work, equipment_used, material_used, blockers, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $db->prepare($sql)->execute($params);
        }
        
        header("Location: ../../../main.php?module=site/forman_dashboard/index&view=reports&success=1");
    } catch (Exception $e) {
        die("Error saving report: " . $e->getMessage());
    }
}
?>
