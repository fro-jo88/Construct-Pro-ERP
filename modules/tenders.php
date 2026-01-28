<?php
// modules/tenders.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/AuthManager.php';
require_once __DIR__ . '/../includes/TenderManager.php';

if (!AuthManager::isLoggedIn()) {
    header("Location: ../index.php");
    exit();
}

$tenders = TenderManager::getAllTenders();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tender Management - WECHECHA</title>
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
            <li class="active"><a href="tenders.php">Tenders</a></li>
            <li><a href="projects.php">Projects</a></li>
            <li><a href="inventory.php">Inventory</a></li>
            <li><a href="finance.php">Finance</a></li>
            <li><a href="procurement.php">Procurement</a></li>
            <li><a href="logistics.php">Logistics</a></li>
            <li><a href="audit.php">Audit</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </nav>

    <main class="content">
        <header class="top-bar">
            <h1>Tender & Bidding System</h1>
            <div class="actions">
                <a href="register_tender.php" class="btn-primary-sm" style="text-decoration: none;">+ Register New Tender</a>
            </div>
        </header>

        <section class="glass-card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tender #</th>
                        <th>Title</th>
                        <th>Client</th>
                        <th>Deadline</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tenders)): ?>
                        <tr><td colspan="6" style="text-align:center;">No tenders found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($tenders as $tender): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($tender['tender_no']); ?></td>
                                <td><?php echo htmlspecialchars($tender['title']); ?></td>
                                <td><?php echo htmlspecialchars($tender['client_name']); ?></td>
                                <td><?php echo htmlspecialchars($tender['deadline']); ?></td>
                                <td><span class="status-badge <?php echo $tender['status']; ?>"><?php echo $tender['status']; ?></span></td>
                                <td><a href="convert_tender.php?id=<?php echo $tender['id']; ?>" class="btn-secondary-sm">Initialize Project</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
