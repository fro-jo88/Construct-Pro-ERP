<?php
// modules/convert_tender.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/AuthManager.php';
require_once __DIR__ . '/../includes/TenderManager.php';
require_once __DIR__ . '/../includes/ProjectManager.php';

if (!AuthManager::isLoggedIn() || $_SESSION['role'] !== 'GM') {
    die("Unauthorized: GM only.");
}

$tender_id = $_GET['id'] ?? null;
if (!$tender_id) die("Missing tender ID.");

$db = Database::getInstance();
$stmt = $db->prepare("SELECT * FROM bids WHERE id = ?");
$stmt->execute([$tender_id]);
$tender = $stmt->fetch();

if (!$tender) die("Tender not found.");

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        ProjectManager::createProjectFromTender(
            $tender_id,
            $_POST['project_code'],
            $_POST['project_name'],
            $_POST['start_date'],
            $_POST['end_date'],
            $_POST['budget']
        );
        header("Location: projects.php");
        exit();
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
    <title>Convert Tender - WECHECHA</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="dashboard-body">
    <?php include '../includes/header.php'; ?>
    <main class="content">
        <header class="top-bar">
            <h1>Initialize Project from Tender</h1>
            <a href="tenders.php" class="btn-secondary-sm">Back</a>
        </header>

        <section class="glass-card" style="max-width: 600px; margin: 0 auto;">
            <p style="color: var(--gold); margin-bottom: 2rem;">Tender: <?php echo htmlspecialchars($tender['title']); ?> (<?php echo htmlspecialchars($tender['tender_no']); ?>)</p>
            
            <?php if ($message): ?>
                <div class="alert"><?php echo $message; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Project Code</label>
                    <input type="text" name="project_code" required placeholder="e.g. WCH-2026-001">
                </div>
                <div class="form-group">
                    <label>Internal Project Name</label>
                    <input type="text" name="project_name" value="<?php echo htmlspecialchars($tender['title']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Planned Start Date</label>
                    <input type="date" name="start_date" required>
                </div>
                <div class="form-group">
                    <label>Planned End Date</label>
                    <input type="date" name="end_date" required>
                </div>
                <div class="form-group">
                    <label>Total Allocated Budget</label>
                    <input type="number" step="0.01" name="budget" required>
                </div>
                <button type="submit" class="btn-primary" style="margin-top: 1rem;">Confirm Winning Bid & Start Project</button>
            </form>
        </section>
    </main>
</body>
</html>
