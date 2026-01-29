<?php
// includes/BidManager.php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../core/Logger.php';

class BidManager {
    
    /**
     * HR Creation: Creates bid + parallel team assignments
     */
    public static function createBid($data, $user_id) {
        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            // 1. Create the Main Bid
            $tender_no = "TND-" . strtoupper(substr(uniqid(), 7));
            $submission_mode = $data['submission_mode'] ?? 'softcopy';

            $stmt = $db->prepare("INSERT INTO bids (tender_no, title, client_name, description, deadline, status, created_by, bid_file, submission_mode) 
                                 VALUES (?, ?, ?, ?, ?, 'under_review', ?, ?, ?)");
            $stmt->execute([
                $tender_no,
                $data['title'],
                $data['client_name'],
                $data['description'],
                $data['deadline'],
                $user_id,
                $data['bid_file'] ?? null,
                $submission_mode
            ]);
            $bid_id = $db->lastInsertId();

            // 1.1 Attach File to bid_files if it exists
            if (!empty($data['bid_file'])) {
                $file_path = $data['bid_file'];
                $file_name = basename($file_path);
                $file_type = pathinfo($file_path, PATHINFO_EXTENSION);
                
                $stmt = $db->prepare("INSERT INTO bid_files (bid_id, file_name, file_path, file_type, uploaded_by) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$bid_id, $file_name, $file_path, $file_type, $user_id]);
            }

            // 2. Automated Parallel Assignment
            self::assignBid($bid_id, 'TECHNICAL');
            self::assignBid($bid_id, 'FINANCIAL');

            // 3. Initialize Data Containers
            $db->prepare("INSERT INTO technical_bids (bid_id, status) VALUES (?, 'draft')")->execute([$bid_id]);
            $db->prepare("INSERT INTO financial_bids (bid_id, status) VALUES (?, 'draft')")->execute([$bid_id]);

            Logger::info('Bidding', "New Bid Created: $tender_no", ['bid_id' => $bid_id, 'by' => $user_id]);
            
            // NOTIFICATIONS
            require_once __DIR__ . '/../core/NotificationManager.php';
            NotificationManager::notifyRole('TECH_BID_MANAGER', 'New Bid Assigned', "Bid #$tender_no requires technical evaluation. Document attached.", "main.php?module=bidding/view&id=$bid_id");
            NotificationManager::notifyRole('FINANCE_BID_MANAGER', 'New Bid Assigned', "Bid #$tender_no requires financial evaluation. Document attached.", "main.php?module=bidding/view&id=$bid_id");

            $db->commit();
            return $bid_id;
        } catch (Exception $e) {
            $db->rollBack();
            Logger::error('Bidding', "Failed to create bid", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public static function getBid($bid_id) {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT b.*, u.username as creator_name FROM bids b LEFT JOIN users u ON b.created_by = u.id WHERE b.id = ?");
        $stmt->execute([$bid_id]);
        return $stmt->fetch();
    }

    public static function getBidFiles($bid_id) {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM bid_files WHERE bid_id = ?");
        $stmt->execute([$bid_id]);
        return $stmt->fetchAll();
    }

    public static function canUserViewBidFile($user_id, $bid_id) {
        $db = Database::getInstance();
        
        // 1. Get User Role
        $stmt = $db->prepare("SELECT r.role_code FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
        $stmt->execute([$user_id]);
        $role_code = $stmt->fetchColumn();

        // 2. System Admin - Full Access
        if ($role_code === 'SYSTEM_ADMIN') return true;

        // 3. Bid Creator - Full Access
        $stmt = $db->prepare("SELECT created_by FROM bids WHERE id = ?");
        $stmt->execute([$bid_id]);
        if ($stmt->fetchColumn() == $user_id) return true;

        // 4. GM - Global Access
        if ($role_code === 'GM') return true;

        // 5. Workflow Roles (Technical / Financial)
        $mapped_workflow_role = null;
        if ($role_code === 'TECH_BID_MANAGER') $mapped_workflow_role = 'TECHNICAL';
        if ($role_code === 'FINANCE_BID_MANAGER') $mapped_workflow_role = 'FINANCIAL';

        if ($mapped_workflow_role) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM bid_assignments WHERE bid_id = ? AND assignment_role = ?");
            $stmt->execute([$bid_id, $mapped_workflow_role]);
            if ($stmt->fetchColumn() > 0) return true;
        }

        return false;
    }

    public static function assignBid($bid_id, $role) {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO bid_assignments (bid_id, assignment_role, status) VALUES (?, ?, 'pending')");
        $stmt->execute([$bid_id, $role]);
    }

    /**
     * Technical Submission
     */
    public static function submitTechnical($bid_id, $tech_data, $user_id) {
        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            // Update Data
            $stmt = $db->prepare("UPDATE technical_bids SET compliance_score = ?, status = 'submitted', updated_at = NOW() WHERE bid_id = ?");
            $stmt->execute([$tech_data['compliance_score'] ?? 0, $bid_id]);

            // Update Assignment
            $stmt = $db->prepare("UPDATE bid_assignments SET status = 'submitted', submitted_at = NOW() WHERE bid_id = ? AND assignment_role = 'TECHNICAL'");
            $stmt->execute([$bid_id]);

            Logger::info('Bidding', "Technical bid submitted for ID $bid_id", ['score' => $tech_data['compliance_score'] ?? 0]);
            
            // Notifications
            require_once __DIR__ . '/../core/NotificationManager.php';
            NotificationManager::notifyRole('HR_MANAGER', 'Technical Bid Submitted', "Technical team has submitted evaluation for Bid #$bid_id", "main.php?module=hr/tenders");
            
            self::checkAndGraduatedToGM($bid_id);
            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Financial Submission
     */
    public static function submitFinancial($bid_id, $finance_data, $user_id) {
        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            // Update Data
            // Note: Financial Dashboard might have already saved data to `financial_bids`. We just update status here if needed.
            // If total_amount is passed, update it.
            if (isset($finance_data['total_amount'])) {
                $stmt = $db->prepare("UPDATE financial_bids SET total_amount = ?, status = 'submitted_to_gm', updated_at = NOW(), submitted_at = NOW() WHERE bid_id = ?");
                $stmt->execute([$finance_data['total_amount'], $bid_id]);
            } else {
                 $stmt = $db->prepare("UPDATE financial_bids SET status = 'submitted_to_gm', updated_at = NOW(), submitted_at = NOW() WHERE bid_id = ?");
                 $stmt->execute([$bid_id]);
            }

            // Update Assignment
            $stmt = $db->prepare("UPDATE bid_assignments SET status = 'submitted', submitted_at = NOW() WHERE bid_id = ? AND assignment_role = 'FINANCIAL'");
            $stmt->execute([$bid_id]);

            Logger::info('Bidding', "Financial bid submitted for ID $bid_id");
            
            // Notifications
            require_once __DIR__ . '/../core/NotificationManager.php';
            NotificationManager::notifyRole('HR_MANAGER', 'Financial Bid Submitted', "Financial team has submitted evaluation for Bid #$bid_id", "main.php?module=hr/tenders");

            self::checkAndGraduatedToGM($bid_id);
            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Completion Logic: Graduation to GM review
     */
    private static function checkAndGraduatedToGM($bid_id) {
        $db = Database::getInstance();
        
        $stmt = $db->prepare("SELECT COUNT(*) FROM bid_assignments WHERE bid_id = ? AND status = 'pending'");
        $stmt->execute([$bid_id]);
        $pending = $stmt->fetchColumn();

        if ($pending == 0) {
            $db->prepare("UPDATE bids SET status = 'ready_for_gm' WHERE id = ?")->execute([$bid_id]);
            Logger::info('Bidding', "Bid #$bid_id is now READY FOR GM", ['bid_id' => $bid_id]);
            
            // Notify GM
            require_once __DIR__ . '/../core/NotificationManager.php';
            NotificationManager::notifyRole('GM', 'Bid Ready for Approval', "Bid #$bid_id has passed all evaluations and is ready for review.", "main.php?module=bidding/gm_review/index");
            
            return true;
        }
        return false;
    }

    /**
     * Access Queries for Dashboards
     */
    public static function getBidsByAssignment($role, $status = 'pending') {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT b.*, ba.status as assignment_status 
                             FROM bids b 
                             JOIN bid_assignments ba ON b.id = ba.bid_id 
                             WHERE ba.assignment_role = ? AND ba.status = ?");
        $stmt->execute([$role, $status]);
        return $stmt->fetchAll();
    }

    public static function getBidsForGM() {
        $db = Database::getInstance();
        return $db->query("SELECT b.*, u.username as creator_name FROM bids b LEFT JOIN users u ON b.created_by = u.id WHERE b.status = 'ready_for_gm' ORDER BY b.created_at DESC")->fetchAll();
    }

    public static function getAllBids() {
        $db = Database::getInstance();
        return $db->query("SELECT b.*, u.username as creator_name FROM bids b LEFT JOIN users u ON b.created_by = u.id ORDER BY b.created_at DESC")->fetchAll();
    }
}
