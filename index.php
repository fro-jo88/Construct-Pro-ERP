<?php
// index.php
require_once 'config/config.php';
require_once 'includes/AuthManager.php';
require_once 'includes/Database.php';

// Dynamic Demo Panel Loader
$demo_groups = [];
try {
    $db = Database::getInstance();
    $active_employees = $db->query("
        SELECT e.full_name, e.department, e.position, u.username, r.role_name 
        FROM employees e 
        JOIN users u ON e.user_id = u.id 
        JOIN roles r ON u.role_id = r.id 
        WHERE u.status = 'active'
        ORDER BY e.department DESC, e.full_name ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($active_employees as $emp) {
        $cat = $emp['department'] ?: 'General Workforce';
        if (strtoupper($emp['role_name']) === 'GM') $cat = 'Management';
        if ($emp['department'] === 'HR') $cat = 'Human Resource';
        
        $demo_groups[$cat][] = [
            'name'      => $emp['full_name'],
            'user'      => $emp['username'],
            'role'      => $emp['role_name'],
            'desig'     => $emp['position'],
            'dashboard' => $emp['role_name'] . ' Dashboard'
        ];
    }
} catch (Exception $e) {}

// Fallback to static if database is empty/unplugged
if (empty($demo_groups)) {
    $demo_groups = require 'config/demo_users.php';
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    // Special handling if coming from demo click: we might not need actual password verification if it's a known demo account
    // But for security alignment, we assume the demo password is 'password123' as seeded
    $password = $_POST['password'] ?? '';
    
    if (AuthManager::login($username, $password)) {
        header("Location: main.php");
        exit();
    } else {
        $error = "Invalid credentials or account inactive.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WECHECHA CONSTRUCTION - System Access</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/login_demo.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-body" style="margin: 0; display: block;">
    <div class="login-page-container">
        <!-- Left: Demo Side Panel -->
        <aside class="demo-side-panel">
            <div class="demo-header">
                <h2>WECHECHA ERP</h2>
                <p>Enterprise Demo Access Panel</p>
            </div>
            
            <div class="demo-scroll-area">
                <?php foreach ($demo_groups as $group => $users): ?>
                    <div class="demo-category">
                        <div class="demo-category-title">
                            <i class="fa-solid fa-folder-open"></i>
                            <?php echo $group; ?>
                        </div>
                        <?php foreach ($users as $u): ?>
                            <div class="demo-user-card" onclick="fillLogin('<?php echo $u['user']; ?>', 'password123')">
                                <span class="demo-user-name"><?php echo $u['name']; ?></span>
                                <div class="demo-user-credentials">
                                    <span><i class="fa-solid fa-user"></i> <?php echo $u['user']; ?></span>
                                    <span><i class="fa-solid fa-key"></i> password123</span>
                                </div>
                                <span class="demo-user-role"><?php echo $u['desig'] ?? $u['role']; ?></span>
                                <div class="demo-dashboard-hint">
                                    <i class="fa-solid fa-gauge-high"></i> Will enter: <?php echo $u['dashboard']; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </aside>

        <!-- Right: Login Form -->
        <div class="login-form-side">
            <div class="login-container glass-card">
                <h1 class="brand">SYSTEM LOGIN</h1>
                <form method="POST" id="loginForm">
                    <?php if ($error): ?>
                        <div style="color: #ff4444; margin-bottom: 1rem; text-align: center; font-size: 0.9rem;">
                            <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="username">Username / Email</label>
                        <input type="text" id="username" name="username" required placeholder="User identifier">
                    </div>
                    <div class="form-group">
                        <label for="password">Security Password</label>
                        <input type="password" id="password" name="password" required placeholder="••••••••">
                    </div>
                    <button type="submit" class="btn-primary">Authenticate</button>
                    
                    <div id="demo-indicator" style="display: none; margin-top: 1rem; text-align: center; color: var(--gold); font-size: 0.8rem;">
                        <i class="fa-solid fa-user-check"></i> Demo credentials loaded. Ready.
                    </div>
                </form>
                <p class="footer-text">Secure Access Node: <?php echo $_SERVER['REMOTE_ADDR']; ?></p>
            </div>
        </div>
    </div>

    <script>
        function fillLogin(user, pass) {
            document.getElementById('username').value = user;
            document.getElementById('password').value = pass;
            document.getElementById('demo-indicator').style.display = 'block';
            
            // Optional: Subtle highlight on the form
            const form = document.querySelector('.login-container');
            form.style.borderColor = '#ffcc00';
            setTimeout(() => { form.style.borderColor = 'rgba(255, 255, 255, 0.1)'; }, 1000);
        }
    </script>
</body>
</html>
