<?php
// includes/TenderManager.php
require_once __DIR__ . '/Database.php';

class TenderManager {
    public static function getAllTenders() {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT b.*, u.username as creator_name FROM bids b JOIN users u ON b.created_by = u.id ORDER BY b.created_at DESC");
        return $stmt->fetchAll();
    }

    public static function createTender($data) {
        AuthManager::restrictDemoAction();
        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            // 1. Create Bid (Tender Origin)
            $stmt = $db->prepare("INSERT INTO bids (tender_no, title, client_name, description, deadline, status, created_by) VALUES (?, ?, ?, ?, ?, 'DRAFT', ?)");
            $stmt->execute([
                $data['tender_no'],
                $data['title'],
                $data['client_name'],
                $data['description'],
                $data['deadline'],
                $_SESSION['user_id']
            ]);
            $bid_id = $db->lastInsertId();

            // 2. Initialize Bidding Tables (Financial & Technical)
            $db->prepare("INSERT INTO financial_bids (bid_id, status) VALUES (?, 'draft')")->execute([$bid_id]);
            $db->prepare("INSERT INTO technical_bids (bid_id, status) VALUES (?, 'draft')")->execute([$bid_id]);

            $db->commit();
            return $bid_id;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function getTenderWithBids($bid_id) {
        $db = Database::getInstance();
        $tender = $db->prepare("SELECT * FROM bids WHERE id = ?");
        $tender->execute([$bid_id]);
        $t = $tender->fetch();

        if ($t) {
            $fin = $db->prepare("SELECT * FROM financial_bids WHERE bid_id = ?");
            $fin->execute([$bid_id]);
            $t['financial_bid'] = $fin->fetch();

            $tech = $db->prepare("SELECT * FROM technical_bids WHERE bid_id = ?");
            $tech->execute([$bid_id]);
            $t['technical_bid'] = $tech->fetch();
        }
        return $t;
    }

    public static function allowFinalSubmission($tender_id) {
        // Validation Gate: Check if both bids are 'ready'
        $t = self::getTenderWithBids($tender_id);
        if ($t['financial_bid']['status'] === 'ready' && $t['technical_bid']['status'] === 'ready') {
            return true;
        }
        return false;
    }
}
?>
