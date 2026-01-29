<?php
// modules/common/my_messages.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/Database.php';

if (!AuthManager::isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$userId = $_SESSION['user_id'];
$db = Database::getInstance();

// Handle Mark as Read
if (isset($_POST['action']) && $_POST['action'] === 'mark_read') {
    $msgId = $_POST['message_id'];
    $stmt = $db->prepare("UPDATE hr_messages SET is_read = 1 WHERE id = ? AND receiver_id = ?");
    $stmt->execute([$msgId, $userId]);
    echo "<script>window.location.href='main.php?module=common/my_messages';</script>";
    exit();
}

// Fetch Messages from HR (or any other sender to this user)
$messages = $db->query("
    SELECT m.*, u.username as sender_name, r.role_name as sender_role
    FROM hr_messages m
    JOIN users u ON m.sender_id = u.id
    LEFT JOIN roles r ON u.role_id = r.id
    WHERE m.receiver_id = $userId
    ORDER BY m.sent_at DESC
")->fetchAll();
?>

<div class="my-messages-module">
    <div class="section-header mb-4">
        <h2><i class="fas fa-comments text-gold"></i> My HR Feedback</h2>
        <p class="text-dim">View replies and direct communications from the HR Department.</p>
    </div>

    <div class="row">
        <div class="col-12">
            <?php if (empty($messages)): ?>
                <div class="glass-card text-center" style="padding: 4rem;">
                    <i class="fas fa-envelope-open fa-3x mb-3" style="opacity: 0.2;"></i>
                    <p class="text-dim">You have no messages from HR at this time.</p>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <?php foreach ($messages as $m): 
                        $unreadClass = !$m['is_read'] ? 'border-left: 4px solid var(--gold); background: rgba(255, 204, 0, 0.03);' : 'opacity: 0.8;';
                    ?>
                        <div class="glass-card message-card" style="<?= $unreadClass ?> padding: 1.5rem;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                                <div>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <h3 style="color: #fff; margin: 0; font-size: 1.1rem;"><?= htmlspecialchars($m['subject']) ?></h3>
                                        <?php if (!$m['is_read']): ?>
                                            <span class="badge" style="background: var(--gold); color: #000; font-size: 0.65rem; font-weight: 800;">NEW RE-PLAY</span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="font-size: 0.8rem; color: var(--text-dim); margin-top: 5px;">
                                        From: <strong style="color: var(--gold);"><?= htmlspecialchars($m['sender_name']) ?></strong> (<?= htmlspecialchars($m['sender_role'] ?? 'Staff') ?>) 
                                        • <?= date('M d, Y H:i', strtotime($m['sent_at'])) ?>
                                    </div>
                                </div>
                                <?php if (!$m['is_read']): ?>
                                    <form method="POST" style="margin: 0;">
                                        <input type="hidden" name="action" value="mark_read">
                                        <input type="hidden" name="message_id" value="<?= $m['id'] ?>">
                                        <button type="submit" class="btn-secondary-sm" style="font-size: 0.7rem;">Mark as Read</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                            <div style="color: rgba(255,255,255,0.9); line-height: 1.6; font-size: 0.95rem; white-space: pre-wrap; background: rgba(0,0,0,0.2); padding: 1.25rem; border-radius: 10px; border: 1px solid rgba(255,255,255,0.03);">
                                <?= nl2br(htmlspecialchars($m['message'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.message-card {
    transition: transform 0.2s, box-shadow 0.2s;
}
.message-card:hover {
    transform: translateX(5px);
    box-shadow: -5px 5px 15px rgba(0,0,0,0.3);
}
</style>
