<?php
// modules/audit.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/AuthManager.php';
require_once __DIR__ . '/../includes/AuditManager.php';

if (!AuthManager::isLoggedIn()) {
    header("Location: ../index.php");
    exit();
}

$reports = AuditManager::getAuditReports();
$stats = AuditManager::getVarianceStats();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Construction Audit - WECHECHA</title>
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
            <li><a href="projects.php">Projects</a></li>
            <li><a href="inventory.php">Inventory</a></li>
            <li><a href="finance.php">Finance</a></li>
            <li><a href="procurement.php">Procurement</a></li>
            <li><a href="logistics.php">Logistics</a></li>
            <li class="active"><a href="audit.php">Audit</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </nav>

    <main class="content">
        <header class="top-bar">
            <h1>Construction Audit & Variance Analysis</h1>
            <div class="actions">
                <button class="btn-primary-sm">+ New Audit Finding</button>
            </div>
        </header>

        <div class="widget-grid">
            <div class="glass-card">
                <h3>System Variance Avg</h3>
                <div style="margin-top: 1rem;">
                    <label style="font-size: 0.8rem; color: var(--text-dim);">Material Variance:</label>
                    <div style="font-size: 1.5rem; font-weight: bold; color: #ff4444;"><?php echo number_format($stats['avg_mat_variance'] ?? 0, 2); ?>%</div>
                </div>
                <div style="margin-top: 1rem;">
                    <label style="font-size: 0.8rem; color: var(--text-dim);">Progress Variance:</label>
                    <div style="font-size: 1.5rem; font-weight: bold; color: var(--gold);"><?php echo number_format($stats['avg_prog_variance'] ?? 0, 2); ?>%</div>
                </div>
            </div>
            
            <div class="glass-card" style="grid-column: span 2;">
                <h3>Recent Audit Findings</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Project/Site</th>
                            <th>Findings</th>
                            <th>Variance</th>
                            <th>Auditor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($reports)): ?>
                            <tr><td colspan="5" style="text-align:center;">No audit reports found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($reports as $r): ?>
                                <tr>
                                    <td><?php echo $r['audit_date']; ?></td>
                                    <td><?php echo htmlspecialchars($r['project_name']); ?> (<?php echo htmlspecialchars($r['site_name'] ?: 'General'); ?>)</td>
                                    <td><?php echo substr(htmlspecialchars($r['findings']), 0, 50); ?>...</td>
                                    <td><span style="color: <?php echo $r['material_variance'] > 0 ? '#ff4444' : '#00ff64'; ?>"><?php echo $r['material_variance']; ?>%</span></td>
                                    <td><?php echo htmlspecialchars($r['auditor_name']); ?></td>
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
