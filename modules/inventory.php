<?php
// modules/inventory.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/AuthManager.php';
require_once __DIR__ . '/../includes/InventoryManager.php';

if (!AuthManager::isLoggedIn()) {
    header("Location: ../index.php");
    exit();
}

$stores = InventoryManager::getAllStores();
$products = InventoryManager::getAllProducts();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory & Stores - WECHECHA</title>
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
            <li class="active"><a href="inventory.php">Inventory</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </nav>

    <main class="content">
        <header class="top-bar">
            <h1>Warehouse & Inventory Control</h1>
            <div class="actions">
                <button class="btn-primary-sm">+ Add Product SKU</button>
            </div>
        </header>

        <div class="widget-grid">
            <?php foreach ($stores as $store): ?>
                <div class="glass-card">
                    <h3><?php echo htmlspecialchars($store['store_name']); ?></h3>
                    <p style="font-size: 0.8rem; color: var(--text-dim);"><?php echo htmlspecialchars($store['location']); ?></p>
                    <div class="stat-item" style="margin-top: 1rem;">
                        <label>Manager:</label>
                        <span><?php echo htmlspecialchars($store['manager_name'] ?? 'Unassigned'); ?></span>
                    </div>
                    <a href="store_details.php?id=<?php echo $store['id']; ?>" class="btn-secondary-sm" style="display: block; text-align: center; margin-top: 1rem; text-decoration: none;">Manage Stock</a>
                </div>
            <?php endforeach; ?>
        </div>

        <h2 style="margin-top: 3rem;">Master Product List</h2>
        <section class="glass-card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Unit</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr><td colspan="5" style="text-align:center;">No products registered.</td></tr>
                    <?php else: ?>
                        <?php foreach ($products as $prod): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($prod['sku']); ?></td>
                                <td><?php echo htmlspecialchars($prod['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($prod['category']); ?></td>
                                <td><?php echo htmlspecialchars($prod['unit']); ?></td>
                                <td><button class="btn-secondary-sm">Edit</button></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
