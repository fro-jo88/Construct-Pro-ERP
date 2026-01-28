<?php
// main.php
require_once 'config/config.php';
require_once 'includes/AuthManager.php';

if (!AuthManager::isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// LANDING LOGIC: If no module specified, go to role-specific dashboard
if (!isset($_GET['module'])) {
    $role_code = $_SESSION['role_code'] ?? 'default';
    header("Location: main.php?module=dashboards/roles/" . $role_code);
    exit();
}

$role_code = $_SESSION['role_code'];
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
        $sidebar = new SidebarEngine($role_code);
        echo $sidebar->render();
        ?>

        <div class="sidebar-footer">
            <div class="user-info">
                <span class="role-badge"><?php echo $role_code; ?></span>
                <span class="username"><?php echo $_SESSION['username']; ?></span>
            </div>
            <a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <main class="content">
        <header class="top-bar">
            <h1><?php echo isset($_GET['module']) ? ucwords(str_replace(['dashboards/roles/', 'hr/', '_'], ['', 'HR: ', ' '], $_GET['module'])) : $role_code . ' Dashboard'; ?></h1>
            <div class="actions">
                <button class="btn-primary-sm">+ New Action</button>
            </div>
        </header>

        <div class="module-container">
            <?php 
            if (isset($_GET['module'])) {
                $module = $_GET['module'];
                // Basic security check: allow only specific patterns (a-z, 0-9, /, _)
                if (preg_match('/^[a-zA-Z0-9\/_]+$/', $module)) {
                    $module_file = "modules/" . $module . ".php";
                    $index_file = "modules/" . $module . "/index.php";

                    if (file_exists($module_file)) {
                        include $module_file;
                    } elseif (file_exists($index_file)) {
                        include $index_file;
                    } else {
                        echo "<div class='glass-card'>";
                        echo "<p style='color:#ff4444; font-weight:bold;'>Module '$module' not found.</p>";
                        echo "</div>";
                    }
                } else {
                    echo "<div class='glass-card'><p class='text-red'>Invalid module path.</p></div>";
                }
            }
            ?>
        </div>
    </main>

    <script src="assets/js/dashboard.js"></script>
</body>
</html>
