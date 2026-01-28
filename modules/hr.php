<?php
// modules/hr.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/AuthManager.php';
require_once __DIR__ . '/../includes/HRManager.php';

if (!AuthManager::isLoggedIn()) {
    header("Location: ../index.php");
    exit();
}

$employees = HRManager::getAllEmployees();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Management - WECHECHA</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="dashboard-body">
    <?php include '../includes/header.php'; ?>
    <nav class="sidebar glass-card">
        <h2 class="brand-small">WECHECHA</h2>
        <ul class="nav-links">
            <li><a href="../main.php">Dashboard</a></li>
            <li class="active"><a href="hr.php">HR & Employees</a></li>
            <li><a href="tenders.php">Tenders</a></li>
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
            <h1>HR & Employee Management</h1>
            <div class="actions">
                <a href="add_employee.php" class="btn-primary-sm" style="text-decoration: none;">+ Add Employee</a>
            </div>
        </header>

        <section class="glass-card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Designation</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($employees)): ?>
                        <tr><td colspan="6" style="text-align:center;">No employees found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($employees as $emp): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($emp['employee_code']); ?></td>
                                <td><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($emp['designation']); ?></td>
                                <td><?php echo htmlspecialchars($emp['department']); ?></td>
                                <td><span class="status-badge <?php echo $emp['status']; ?>"><?php echo $emp['status']; ?></span></td>
                                <td><button class="btn-secondary-sm">View</button></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
