<?php
// modules/store_details.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/AuthManager.php';
require_once __DIR__ . '/../includes/InventoryManager.php';

if (!AuthManager::isLoggedIn()) {
    header("Location: ../index.php");
    exit();
}

$store_id = $_GET['id'] ?? null;
if (!$store_id) die("Missing store ID.");

$db = Database::getInstance();
$stmt = $db->prepare("SELECT * FROM stores WHERE id = ?");
$stmt->execute([$store_id]);
$store = $stmt->fetch();

$inventory = InventoryManager::getStoreInventory($store_id);
$all_products = InventoryManager::getAllProducts();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    InventoryManager::updateStock($store_id, $_POST['product_id'], $_POST['quantity'], $_POST['type']);
    header("Location: store_details.php?id=$store_id");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($store['store_name']); ?> - Stock</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="dashboard-body">
    <?php include '../includes/header.php'; ?>
    <main class="content">
        <header class="top-bar">
            <h1>Inventory: <?php echo htmlspecialchars($store['store_name']); ?></h1>
            <a href="inventory.php" class="btn-secondary-sm">Back to Warehouse</a>
        </header>

        <div class="widget-grid">
            <div class="glass-card" style="grid-column: span 2;">
                <h3>Current Stock Levels</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Unit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($inventory)): ?>
                            <tr><td colspan="4" style="text-align:center;">Store is empty.</td></tr>
                        <?php else: ?>
                            <?php foreach ($inventory as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['sku']); ?></td>
                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td><span style="font-weight: bold; color: var(--gold);"><?php echo number_format($item['quantity'], 2); ?></span></td>
                                    <td><?php echo htmlspecialchars($item['unit']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="glass-card">
                <h3>Stock Adjustment</h3>
                <form method="POST">
                    <input type="hidden" name="update_stock" value="1">
                    <div class="form-group">
                        <label>Product</label>
                        <select name="product_id" required style="width:100%; padding:0.8rem; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); border-radius:8px; color:white;">
                            <?php foreach ($all_products as $p): ?>
                                <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['product_name']); ?> (<?php echo $p['sku']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Transaction Type</label>
                        <select name="type" required style="width:100%; padding:0.8rem; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); border-radius:8px; color:white;">
                            <option value="in">STOCK IN (GRN/Return)</option>
                            <option value="out">STOCK OUT (Issue/Damaged)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Quantity</label>
                        <input type="number" step="0.01" name="quantity" required>
                    </div>
                    <button type="submit" class="btn-primary-sm" style="width:100%; margin-top:1rem;">Apply Transaction</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
