<?php
// modules/register_tender.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/AuthManager.php';
require_once __DIR__ . '/../includes/TenderManager.php';

if (!AuthManager::isLoggedIn()) {
    die("Unauthorized access.");
}

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (TenderManager::createTender($_POST)) {
        $message = "Tender registered successfully!";
    } else {
        $message = "Failed to register tender.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Tender - WECHECHA</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="dashboard-body">
    <?php include '../includes/header.php'; ?>
    <main class="content">
        <header class="top-bar">
            <h1>Register New Tender</h1>
            <a href="tenders.php" class="btn-secondary-sm">Back to List</a>
        </header>

        <section class="glass-card" style="max-width: 600px; margin: 0 auto;">
            <?php if ($message): ?>
                <p style="color: var(--gold);"><?php echo $message; ?></p>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Tender Number</label>
                    <input type="text" name="tender_no" required>
                </div>
                <div class="form-group">
                    <label>Project Title</label>
                    <input type="text" name="title" required>
                </div>
                <div class="form-group">
                    <label>Client Name</label>
                    <input type="text" name="client_name" required>
                </div>
                <div class="form-group">
                    <label>Deadline</label>
                    <input type="datetime-local" name="deadline" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" style="width:100%; height:100px; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); border-radius:8px; color:white; padding:0.5rem;"></textarea>
                </div>
                <button type="submit" class="btn-primary" style="margin-top: 1rem;">Register Tender</button>
            </form>
        </section>
    </main>
</body>
</html>
