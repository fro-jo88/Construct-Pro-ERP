<?php
// modules/site/forman_dashboard/messages.php

$db = Database::getInstance();
$user_id = $_SESSION['user_id'];

// Simple messaging view - Fetch direct messages from GM
$messages = $db->query("SELECT m.*, u.username as sender_name 
                        FROM hr_messages m 
                        JOIN users u ON m.sender_id = u.id 
                        WHERE m.recipient_user_id = ? 
                        AND u.role_id = (SELECT id FROM roles WHERE role_name IN ('GM', 'SYSTEM_ADMIN'))
                        ORDER BY m.created_at DESC", [$user_id])->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">GM Instructions</h3>
    <span class="text-secondary text-sm">Direct channel to management</span>
</div>

<div class="glass-panel p-4" style="max-width: 800px; margin: 0 auto;">
    <?php foreach ($messages as $msg): ?>
        <div class="mb-4 d-flex gap-3">
            <div class="rounded-circle bg-gold d-flex align-items-center justify-content-center flex-shrink-0" style="width: 45px; height: 45px; color: #000;">
                <i class="fas fa-crown"></i>
            </div>
            <div class="flex-grow-1 p-3 rounded-3" style="background: rgba(245,158,11,0.05); border: 1px solid rgba(245,158,11,0.1);">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold text-gold text-uppercase" style="letter-spacing: 1px; font-size: 0.7rem;"><?= $msg['sender_name'] ?> (GM)</span>
                    <span class="text-xs text-secondary"><?= date('M d, H:i', strtotime($msg['created_at'])) ?></span>
                </div>
                <h6 class="fw-bold mb-1"><?= htmlspecialchars($msg['title'] ?: 'Instruction') ?></h6>
                <p class="text-sm mb-0 text-white opacity-90"><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($messages)): ?>
        <div class="text-center py-5 opacity-30">
            <i class="fas fa-comment-slash fa-4x mb-3"></i>
            <p>No direct instructions from the GM yet.</p>
        </div>
    <?php endif; ?>

    <div class="mt-5 pt-4 border-top border-secondary">
        <p class="text-xs text-secondary mb-3"><i class="fas fa-info-circle me-1"></i> Use this panel to respond to GM queries regarding your daily reports.</p>
        <div class="input-group">
            <input type="text" class="form-control bg-dark text-white border-secondary" placeholder="Type a clarification to GM...">
            <button class="btn btn-warning fw-bold text-dark px-4">SEND</button>
        </div>
    </div>
</div>
