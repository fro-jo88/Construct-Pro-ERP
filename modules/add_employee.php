<?php
// modules/add_employee.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/AuthManager.php';
require_once __DIR__ . '/../includes/HRManager.php';

if (!AuthManager::isLoggedIn() || $_SESSION['role'] !== 'GM' && $_SESSION['role'] !== 'HR') {
    die("Unauthorized access.");
}

$db = Database::getInstance();
$roles = $db->query("SELECT * FROM roles")->fetchAll();

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        HRManager::createEmployee($_POST);
        $message = "Employee created successfully!";
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Employee - WECHECHA</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    </style>
</head>
<body class="dashboard-body">
    <?php include '../includes/header.php'; ?>
    <main class="content">
        <header class="top-bar">
            <h1>Register New Employee</h1>
            <a href="hr.php" class="btn-secondary-sm">Back to List</a>
        </header>

        <section class="glass-card" style="max-width: 800px; margin: 0 auto;">
            <?php if ($message): ?>
                <div class="alert"><?php echo $message; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" required>
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label>Employee Code</label>
                        <input type="text" name="employee_code" required>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role_id" style="width:100%; padding:0.8rem; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); border-radius:8px; color:white;">
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo $role['id']; ?>"><?php echo $role['role_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Designation</label>
                        <input type="text" name="designation">
                    </div>
                    <div class="form-group">
                        <label>Department</label>
                        <input type="text" name="department">
                    </div>
                    <div class="form-group">
                        <label>Base Salary</label>
                        <input type="number" step="0.01" name="base_salary">
                    </div>
                    <div class="form-group">
                        <label>Salary Type</label>
                        <select name="salary_type" style="width:100%; padding:0.8rem; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); border-radius:8px; color:white;">
                            <option value="monthly">Monthly</option>
                            <option value="daily">Daily</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn-primary" style="margin-top: 2rem;">Save Employee Profile</button>
            </form>
        </section>
    </main>
</body>
</html>
