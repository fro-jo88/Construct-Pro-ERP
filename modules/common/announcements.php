<?php
// modules/common/announcements.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/HRManager.php';

// Allow any logged in user
if (!AuthManager::isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$announcements = HRManager::getAnnouncements();
?>

<div class="announcements-module">
    <div class="section-header mb-4">
        <h2><i class="fas fa-bullhorn text-gold"></i> Company Announcements</h2>
        <p class="text-dim">Stay updated with the latest news and notices from management.</p>
    </div>

    <div class="row">
        <div class="col-12">
            <?php if (empty($announcements)): ?>
                <div class="glass-card text-center" style="padding: 4rem;">
                    <i class="fas fa-comment-slash fa-3x mb-3" style="opacity: 0.2;"></i>
                    <p class="text-dim">No announcements at this time.</p>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <?php foreach ($announcements as $a): ?>
                        <div class="glass-card announcement-card" style="border-left: 4px solid var(--gold);">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                                <div>
                                    <h3 style="color: var(--gold); margin: 0; font-size: 1.25rem;"><?= htmlspecialchars($a['title']) ?></h3>
                                    <div style="font-size: 0.8rem; color: var(--text-dim); margin-top: 5px;">
                                        Posted by <strong><?= htmlspecialchars($a['username']) ?></strong> • <?= date('F d, Y \a\t H:i', strtotime($a['created_at'])) ?>
                                    </div>
                                </div>
                                <span class="badge" style="background: rgba(255, 204, 0, 0.1); color: var(--gold); border: 1px solid var(--gold); font-size: 0.7rem;">OFFICIAL</span>
                            </div>
                            <div style="color: rgba(255,255,255,0.9); line-height: 1.6; font-size: 1rem; white-space: pre-wrap; background: rgba(0,0,0,0.2); padding: 1.5rem; border-radius: 12px;">
                                <?= nl2br(htmlspecialchars($a['content'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.announcement-card {
    transition: transform 0.2s;
}
.announcement-card:hover {
    transform: translateY(-5px);
}
</style>
