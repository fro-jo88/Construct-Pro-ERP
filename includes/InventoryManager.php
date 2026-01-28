<?php
// includes/InventoryManager.php
require_once __DIR__ . '/Database.php';

class InventoryManager {
    public static function getAllStores() {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT s.*, u.username as manager_name FROM stores s LEFT JOIN users u ON s.manager_id = u.id");
        return $stmt->fetchAll();
    }

    public static function getStoreInventory($store_id) {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT sl.*, p.product_name, p.sku, p.unit 
                            FROM stock_levels sl 
                            JOIN products p ON sl.product_id = p.id 
                            WHERE sl.store_id = ?");
        $stmt->execute([$store_id]);
        return $stmt->fetchAll();
    }

    public static function updateStock($store_id, $product_id, $quantity, $type, $reference_id = null) {
        AuthManager::restrictDemoAction();
        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            // 1. Log Transaction
            $stmt = $db->prepare("INSERT INTO audit_logs (user_id, action, table_affected, record_id, new_values) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], 'stock_' . $type, 'stock_levels', $product_id, json_encode(['qty' => $quantity, 'store' => $store_id])]);

            // 2. Update stock levels
            $stmt = $db->prepare("INSERT INTO stock_levels (store_id, product_id, quantity) 
                                VALUES (?, ?, ?) 
                                ON DUPLICATE KEY UPDATE quantity = quantity + ?");
            $change = ($type === 'in') ? $quantity : -$quantity;
            $stmt->execute([$store_id, $product_id, $change, $change]);

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function getAllProducts() {
        $db = Database::getInstance();
        return $db->query("SELECT * FROM products")->fetchAll();
    }
}
?>
