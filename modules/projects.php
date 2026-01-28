<?php
// modules/projects.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/AuthManager.php';
require_once __DIR__ . '/../includes/ProjectManager.php';

if (!AuthManager::isLoggedIn()) {
    header("Location: ../index.php");
    exit();
}

$projects = ProjectManager::getAllProjects();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects - WECHECHA</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="dashboard-body">
    <?php include '../includes/header.php'; ?>
    <nav class="sidebar glass-card">
        <h2 class="brand-small">WECHECHA</h2>
        <ul class="nav-links">
            <li><a href="../main.php">Dashboard</a></li>
            <li><a href="hr.php">HR & Employees</a></li>
            <li><a href="tenders.php">Tenders</a></li>
            <li class="active"><a href="projects.php">Projects</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </nav>

    <main class="content">
        <header class="top-bar">
            <h1>Active Projects</h1>
            <div class="actions">
                <p style="font-size: 0.8rem; color: var(--text-dim);">Projects are created from WON tenders.</p>
            </div>
        </header>

        <section class="glass-card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Project Name</th>
                        <th>Tender #</th>
                        <th>Budget</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($projects)): ?>
                        <tr><td colspan="6" style="text-align:center;">No projects active.</td></tr>
                    <?php else: ?>
                        <?php foreach ($projects as $proj): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($proj['project_code']); ?></td>
                                <td><?php echo htmlspecialchars($proj['project_name']); ?></td>
                                <td><?php echo htmlspecialchars($proj['tender_no'] ?? 'N/A'); ?></td>
                                <td>$<?php echo number_format($proj['budget'], 2); ?></td>
                                <td><span class="status-badge <?php echo $proj['status']; ?>"><?php echo $proj['status']; ?></span></td>
                                <td><a href="project_details.php?id=<?php echo $proj['id']; ?>" class="btn-secondary-sm">Manage</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
