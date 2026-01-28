<?php
// modules/bidding/finance_dashboard/gm_decision.php
require_once __DIR__ . '/../../../includes/AuthManager.php';
require_once __DIR__ . '/../../../includes/BidManager.php';
require_once __DIR__ . '/../../../includes/ProjectManager.php';

AuthManager::requireRole('GM');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bid_id = $_POST['bid_id'];
    $decision = $_POST['decision']; // 'WON' or 'LOSS'
    $reason = $_POST['reason'] ?? '';

    $db = Database::getInstance();
    $db->beginTransaction();

    try {
        // 1. Update Bid Status
        $stmt = $db->prepare("UPDATE bids SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$decision, $bid_id]);

        // 2. Insert into bid_decisions (Audit Trail)
        $stmt = $db->prepare("INSERT INTO bid_decisions (bid_id, decision, reason, decided_by) VALUES (?, ?, ?, ?)");
        $stmt->execute([$bid_id, $decision, $reason, $_SESSION['user_id']]);

        // 3. If WON, trigger project conversion workflow (Placeholder for actual logic)
        if ($decision === 'WON') {
            // This would normally call ProjectManager::createProjectFromTender
        }

        $db->commit();
        header("Location: main.php?module=bidding/finance_dashboard&success=decision_recorded");
    } catch (Exception $e) {
        $db->rollBack();
        die("Critical Error recording decision: " . $e->getMessage());
    }
}
