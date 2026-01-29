<?php
// modules/hr/messages.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/HRManager.php';

AuthManager::requireRole(['HR_MANAGER', 'GM']);

$announcements = HRManager::getAnnouncements();
$employees = HRManager::getAllEmployees();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'announce') {
        HRManager::postAnnouncement($_POST['title'], $_POST['content'], $_POST['target'], $_SESSION['user_id']);
        
        // NOTIFICATIONS
        require_once __DIR__ . '/../../core/NotificationManager.php';
        NotificationManager::notifyAll('System Announcement', $_POST['title'], "main.php?module=common/announcements");
    } elseif ($_POST['action'] === 'reply') {
        $db = Database::getInstance();
        $recipientId = $_POST['recipient_id'];
        $subject = "Re: " . str_replace("Re: ", "", $_POST['original_subject']); // Avoid "Re: Re: Re:"
        $message = $_POST['reply_message'];
        
        $stmt = $db->prepare("INSERT INTO hr_messages (sender_id, receiver_id, subject, message, is_read, sent_at) VALUES (?, ?, ?, ?, 0, NOW())");
        $stmt->execute([$_SESSION['user_id'], $recipientId, $subject, $message]);
        
        // NOTIFICATIONS
        require_once __DIR__ . '/../../core/NotificationManager.php';
        $preview = (strlen($message) > 100) ? substr($message, 0, 97) . "..." : $message;
        NotificationManager::notifyUser($recipientId, 'HR Re-play: ' . $subject, $preview, "main.php?module=common/my_messages", 'HR');
    } elseif ($_POST['action'] === 'resolve') {
        $db = Database::getInstance();
        $msgId = $_POST['message_id'];
        $stmt = $db->prepare("UPDATE hr_messages SET is_read = 1 WHERE id = ?");
        $stmt->execute([$msgId]);
    }
    echo "<script>window.location.href='main.php?module=hr/messages&success=1';</script>";
    exit();
}
?>

<?php
// Fetch Inbox Messages
$userId = $_SESSION['user_id'];
$db = Database::getInstance();
// Fetch messages where receiver is current user (HR)
// Since we used a specific HR User ID in send_to_hr.php, and now we are logged in as HR, this should match.
// Also fetching generic HR role messages if we implement generic messaging later.
$inboxMessages = $db->query("
    SELECT m.*, u.username as sender_username, r.role_name 
    FROM hr_messages m 
    JOIN users u ON m.sender_id = u.id 
    LEFT JOIN roles r ON u.role_id = r.id 
    WHERE m.receiver_id = $userId 
    ORDER BY m.sent_at DESC
")->fetchAll();
?>

<div class="messages-module">
    <div class="section-header mb-4" style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h2><i class="fas fa-inbox text-gold"></i> HR Communications Center</h2>
            <p class="text-dim">Manage system-wide announcements and incoming personnel inquiries.</p>
        </div>
        <button class="btn-primary-sm" onclick="document.getElementById('announceModal').style.display='flex'">
            <i class="fas fa-bullhorn me-2"></i> New Announcement
        </button>
    </div>

    <div class="row" style="display:flex; gap:1.5rem; flex-wrap: wrap;">
        
        <!-- INBOX SECTION (New Feature) -->
        <div style="flex: 1; min-width: 600px;">
            <div class="glass-card" style="height: 100%; min-height: 500px;">
                <h3 class="mb-3"><i class="fas fa-envelope-open-text text-gold"></i> Inbox (<?= count($inboxMessages) ?>)</h3>
                
                <?php if (empty($inboxMessages)): ?>
                    <div style="text-align: center; padding: 3rem; color: var(--text-dim); border: 1px dashed rgba(255,255,255,0.1); border-radius: 12px;">
                        <i class="fas fa-inbox fa-3x mb-3"></i>
                        <p>No new messages from staff.</p>
                    </div>
                <?php else: ?>
                    <div class="message-list" style="display: flex; flex-direction: column; gap: 1rem; max-height: 600px; overflow-y: auto;">
                        <?php foreach ($inboxMessages as $msg): 
                            $isRead = $msg['is_read'] ? 'opacity: 0.7;' : 'border-left: 4px solid var(--accent-green); background: rgba(0,255,100,0.05);';
                        ?>
                            <div class="glass-card" style="padding: 1.25rem; margin: 0; <?= $isRead ?>">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                                    <div>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <span style="font-weight: bold; color: #fff; font-size: 1.1rem;"><?= htmlspecialchars($msg['subject']) ?></span>
                                            <?php if (!$msg['is_read']): ?>
                                                <span class="badge" style="background: var(--accent-green); color: black; font-size: 0.7rem;">NEW</span>
                                            <?php endif; ?>
                                        </div>
                                        <div style="font-size: 0.8rem; color: var(--text-dim); margin-top: 4px;">
                                            From: <strong style="color: var(--gold);"><?= htmlspecialchars($msg['sender_username']) ?></strong> 
                                            <span style="margin: 0 5px;">•</span> 
                                            <?= htmlspecialchars($msg['role_name'] ?? 'Staff') ?>
                                        </div>
                                    </div>
                                    <div style="font-size: 0.75rem; color: rgba(255,255,255,0.4);">
                                        <?= date('M d, H:i', strtotime($msg['sent_at'])) ?>
                                    </div>
                                </div>
                                <div style="background: rgba(0,0,0,0.2); padding: 1rem; border-radius: 8px; color: rgba(255,255,255,0.9); font-size: 0.9rem; line-height: 1.5;">
                                    <?= nl2br(htmlspecialchars($msg['message'])) ?>
                                </div>
                                <div style="margin-top: 1rem; display: flex; gap: 10px;">
                                    <?php if (!$msg['is_read']): ?>
                                    <form method="POST" style="margin:0;">
                                        <input type="hidden" name="action" value="resolve">
                                        <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                                        <button type="submit" class="btn-secondary-sm" style="font-size: 0.75rem; padding: 0.4rem 1rem;">
                                            <i class="fas fa-check"></i> Mark as Resolved
                                        </button>
                                    </form>
                                    <?php else: ?>
                                        <span class="badge" style="background: rgba(255,255,255,0.1); color: var(--text-dim); padding: 5px 10px;"><i class="fas fa-check-circle"></i> Resolved</span>
                                    <?php endif; ?>

                                    <button onclick="openReplyModal(<?= $msg['sender_id'] ?>, '<?= htmlspecialchars($msg['sender_username'] ?? 'User') ?>', '<?= htmlspecialchars($msg['subject']) ?>')" class="btn-primary-sm" style="font-size: 0.75rem; padding: 0.4rem 1rem;">
                                        <i class="fas fa-reply"></i> Reply
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- RIGHT SIDE: Announcements & Direct Notification -->
        <div style="flex: 0 0 400px; display: flex; flex-direction: column; gap: 1.5rem;">
            
            <!-- Announcements -->
            <div class="glass-card">
                <h3>System Announcements</h3>
                <div class="announcements-container" style="margin-top:1rem; max-height: 400px; overflow-y: auto;">
                    <?php if (empty($announcements)): ?>
                        <div class="text-dim text-center py-4">No active announcements.</div>
                    <?php else: ?>
                        <?php foreach ($announcements as $a): ?>
                            <div class="announcement-item glass-card mb-3" style="padding:1rem; border-left: 4px solid var(--gold); background: rgba(255,255,255,0.02);">
                                <div style="display:flex; justify-content:space-between; margin-bottom: 5px;">
                                    <h5 style="color:var(--gold); margin:0; font-size: 1rem;"><?= htmlspecialchars($a['title']) ?></h5>
                                    <span style="font-size:0.7rem; color:var(--text-dim);"><?= date('M d', strtotime($a['created_at'])) ?></span>
                                </div>
                                <p style="margin:0; font-size:0.85rem; color: rgba(255,255,255,0.7); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    <?= strip_tags($a['content']) ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Direct Notification -->
            <div class="glass-card">
                <h3>Direct Notification</h3>
                <form method="POST" style="margin-top:1rem;">
                    <div class="form-group mb-3">
                        <label style="font-size: 0.8rem; color: var(--text-dim);">Select Recipient</label>
                        <select name="recipient_id" class="form-control" style="width:100%; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); color:white; padding:0.8rem; border-radius:8px;">
                            <?php foreach ($employees as $e): ?>
                                <option value="<?= $e['user_id'] ?>"><?= $e['first_name'] . ' ' . $e['last_name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label style="font-size: 0.8rem; color: var(--text-dim);">Message Content</label>
                        <textarea name="direct_msg" rows="3" style="width:100%; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); border-radius:8px; color:white; padding:0.5rem;"></textarea>
                    </div>
                    <button type="button" class="btn-primary-sm" style="width:100%;">Send Direct Notice</button>
                </form>
            </div>

        </div>
    </div>
</div>

<!-- Announcement Modal -->
<div id="announceModal" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:2000; justify-content:center; align-items:center;">
    <div class="glass-card" style="width:500px; padding:2rem;">
        <h3>Post Official Announcement</h3>
        <form method="POST">
            <input type="hidden" name="action" value="announce">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" required placeholder="e.g. Eid Holiday Notice">
            </div>
            <div class="form-group">
                <label>Target Audience</label>
                <select name="target" style="width:100%; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); color:white; padding:0.8rem; border-radius:8px;">
                    <option value="all">All Employees</option>
                    <option value="office">Office Staff Only</option>
                    <option value="site">Site Staff Only</option>
                </select>
            </div>
            <div class="form-group">
                <label>Content</label>
                <textarea name="content" rows="5" required style="width:100%; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); border-radius:8px; color:white; padding:0.5rem;"></textarea>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:1rem; margin-top:2rem;">
                <button type="button" class="btn-secondary-sm" onclick="document.getElementById('announceModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn-primary-sm">Publish Notice</button>
            </div>
        </form>
    </div>
</div>

<!-- Reply Modal -->
<div id="replyModal" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:2000; justify-content:center; align-items:center;">
    <div class="glass-card" style="width:500px; padding:2rem;">
        <h3><i class="fas fa-reply text-gold"></i> Send Reply</h3>
        <p class="text-dim mb-3">Replying to <span id="replyToName" class="text-white font-bold"></span></p>
        
        <form method="POST">
            <input type="hidden" name="action" value="reply">
            <input type="hidden" name="recipient_id" id="replyRecipientId">
            <input type="hidden" name="original_subject" id="replySubject">
            
            <div class="form-group">
                <label>Your Message</label>
                <textarea name="reply_message" rows="5" required style="width:100%; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); border-radius:8px; color:white; padding:0.8rem; font-family: inherit;"></textarea>
            </div>
            
            <div style="display:flex; justify-content:flex-end; gap:1rem; margin-top:2rem;">
                <button type="button" class="btn-secondary-sm" onclick="document.getElementById('replyModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn-primary-sm">Send Reply</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openReplyModal(senderId, senderName, subject) {
        document.getElementById('replyModal').style.display = 'flex';
        document.getElementById('replyRecipientId').value = senderId;
        document.getElementById('replyToName').innerText = senderName;
        document.getElementById('replySubject').value = subject;
    }
</script>
