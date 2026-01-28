<?php
// includes/FinanceManager.php
require_once __DIR__ . '/Database.php';

class FinanceManager {
    public static function getProjectBudgets() {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT b.*, p.project_name, p.project_code 
                            FROM budgets b 
                            JOIN projects p ON b.project_id = p.id");
        return $stmt->fetchAll();
    }

    public static function getExpensesByProject($project_id = null) {
        $db = Database::getInstance();
        $sql = "SELECT e.*, p.project_name, s.site_name, u1.username as finance_approver, u2.username as gm_approver 
                FROM expenses e 
                JOIN projects p ON e.project_id = p.id 
                LEFT JOIN sites s ON e.site_id = s.id 
                LEFT JOIN users u1 ON e.finance_approved_by = u1.id 
                LEFT JOIN users u2 ON e.gm_approved_by = u2.id";
        
        if ($project_id) {
            $stmt = $db->prepare($sql . " WHERE e.project_id = ? ORDER BY e.created_at DESC");
            $stmt->execute([$project_id]);
        } else {
            $stmt = $db->query($sql . " ORDER BY e.created_at DESC");
        }
        return $stmt->fetchAll();
    }

    public static function recordExpense($data) {
        AuthManager::restrictDemoAction();
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO expenses (project_id, site_id, category, amount, description, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        return $stmt->execute([
            $data['project_id'],
            $data['site_id'],
            $data['category'],
            $data['amount'],
            $data['description']
        ]);
    }

    public static function approveExpense($expense_id, $role) {
        AuthManager::restrictDemoAction();
        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            if ($role === 'Finance') {
                $stmt = $db->prepare("UPDATE expenses SET finance_approved_by = ?, status = 'approved' WHERE id = ?");
                $stmt->execute([$_SESSION['user_id'], $expense_id]);
            } elseif ($role === 'GM') {
                $stmt = $db->prepare("UPDATE expenses SET gm_approved_by = ?, status = 'approved' WHERE id = ?");
                $stmt->execute([$_SESSION['user_id'], $expense_id]);
            }

            // Deduct from budget if fully approved (assuming joint approval or single role for simplicity now)
            // In a real ERP, we'd check if both approved.
            $stmt = $db->prepare("SELECT amount, project_id FROM expenses WHERE id = ?");
            $stmt->execute([$expense_id]);
            $exp = $stmt->fetch();

            $stmt = $db->prepare("UPDATE budgets SET spent_amount = spent_amount + ?, remaining_amount = remaining_amount - ? WHERE project_id = ?");
            $stmt->execute([$exp['amount'], $exp['amount'], $exp['project_id']]);

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
?>
