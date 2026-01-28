<?php
// modules/finance.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/AuthManager.php';
require_once __DIR__ . '/../includes/FinanceManager.php';

if (!AuthManager::isLoggedIn()) {
    header("Location: ../index.php");
    exit();
}

$budgets = FinanceManager::getProjectBudgets();
$expenses = FinanceManager::getExpensesByProject();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance & Budget - WECHECHA</title>
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
            <li class="active"><a href="finance.php">Finance</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </nav>

    <main class="content">
        <header class="top-bar">
            <h1>Finance & Executive Budget Control</h1>
            <div class="actions">
                <button class="btn-primary-sm">+ New Expense Voucher</button>
            </div>
        </header>

        <h2 style="margin-top: 2rem;">Project Budget Health</h2>
        <div class="widget-grid">
            <?php foreach ($budgets as $b): ?>
                <div class="glass-card">
                    <h3><?php echo htmlspecialchars($b['project_name']); ?></h3>
                    <div style="margin-top: 1rem;">
                        <div style="display:flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 0.5rem;">
                            <span style="color: var(--text-dim);">Utilization</span>
                            <span style="color: var(--gold);"><?php echo round(($b['spent_amount'] / $b['total_amount']) * 100, 1); ?>%</span>
                        </div>
                        <div style="width: 100%; height: 8px; background: rgba(255,255,255,0.1); border-radius: 4px; overflow: hidden;">
                            <div style="width: <?php echo ($b['spent_amount'] / $b['total_amount']) * 100; ?>%; height: 100%; background: var(--gold); box-shadow: 0 0 10px var(--gold-glow);"></div>
                        </div>
                    </div>
                    <div style="margin-top: 1.5rem; display: flex; justify-content: space-between;">
                        <div>
                            <label style="font-size: 0.7rem;">Spent</label>
                            <div style="font-weight: bold;">$<?php echo number_format($b['spent_amount'], 2); ?></div>
                        </div>
                        <div style="text-align: right;">
                            <label style="font-size: 0.7rem;">Remaining</label>
                            <div style="font-weight: bold; color: var(--gold);">$<?php echo number_format($b['remaining_amount'], 2); ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <h2 style="margin-top: 3rem;">Recent Expenses & Approvals</h2>
        <section class="glass-card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Project</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($expenses)): ?>
                        <tr><td colspan="6" style="text-align:center;">No expenses recorded.</td></tr>
                    <?php else: ?>
                        <?php foreach ($expenses as $exp): ?>
                            <tr>
                                <td><?php echo date('Y-m-d', strtotime($exp['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($exp['project_name']); ?></td>
                                <td><?php echo htmlspecialchars($exp['category']); ?></td>
                                <td style="font-weight: bold;">$<?php echo number_format($exp['amount'], 2); ?></td>
                                <td><span class="status-badge <?php echo $exp['status']; ?>"><?php echo $exp['status']; ?></span></td>
                                <td>
                                    <?php if ($exp['status'] === 'pending' && ($_SESSION['role'] === 'Finance' || $_SESSION['role'] === 'GM')): ?>
                                        <button class="btn-primary-sm" style="padding: 2px 8px; font-size: 0.7rem;">Approve</button>
                                    <?php else: ?>
                                        <button class="btn-secondary-sm">View</button>
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
