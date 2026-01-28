<?php
// main.php
require_once 'config/config.php';
require_once 'includes/AuthManager.php';
require_once 'includes/DashboardEngine.php';

if (!AuthManager::isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// LANDING LOGIC: If no module specified, go to role-specific dashboard
if (!isset($_GET['module'])) {
    $role_landing = [
        'GM' => 'gm/dashboard',
        'HR_MANAGER' => 'hr/dashboard',
        'FINANCE_HEAD' => 'finance/dashboard',
        'FINANCE_TEAM' => 'finance/dashboard',
        'AUDIT_TEAM' => 'finance/audit_dashboard',
        'TECH_BID_MANAGER' => 'bidding/technical_dashboard',
        'FINANCE_BID_MANAGER' => 'bidding/finance_dashboard',
        'PLANNING_MANAGER' => 'planning/dashboard',
        'PLANNING_ENGINEER' => 'planning/tasks',
        'FORMAN' => 'foreman/dashboard',
        'STORE_MANAGER' => 'store/dashboard',
        'STORE_KEEPER' => 'store/dashboard',
        'DRIVER_MANAGER' => 'transport/dashboard',
        'DRIVER' => 'transport/my_trips',
        'CONSTRUCTION_AUDIT' => 'audit/site_progress',
        'TENDER_FINANCE' => 'tender/finance',
        'TENDER_TECHNICAL' => 'tender/technical',
        'PURCHASE_MANAGER' => 'procurement/dashboard',
        'PURCHASE_OFFICER' => 'procurement/requests',
        'SYSTEM_ADMIN' => 'admin/dashboard',
        'SUPER_ADMIN' => 'admin/dashboard'
    ];
    $norm_role = strtoupper($role);
    if (isset($role_landing[$norm_role])) {
        header("Location: main.php?module=" . $role_landing[$norm_role]);
        exit();
    }
}

$engine = new DashboardEngine($role, $user_id);
$widgets = $engine->getWidgets();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/hr_custom.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="dashboard-body">
    <?php include 'includes/header.php'; ?>
    <nav class="sidebar glass-card">
        <h2 class="brand-small">WECHECHA</h2>
        
        <?php
        require_once 'includes/SidebarEngine.php';
        $sidebar = new SidebarEngine($role);
        echo $sidebar->render();
        ?>

        <div class="sidebar-footer">
            <div class="user-info">
                <span class="role-badge"><?php echo $role; ?></span>
                <span class="username"><?php echo $_SESSION['username']; ?></span>
            </div>
            <a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <main class="content">
        <header class="top-bar">
            <h1><?php echo isset($_GET['module']) ? ucwords(str_replace(['hr/', '_'], ['HR: ', ' '], $_GET['module'])) : $role . ' Dashboard'; ?></h1>
            <div class="actions">
                <button class="btn-primary-sm">+ New Action</button>
            </div>
        </header>

        <div class="module-container">
            <?php 
            if (isset($_GET['module'])) {
                $module = $_GET['module'];
                // Basic security check: allow only specific patterns (a-z, 0-9, /, _)
                if (preg_match('/^[a-z0-9\/_]+$/', $module)) {
                    $module_file = "modules/" . $module . ".php";
                    if (file_exists($module_file)) {
                        include $module_file;
                    } else {
                        echo "<div class='glass-card'><p class='text-red'>Module '$module' not found.</p></div>";
                    }
                } else {
                    echo "<div class='glass-card'><p class='text-red'>Invalid module path.</p></div>";
                }
            } else {
                ?>
                <div class="widget-grid">
                    <?php foreach ($widgets as $widget): ?>
                        <?php echo $engine->renderWidget($widget); ?>
                    <?php endforeach; ?>
                </div>
                <?php
            }
            ?>
        </div>
    </main>

    <script src="assets/js/dashboard.js"></script>
</body>
</html>
