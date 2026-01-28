<?php
// modules/project_details.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/AuthManager.php';
require_once __DIR__ . '/../includes/ProjectManager.php';
require_once __DIR__ . '/../includes/SiteManager.php';

if (!AuthManager::isLoggedIn()) {
    header("Location: ../index.php");
    exit();
}

$project_id = $_GET['id'] ?? null;
if (!$project_id) die("Missing project ID.");

$project = ProjectManager::getProjectDetails($project_id);
$sites = SiteManager::getSitesByProject($project_id);

$db = Database::getInstance();
$foremen = $db->query("SELECT u.id, u.username FROM users u JOIN roles r ON u.role_id = r.id WHERE r.role_name = 'Foreman'")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_site'])) {
    SiteManager::createSite($project_id, $_POST['site_name'], $_POST['location'], $_POST['foreman_id']);
    header("Location: project_details.php?id=$project_id");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($project['project_name']); ?> - Details</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="dashboard-body">
    <?php include '../includes/header.php'; ?>
    <main class="content">
        <header class="top-bar">
            <h1><?php echo htmlspecialchars($project['project_name']); ?> (<?php echo $project['project_code']; ?>)</h1>
            <a href="projects.php" class="btn-secondary-sm">Back</a>
        </header>

        <div class="widget-grid">
            <div class="widget glass-card">
                <h3>Financial Summary</h3>
                <div class="stat-item">
                    <label>Total Budget:</label>
                    <span class="value" style="font-size: 1.5rem;">$<?php echo number_format($project['total_amount'], 2); ?></span>
                </div>
                <div class="stat-item">
                    <label>Total Spent:</label>
                    <span class="value" style="font-size: 1.5rem; color: #ff4444;">$<?php echo number_format($project['spent_amount'], 2); ?></span>
                </div>
            </div>

            <div class="widget glass-card">
                <h3>Timeline</h3>
                <p>Start: <?php echo $project['start_date']; ?></p>
                <p>End: <?php echo $project['end_date']; ?></p>
                <span class="status-badge <?php echo $project['status']; ?>"><?php echo $project['status']; ?></span>
            </div>
        </div>

        <h2 style="margin-top: 3rem;">Site Locations & Assignments</h2>
        <section class="glass-card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Site Name</th>
                        <th>Location</th>
                        <th>Foreman</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sites)): ?>
                        <tr><td colspan="4" style="text-align:center;">No sites created for this project.</td></tr>
                    <?php else: ?>
                        <?php foreach ($sites as $site): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($site['site_name']); ?></td>
                                <td><?php echo htmlspecialchars($site['location']); ?></td>
                                <td><span class="role-badge" style="background: rgba(255,255,255,0.1); color: white;"><?php echo htmlspecialchars($site['foreman_name'] ?? 'Unassigned'); ?></span></td>
                                <td><button class="btn-secondary-sm">View Reports</button></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <h2 style="margin-top: 3rem;">Quick Actions</h2>
        <div class="widget-grid">
            <div class="glass-card">
                <h3>Project Controls</h3>
                <a href="planning.php?project_id=<?php echo $project_id; ?>" class="btn-primary-sm" style="display: block; text-align: center; margin-bottom: 1rem; text-decoration: none;">View Scheduling & Planning</a>
                <button class="btn-secondary-sm" style="width: 100%;" onclick="alert('Module In Progress')">Site Reports</button>
            </div>

            <div class="glass-card">
                <h3>Add New Site</h3>
                <form method="POST">
                    <input type="hidden" name="add_site" value="1">
                    <div class="form-group">
                        <label>Site Name</label>
                        <input type="text" name="site_name" required>
                    </div>
                    <div class="form-group">
                        <label>Location</label>
                        <input type="text" name="location" required>
                    </div>
                    <div class="form-group">
                        <label>Assign Foreman</label>
                        <select name="foreman_id" style="width:100%; padding:0.8rem; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); border-radius:8px; color:white;">
                            <option value="">Select Foreman</option>
                            <?php foreach ($foremen as $fm): ?>
                                <option value="<?php echo $fm['id']; ?>"><?php echo htmlspecialchars($fm['username']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn-primary-sm" style="width:100%; margin-top:1rem;">Create Site</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
