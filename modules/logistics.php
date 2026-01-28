<?php
// modules/logistics.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/AuthManager.php';
require_once __DIR__ . '/../includes/LogisticsManager.php';

if (!AuthManager::isLoggedIn()) {
    header("Location: ../index.php");
    exit();
}

$vehicles = LogisticsManager::getAllVehicles();
$trips = LogisticsManager::getTripLogs();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logistics - WECHECHA</title>
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
            <li class="active"><a href="logistics.php">Logistics</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </nav>

    <main class="content">
        <header class="top-bar">
            <h1>Transport & Fleet Logistics</h1>
            <div class="actions">
                <button class="btn-primary-sm">+ New Trip Log</button>
            </div>
        </header>

        <h2 style="margin-top: 2rem;">Fleet Status</h2>
        <div class="widget-grid">
            <?php foreach ($vehicles as $v): ?>
                <div class="glass-card">
                    <h3><?php echo htmlspecialchars($v['plate_number']); ?></h3>
                    <p style="font-size: 0.8rem; color: var(--text-dim);"><?php echo htmlspecialchars($v['model']); ?> (<?php echo htmlspecialchars($v['vehicle_type']); ?>)</p>
                    <div style="margin-top: 1rem;">
                        <span class="status-badge <?php echo $v['status']; ?>"><?php echo $v['status']; ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <h2 style="margin-top: 3rem;">Recent Trip Logs & Fuel Consumption</h2>
        <section class="glass-card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Vehicle</th>
                        <th>Driver</th>
                        <th>Project</th>
                        <th>Start Time</th>
                        <th>Status</th>
                        <th>KMS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($trips)): ?>
                        <tr><td colspan="6" style="text-align:center;">No trip logs found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($trips as $t): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($t['plate_number']); ?></td>
                                <td><?php echo htmlspecialchars($t['driver_name']); ?></td>
                                <td><?php echo htmlspecialchars($t['project_name']); ?></td>
                                <td><?php echo $t['start_time']; ?></td>
                                <td><span class="status-badge active">Ongoing</span></td>
                                <td><?php echo $t['kms_start']; ?> km</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
