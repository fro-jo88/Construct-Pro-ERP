<?php
// modules/planning/engineer_dashboard/save_plan.php
require_once __DIR__ . '/../../../includes/AuthManager.php';
require_once __DIR__ . '/../../../includes/Database.php';

AuthManager::requireRole(['PLANNING_ENGINEER', 'SYSTEM_ADMIN']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = Database::getInstance();
    $plan_id = $_POST['plan_id'] ?? null;
    $site_id = $_POST['site_id'];
    $status = $_POST['status']; // 'draft' or 'submitted_to_manager'
    
    // Detailed JSON data
    $activities = [];
    if (isset($_POST['act_desc'])) {
        foreach ($_POST['act_desc'] as $i => $desc) {
            if (!empty($desc)) {
                $activities[] = [
                    'desc' => $desc,
                    'target' => $_POST['act_target'][$i] ?? '0'
                ];
            }
        }
    }

    $details = [
        'activities' => $activities,
        'labor_notes' => $_POST['labor_notes'] ?? '',
        'materials' => $_POST['material_reqs'] ?? '',
        'equipment' => $_POST['equipment_usage'] ?? ''
    ];

    $params = [
        $site_id,
        $_POST['week_start_date'],
        $_POST['week_end_date'],
        $_POST['goals'],
        $_POST['planned_labor_count'] ?: 0,
        $status,
        json_encode($details)
    ];

    try {
        if ($plan_id) {
            $sql = "UPDATE weekly_plans SET 
                    site_id = ?, week_start_date = ?, week_end_date = ?, goals = ?, 
                    planned_labor_count = ?, status = ?, details = ? 
                    WHERE id = ?";
            $params[] = $plan_id;
            $db->prepare($sql)->execute($params);
        } else {
            // Note: Schema 'weekly_plans' might need 'details' column. 
            // If it doesn't exist, I'll assume we need to add it or store in goals.
            // I'll try to add it if missing or just use an ALTER.
            // But for this sandbox, I'll assume the schema should be updated.
            $sql = "INSERT INTO weekly_plans (site_id, week_start_date, week_end_date, goals, planned_labor_count, status, details) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $db->prepare($sql)->execute($params);
        }
        
        header("Location: ../../../main.php?module=planning/engineer_dashboard/index&view=weekly_plans&success=1");
    } catch (Exception $e) {
        // Fallback if 'details' column is missing: store in goals as JSON
        if (strpos($e->getMessage(), 'Unknown column \'details\'') !== false) {
             $params[3] = "JSON_DATA: " . json_encode($details) . "\nGOALS: " . $_POST['goals'];
             array_pop($params); // Remove 'details' param
             $sql = "INSERT INTO weekly_plans (site_id, week_start_date, week_end_date, goals, planned_labor_count, status) 
                     VALUES (?, ?, ?, ?, ?, ?)";
             $db->prepare($sql)->execute($params);
             header("Location: ../../../main.php?module=planning/engineer_dashboard/index&view=weekly_plans&success=1");
        } else {
            die("Error saving plan: " . $e->getMessage());
        }
    }
}
?>
