<?php
// modules/planning.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/AuthManager.php';
require_once __DIR__ . '/../includes/ProjectManager.php';
require_once __DIR__ . '/../includes/PlanningManager.php';
require_once __DIR__ . '/../includes/SiteManager.php';

if (!AuthManager::isLoggedIn()) {
    header("Location: ../index.php");
    exit();
}

$project_id = $_GET['project_id'] ?? null;
if (!$project_id) die("Missing project ID.");

$project = ProjectManager::getProjectDetails($project_id);
$sites = SiteManager::getSitesByProject($project_id);
$schedules = PlanningManager::getSchedulesByProject($project_id);

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['schedule_file'])) {
    if (PlanningManager::uploadSchedule($project_id, $_POST['site_id'], $_POST['schedule_type'], $_FILES['schedule_file'])) {
        $message = "Schedule uploaded successfully!";
        header("Location: planning.php?project_id=$project_id&msg=success");
        exit();
    } else {
        $message = "Failed to upload schedule.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planning & Scheduling - WECHECHA</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="dashboard-body">
    <?php include '../includes/header.php'; ?>
    <main class="content">
        <header class="top-bar">
            <h1>Planning & Scheduling: <?php echo htmlspecialchars($project['project_name']); ?></h1>
            <a href="project_details.php?id=<?php echo $project_id; ?>" class="btn-secondary-sm">Back to Project</a>
        </header>

        <div class="widget-grid">
            <div class="glass-card">
                <h3>Upload New Schedule</h3>
                <?php if (isset($_GET['msg']) && $_GET['msg'] === 'success'): ?>
                    <p style="color: var(--gold);">Upload successful!</p>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Target Site</label>
                        <select name="site_id" required style="width:100%; padding:0.8rem; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); border-radius:8px; color:white;">
                            <option value="0">Overall Project</option>
                            <?php foreach ($sites as $site): ?>
                                <option value="<?php echo $site['id']; ?>"><?php echo htmlspecialchars($site['site_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Schedule Type</label>
                        <select name="schedule_type" required style="width:100%; padding:0.8rem; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); border-radius:8px; color:white;">
                            <option value="ms_project">MS Project (Gantt)</option>
                            <option value="manpower">Manpower Schedule</option>
                            <option value="equipment">Equipment Schedule</option>
                            <option value="material">Material Schedule</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>File (PDF, XLSX, MPP)</label>
                        <input type="file" name="schedule_file" required>
                    </div>
                    <button type="submit" class="btn-primary-sm" style="width:100%; margin-top:1rem;">Upload & Version Control</button>
                </form>
            </div>

            <div class="glass-card" style="grid-column: span 2;">
                <h3>Schedule Version History</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Version</th>
                            <th>Site</th>
                            <th>Type</th>
                            <th>Uploaded At</th>
                            <th>File</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($schedules)): ?>
                            <tr><td colspan="5" style="text-align:center;">No schedules uploaded yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($schedules as $sch): ?>
                                <tr>
                                    <td>v<?php echo $sch['version']; ?></td>
                                    <td><?php echo $sch['site_name'] ?: 'Overall'; ?></td>
                                    <td><?php echo strtoupper(str_replace('_', ' ', $sch['schedule_type'])); ?></td>
                                    <td><?php echo $sch['uploaded_at']; ?></td>
                                    <td><a href="../<?php echo $sch['file_path']; ?>" target="_blank" class="btn-secondary-sm">Download</a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
