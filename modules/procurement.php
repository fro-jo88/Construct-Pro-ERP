<?php
// modules/procurement.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/AuthManager.php';
require_once __DIR__ . '/../includes/ProcurementManager.php';

if (!AuthManager::isLoggedIn()) {
    header("Location: ../index.php");
    exit();
}

$vendors = ProcurementManager::getAllVendors();
$requests = ProcurementManager::getAllPurchaseRequests();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procurement - WECHECHA</title>
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
            <li class="active"><a href="procurement.php">Procurement</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </nav>

    <main class="content">
        <header class="top-bar">
            <h1>Procurement & Supply Chain Flow</h1>
            <div class="actions">
                <button class="btn-primary-sm">+ Initiate Purchase Request</button>
            </div>
        </header>

        <h2 style="margin-top: 2rem;">Approved Vendors</h2>
        <div class="widget-grid">
            <?php foreach ($vendors as $v): ?>
                <div class="glass-card">
                    <h3><?php echo htmlspecialchars($v['vendor_name']); ?></h3>
                    <p style="font-size: 0.8rem; color: var(--text-dim);"><?php echo htmlspecialchars($v['contact_person']); ?></p>
                    <div style="margin-top: 0.5rem; font-size: 0.85rem;">
                        <i class="fa fa-phone"></i> <?php echo htmlspecialchars($v['phone']); ?>
                    </div>
                    <button class="btn-secondary-sm" style="width: 100%; margin-top: 1rem;">View Catalogue</button>
                </div>
            <?php endforeach; ?>
        </div>

        <h2 style="margin-top: 3rem;">Purchase Requests (PR) & Workflow</h2>
        <section class="glass-card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>PR ID</th>
                        <th>Project</th>
                        <th>Requester</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($requests)): ?>
                        <tr><td colspan="6" style="text-align:center;">No purchase requests found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($requests as $pr): ?>
                            <tr>
                                <td>PR-#<?php echo $pr['id']; ?></td>
                                <td><?php echo htmlspecialchars($pr['project_name']); ?></td>
                                <td><?php echo htmlspecialchars($pr['requester']); ?></td>
                                <td><span class="status-badge <?php echo $pr['status']; ?>"><?php echo str_replace('_', ' ', $pr['status']); ?></span></td>
                                <td><?php echo date('Y-m-d', strtotime($pr['created_at'])); ?></td>
                                <td>
                                    <?php if ($pr['status'] === 'pending' && ($_SESSION['role'] === 'Finance' || $_SESSION['role'] === 'GM')): ?>
                                        <button class="btn-primary-sm" style="padding: 2px 8px; font-size: 0.7rem;">Approve PR</button>
                                    <?php elseif ($pr['status'] === 'gm_approved'): ?>
                                        <button class="btn-primary-sm" style="padding: 2px 8px; font-size: 0.7rem; background: #00ff64; border-color: #00ff64;">Generate PO</button>
                                    <?php else: ?>
                                        <button class="btn-secondary-sm">Details</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
