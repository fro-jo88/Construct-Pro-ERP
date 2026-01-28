<?php
// includes/GMManager.php
require_once __DIR__ . '/Database.php';

class GMManager {
    
    /**
     * Aggregates real-time KPIs for the Executive Overview
     */
    public static function getExecutiveKPIs() {
        $db = Database::getInstance();
        
        $stats = [
            'active_projects' => $db->query("SELECT COUNT(*) FROM projects WHERE status = 'active'")->fetchColumn(),
            'active_bids' => $db->query("SELECT COUNT(*) FROM bids WHERE status NOT IN ('WON', 'LOSS')")->fetchColumn(),
            'workforce_count' => $db->query("SELECT COUNT(*) FROM employees WHERE status = 'active'")->fetchColumn(),
            'pending_approvals' => self::getPendingApprovalCount(),
            'pending_procurement' => $db->query("SELECT COUNT(*) FROM purchase_requests WHERE status IN ('pending', 'finance_approved')")->fetchColumn(),
            'budget_utilization' => self::calculateBudgetUtilization(),
            'cash_exposure' => self::calculateCashExposure(),
            'critical_incidents' => self::getCriticalIncidentCount($db)
        ];
        
        return $stats;
    }

    private static function getCriticalIncidentCount($db) {
        try {
            return $db->query("SELECT COUNT(*) FROM site_incidents WHERE severity = 'critical' AND gm_acknowledged = 0")->fetchColumn() ?: 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    private static function getPendingApprovalCount() {
        $db = Database::getInstance();
        $count = 0;
        try {
            $count += $db->query("SELECT COUNT(*) FROM employees WHERE gm_approval_status = 'pending'")->fetchColumn() ?: 0;
            $count += $db->query("SELECT COUNT(*) FROM bids WHERE status IN ('FINANCIAL_COMPLETED', 'FINANCE_FINAL_REVIEW')")->fetchColumn() ?: 0;
            $count += $db->query("SELECT COUNT(*) FROM payroll WHERE status = 'draft'")->fetchColumn() ?: 0;
            $count += $db->query("SELECT COUNT(*) FROM material_requests WHERE gm_approval_status = 'pending'")->fetchColumn() ?: 0;
            
            // Check budgets if column exists
            $count += $db->query("SELECT COUNT(*) FROM budgets WHERE status = 'pending'")->fetchColumn() ?: 0;
        } catch (Exception $e) {
            // Silently continue if some modules aren't fully schema-ready
        }
        return $count;
    }

    private static function calculateBudgetUtilization() {
        $db = Database::getInstance();
        // Simplified logic: Total spent vs Total Budgeted
        $total_budget = $db->query("SELECT SUM(total_amount) FROM budgets")->fetchColumn() ?: 1;
        $total_spent = $db->query("SELECT SUM(amount) FROM expenses")->fetchColumn() ?: 0;
        return round(($total_spent / $total_budget) * 100, 1);
    }

    private static function calculateCashExposure() {
        $db = Database::getInstance();
        try {
            // Sum of all approved budget totals
            $exposure = $db->query("SELECT SUM(total_amount) FROM budgets WHERE status = 'active'")->fetchColumn();
            return number_format($exposure ?: 0, 2);
        } catch (Exception $e) {
            // Fallback if status column hasn't been added to budgets yet
            $exposure = $db->query("SELECT SUM(total_amount) FROM budgets")->fetchColumn();
            return number_format($exposure ?: 0, 2);
        }
    }

    /**
     * Fetches critical system-wide alerts
     */
    public static function getCriticalAlerts() {
        $db = Database::getInstance();
        $alerts = [];
        
        // 1. Unacknowledged emergency incidents
        try {
            $incidents = $db->query("SELECT i.*, s.site_name FROM site_incidents i 
                                    JOIN sites s ON i.site_id = s.id 
                                    WHERE i.gm_acknowledged = 0 ORDER BY i.created_at DESC LIMIT 5")->fetchAll();
            foreach ($incidents as $inc) {
                $alerts[] = [
                    'type' => 'EMERGENCY',
                    'severity' => $inc['severity'],
                    'message' => "{$inc['site_name']}: {$inc['type']} - {$inc['description']}",
                    'created_at' => $inc['created_at'],
                    'id' => $inc['id']
                ];
            }
        } catch (Exception $e) {
            // Incidents table or columns might be missing
        }

        // 2. Budget overruns
        // (Simulated logic: projects over 90%)
        return $alerts;
    }

    /**
     * Unified approval decision logic
     */
    public static function processApproval($module, $ref_id, $decision, $reason, $user_id) {
        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            // 1. Log in approval history
            $stmt = $db->prepare("INSERT INTO approval_history (module, reference_id, approver_id, decision, reason) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$module, $ref_id, $user_id, $decision, $reason]);

            // 2. Update the target record based on module
            switch ($module) {
                case 'HR':
                    require_once __DIR__ . '/HRManager.php';
                    if ($decision === 'approved') HRManager::approveEmployee($ref_id, $user_id);
                    else HRManager::rejectEmployee($ref_id, $user_id, $reason);
                    break;
                
                case 'BIDS':
                    require_once __DIR__ . '/BidManager.php';
                    if ($decision === 'pre_approved') {
                        BidManager::gmPreApprove($ref_id, $user_id, $reason);
                    } elseif ($decision === 'won') {
                        BidManager::gmFinalDecision($ref_id, 'WON', $user_id, $reason);
                    } elseif ($decision === 'loss') {
                        BidManager::gmFinalDecision($ref_id, 'LOSS', $user_id, $reason);
                    } else {
                        // General fallback for querying/rejection
                        $db->prepare("UPDATE bids SET status = 'DRAFT' WHERE id = ?")->execute([$ref_id]);
                    }
                    break;
                
                case 'FINANCE':
                    $status = ($decision === 'approved') ? 'active' : 'rejected';
                    $stmt = $db->prepare("UPDATE budgets SET status = ? WHERE id = ?");
                    $stmt->execute([$status, $ref_id]);
                    break;
                
                case 'PROCUREMENT':
                    $status = ($decision === 'approved') ? 'gm_approved' : 'rejected';
                    $stmt = $db->prepare("UPDATE purchase_requests SET status = ? WHERE id = ?");
                    $stmt->execute([$status, $ref_id]);
                    break;

                case 'INVENTORY':
                    // e.g. approving a material request from a site
                    $status = ($decision === 'approved') ? 'validated' : 'rejected';
                    $stmt = $db->prepare("UPDATE material_requests SET gm_approval_status = ?, hr_review_status = 'validated' WHERE id = ?");
                    $stmt->execute([$decision === 'approved' ? 'approved' : 'rejected', $ref_id]);
                    break;
            }

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function getProjectOversight() {
        $db = Database::getInstance();
        try {
            return $db->query("SELECT p.*, b.client_name, 
                               (SELECT COUNT(*) FROM sites WHERE project_id = p.id) as site_count 
                               FROM projects p 
                               JOIN bids b ON p.tender_id = b.id 
                               ORDER BY p.progress_percent ASC")->fetchAll();
        } catch (Exception $e) {
            // Fallback if schema not updated
            return $db->query("SELECT p.*, b.client_name, 0 as progress_percent, 0 as risk_score,
                               (SELECT COUNT(*) FROM sites WHERE project_id = p.id) as site_count 
                               FROM projects p 
                               JOIN bids b ON p.tender_id = b.id")->fetchAll();
        }
    }

    public static function getAuditRiskScores() {
        $db = Database::getInstance();
        try {
            return $db->query("SELECT s.site_name, p.project_name, p.risk_score 
                               FROM sites s 
                               JOIN projects p ON s.project_id = p.id 
                               ORDER BY p.risk_score DESC LIMIT 10")->fetchAll();
        } catch (Exception $e) {
            return $db->query("SELECT s.site_name, p.project_name, 0 as risk_score 
                               FROM sites s 
                               JOIN projects p ON s.project_id = p.id LIMIT 10")->fetchAll();
        }
    }

    public static function getInventoryOversight() {
        $db = Database::getInstance();
        return [
            'low_stock' => $db->query("SELECT sl.*, p.product_name, s.store_name 
                                      FROM stock_levels sl 
                                      JOIN products p ON sl.product_id = p.id 
                                      JOIN stores s ON sl.store_id = s.id 
                                      WHERE sl.quantity < 10")->fetchAll(),
            'pending_releases' => $db->query("SELECT mr.*, s.site_name, u.username as requester 
                                             FROM material_requests mr 
                                             JOIN sites s ON mr.site_id = s.id 
                                             JOIN users u ON mr.requested_by = u.id 
                                             WHERE mr.gm_approval_status = 'pending'")->fetchAll()
        ];
    }
}
