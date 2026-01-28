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
    }
    header("Location: main.php?module=hr/messages&success=1");
    exit();
}
?>

<div class="messages-module">
    <div class="section-header mb-4" style="display:flex; justify-content:space-between; align-items:center;">
        <h2><i class="fas fa-bullhorn"></i> Communications Hub</h2>
        <button class="btn-primary-sm" onclick="document.getElementById('announceModal').style.display='flex'">+ New Announcement</button>
    </div>

    <div class="row" style="display:flex; gap:1.5rem;">
        <!-- Left: Announcements List -->
        <div style="flex: 2;">
            <div class="glass-card">
                <h3>System-wide Announcements</h3>
                <div class="announcements-container" style="margin-top:1rem;">
                    <?php foreach ($announcements as $a): ?>
                        <div class="announcement-item glass-card mb-3" style="padding:1rem; border-left: 4px solid var(--gold);">
                            <div style="display:flex; justify-content:space-between;">
                                <h4 style="color:var(--gold); margin:0;"><?= htmlspecialchars($a['title']) ?></h4>
                                <span style="font-size:0.75rem; color:var(--text-dim);"><?= date('M d, Y H:i', strtotime($a['created_at'])) ?></span>
                            </div>
                            <p style="margin:0.5rem 0; font-size:0.9rem;"><?= nl2br(htmlspecialchars($a['content'])) ?></p>
                            <div style="font-size:0.75rem; color:var(--text-dim);">Target: <span class="badge" style="background:rgba(255,255,255,0.1);"><?= strtoupper($a['target_group']) ?></span> By: <?= $a['username'] ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Right: Employee Messaging (Simplified for MVP) -->
        <div style="flex: 1;">
            <div class="glass-card">
                <h3>Direct Notification</h3>
                <form method="POST" style="margin-top:1rem;">
                    <div class="form-group">
                        <label>Select Recipient</label>
                        <select name="recipient_id" class="form-control" style="width:100%; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); color:white; padding:0.8rem; border-radius:8px;">
                            <?php foreach ($employees as $e): ?>
                                <option value="<?= $e['user_id'] ?>"><?= $e['first_name'] . ' ' . $e['last_name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Message Content</label>
                        <textarea name="direct_msg" rows="4" style="width:100%; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); border-radius:8px; color:white; padding:0.5rem;"></textarea>
                    </div>
                    <button type="button" class="btn-primary-sm" style="width:100%;">Send Direct Notice</button>
                    <p style="font-size:0.75rem; color:var(--text-dim); margin-top:0.5rem;"><i class="fas fa-info-circle"></i> Direct notices appear in the employee's personal dashboard.</p>
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
