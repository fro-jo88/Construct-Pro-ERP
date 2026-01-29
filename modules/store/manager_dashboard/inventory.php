<?php
// modules/store/manager_dashboard/inventory.php

$db = Database::getInstance();

// Search & Filter
$search = $_GET['search'] ?? '';
$store_filter = $_GET['store_id'] ?? '';
$cat_filter = $_GET['category'] ?? '';

$sql = "SELECT sl.*, p.product_name, p.sku, p.unit, p.category, p.min_threshold, s.store_name 
        FROM stock_levels sl
        JOIN products p ON sl.product_id = p.id
        JOIN stores s ON sl.store_id = s.id
        WHERE 1=1";

$params = [];
if (!empty($search)) {
    $sql .= " AND (p.product_name LIKE ? OR p.sku LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if (!empty($store_filter)) {
    $sql .= " AND sl.store_id = ?";
    $params[] = $store_filter;
}
if (!empty($cat_filter)) {
    $sql .= " AND p.category = ?";
    $params[] = $cat_filter;
}

$sql .= " ORDER BY s.store_name ASC, p.product_name ASC";
$inventory = $db->prepare($sql);
$inventory->execute($params);
$items = $inventory->fetchAll();

$stores = $db->query("SELECT id, store_name FROM stores")->fetchAll();
$categories = $db->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL")->fetchAll();

?>

<div class="glass-panel">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Multi-Site Inventory Repository</h4>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-file-export me-1"></i> Export Data</button>
        </div>
    </div>

    <!-- Filters -->
    <form class="row g-3 mb-4 p-3 rounded-3" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
        <input type="hidden" name="module" value="store/manager_dashboard/index">
        <input type="hidden" name="view" value="inventory">
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text bg-dark border-secondary text-secondary"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control bg-dark text-white border-secondary" name="search" placeholder="Search by SKU or Name..." value="<?= htmlspecialchars($search) ?>">
            </div>
        </div>
        <div class="col-md-3">
            <select class="form-select bg-dark text-white border-secondary" name="store_id">
                <option value="">All Stores</option>
                <?php foreach ($stores as $st): ?>
                    <option value="<?= $st['id'] ?>" <?= $store_filter == $st['id'] ? 'selected' : '' ?>><?= htmlspecialchars($st['store_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select bg-dark text-white border-secondary" name="category">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['category'] ?>" <?= $cat_filter == $cat['category'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['category']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100 fw-bold">Filter</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-custom text-white">
            <thead class="text-secondary text-xs text-uppercase fw-bold">
                <tr>
                    <th>Store Location</th>
                    <th>Material / SKU</th>
                    <th>Category</th>
                    <th>Qty On Hand</th>
                    <th>Status</th>
                    <th class="text-end">Threshold</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><span class="fw-bold text-info"><?= htmlspecialchars($item['store_name']) ?></span></td>
                    <td>
                        <div class="fw-bold"><?= htmlspecialchars($item['product_name']) ?></div>
                        <div class="text-xs text-secondary font-monospace"><?= $item['sku'] ?></div>
                    </td>
                    <td><span class="badge bg-dark border border-secondary"><?= htmlspecialchars($item['category']) ?></span></td>
                    <td class="fw-bold h6 mb-0"><?= number_format($item['quantity'], 2) ?> <?= $item['unit'] ?></td>
                    <td>
                        <?php if ($item['quantity'] <= $item['min_threshold']): ?>
                            <span class="text-danger fw-bold"><i class="fas fa-exclamation-circle me-1"></i> LOW STOCK</span>
                        <?php elseif ($item['quantity'] > $item['min_threshold'] * 3): ?>
                            <span class="text-success fw-bold"><i class="fas fa-check-double me-1"></i> HEALTHY</span>
                        <?php else: ?>
                            <span class="text-warning fw-bold">STABLE</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end text-secondary font-monospace"><?= $item['min_threshold'] ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($items)): ?>
                    <tr><td colspan="6" class="text-center py-5 text-secondary">No inventory matching your filters.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
