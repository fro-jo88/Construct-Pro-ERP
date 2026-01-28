<?php
// includes/BidManager.php
require_once __DIR__ . '/Database.php';

class BidManager {
    
    public static function getAllBids() {
        $db = Database::getInstance();
        return $db->query("SELECT b.*, u.username as creator_name 
                           FROM bids b 
                           JOIN users u ON b.created_by = u.id 
                           ORDER BY b.created_at DESC")->fetchAll();
    }

    public static function createBid($data, $user_id) {
        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $tender_no = "TND-" . strtoupper(substr(uniqid(), 7));
            $stmt = $db->prepare("INSERT INTO bids (tender_no, title, client_name, description, deadline, status, created_by, submission_mode, bid_file) 
                                 VALUES (?, ?, ?, ?, ?, 'DRAFT', ?, ?, ?)");
            $stmt->execute([
                $tender_no,
                $data['title'],
                $data['client_name'],
                $data['description'],
                $data['deadline'],
                $user_id,
                $data['submission_mode'] ?? 'softcopy',
                $data['bid_file'] ?? null
            ]);
            $bid_id = $db->lastInsertId();

            // Initialize Bidding Tracks
            $db->prepare("INSERT INTO technical_bids (bid_id) VALUES (?)")->execute([$bid_id]);
            $db->prepare("INSERT INTO financial_bids (bid_id) VALUES (?)")->execute([$bid_id]);

            self::logAction($user_id, 'create_bid', "Created bid $tender_no in DRAFT");
            $db->commit();
            return $bid_id;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /* --- Workflow Transitions --- */

    public static function completeTechnical($bid_id, $user_id) {
        $db = Database::getInstance();
        $db->prepare("UPDATE bids SET status = 'TECHNICAL_COMPLETED' WHERE id = ? AND status = 'DRAFT'")->execute([$bid_id]);
        self::logAction($user_id, 'tech_complete', "Technical bid completed for ID $bid_id");
    }

    public static function completeFinancial($bid_id, $user_id) {
        $db = Database::getInstance();
        $db->prepare("UPDATE bids SET status = 'FINANCIAL_COMPLETED' WHERE id = ? AND status = 'TECHNICAL_COMPLETED'")->execute([$bid_id]);
        self::logAction($user_id, 'fin_complete', "Financial bid completed for ID $bid_id");
    }

    public static function gmPreApprove($bid_id, $gm_user_id, $reason = '') {
        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $db->prepare("UPDATE bids SET status = 'GM_PRE_APPROVED' WHERE id = ?")->execute([$bid_id]);
            
            $stmt = $db->prepare("INSERT INTO bid_decisions (bid_id, gm_id, decision, reason) VALUES (?, ?, 'PRE_APPROVED', ?)");
            $stmt->execute([$bid_id, $gm_user_id, $reason]);

            self::logAction($gm_user_id, 'gm_pre_approve', "GM Pre-Approved bid $bid_id for Final Finance Review");
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function confirmFinanceFinal($bid_id, $user_id) {
        $db = Database::getInstance();
        $db->prepare("UPDATE bids SET status = 'FINANCE_FINAL_REVIEW' WHERE id = ? AND status = 'GM_PRE_APPROVED'")->execute([$bid_id]);
        self::logAction($user_id, 'fin_final_review', "Finance Team finalized figures for bid $bid_id");
    }

    public static function gmFinalDecision($bid_id, $decision, $gm_user_id, $reason = '') {
        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $status = ($decision === 'WON') ? 'WON' : 'LOSS';
            $db->prepare("UPDATE bids SET status = ? WHERE id = ?")->execute([$status, $bid_id]);
            
            $stmt = $db->prepare("INSERT INTO bid_decisions (bid_id, gm_id, decision, reason) VALUES (?, ?, ?, ?)");
            $stmt->execute([$bid_id, $gm_user_id, $decision, $reason]);

            if ($status === 'WON') {
                // Auto-create Project & Site
                $stmt = $db->prepare("SELECT * FROM bids WHERE id = ?");
                $stmt->execute([$bid_id]);
                $bid = $stmt->fetch();
                
                $prj_code = "PRJ-" . strtoupper(substr(uniqid(), 7));
                $stmt = $db->prepare("INSERT INTO projects (tender_id, project_code, project_name, status) VALUES (?, ?, ?, 'planned')");
                $stmt->execute([$bid_id, $prj_code, $bid['title']]);
                $project_id = $db->lastInsertId();

                // Create default site
                $db->prepare("INSERT INTO sites (project_id, site_name, location) VALUES (?, 'Main Site', 'TBD')")->execute([$project_id]);
            }

            self::logAction($gm_user_id, 'gm_final_decision', "GM declared $status for bid $bid_id");
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    private static function logAction($user_id, $action, $details) {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO hr_activity_logs (user_id, action_type, details) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $action, $details]);
    }

    // --- 5. FINANCE SUBMISSION & GM FINAL DECISION ---

    // --- 5. FINANCE SUBMISSION & GM FINAL DECISION ---

    public static function submitFinancialBreakdown($bidId, $data, $userId) {
        $db = Database::getInstance();
        $inTransaction = $db->inTransaction();
        if (!$inTransaction) $db->beginTransaction();
        
        try {
            // Update Financial Bid Details
            $stmt = $db->prepare("UPDATE financial_bids SET 
                labor_cost = ?,
                material_cost = ?,
                equipment_cost = ?,
                overhead_cost = ?,
                tax = ?,
                profit_margin_percent = ?,
                total_amount = ?,
                document_path = ?,
                status = 'submitted',
                updated_at = NOW()
                WHERE bid_id = ?");
            
            $stmt->execute([
                $data['labor_cost'],
                $data['material_cost'],
                $data['equipment_cost'],
                $data['overhead_cost'],
                $data['tax'],
                $data['profit_margin'],
                $data['total_amount'],
                $data['document_path'] ?? null,
                $bidId
            ]);

            // Advance Main Bid Status
            $stmt = $db->prepare("UPDATE bids SET status = 'FINANCE_FINAL_REVIEW' WHERE id = ?");
            $stmt->execute([$bidId]);

            // Log
            // Log
            self::logAction($userId, 'financial_submission', "Submitted final financial breakdown for Bid #$bidId. Total: $" . number_format($data['total_amount'], 2));

            if (!$inTransaction) $db->commit();
            return true;
        } catch (Exception $e) {
            if (!$inTransaction) $db->rollBack();
            throw $e;
        }
    }

    public static function getFinancialDetails($bidId) {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM financial_bids WHERE bid_id = ?");
        $stmt->execute([$bidId]);
        return $stmt->fetch();
    }

    public static function getTechnicalBid($bidId) {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM technical_bids WHERE bid_id = ?");
        $stmt->execute([$bidId]);
        return $stmt->fetch();
    }

    public static function getPlanningRequests($bidId) {
        $db = Database::getInstance();
        // Fallback to empty array if table doesn't exist yet
        try {
            $stmt = $db->prepare("SELECT * FROM planning_requests WHERE bid_id = ? ORDER BY created_at DESC");
            $stmt->execute([$bidId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public static function createPlanningRequest($bidId, $userId, $details) {
        $db = Database::getInstance();
        // Check if table exists, create if not (Simplified for demo)
        $db->exec("CREATE TABLE IF NOT EXISTS planning_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            bid_id INT,
            requested_by INT,
            request_details TEXT,
            status VARCHAR(50) DEFAULT 'PENDING',
            output_type VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        $stmt = $db->prepare("INSERT INTO planning_requests (bid_id, requested_by, request_details) VALUES (?, ?, ?)");
        $stmt->execute([$bidId, $userId, $details]);
        return $db->lastInsertId();
    }

    public static function markTechnicalReady($techBidId) {
        $db = Database::getInstance();
        $db->prepare("UPDATE technical_bids SET status = 'ready', updated_at = NOW() WHERE id = ?")->execute([$techBidId]);
    }
}
