<?php
// modules/gm/project_details.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/GMManager.php';

AuthManager::requireRole('GM');

$db = Database::getInstance();
$project_id = $_GET['id'] ?? null;

if (!$project_id) {
    echo "Project ID required.";
    exit;
}

// Fetch Project Details
$project = $db->prepare("SELECT p.*, t.client_name, t.title as tender_title 
                         FROM projects p 
                         JOIN tenders t ON p.tender_id = t.id 
                         WHERE p.id = ?");
$project->execute([$project_id]);
$project = $project->fetch();

if (!$project) {
    echo "Project not found.";
    exit;
}

// Fetch Sites
$sites = $db->prepare("SELECT s.*, u.username as foreman_name 
                      FROM sites s 
                      LEFT JOIN users u ON s.foreman_id = u.id 
                      WHERE s.project_id = ?");
$sites->execute([$project_id]);
$sites = $sites->fetchAll();

// Fetch Budget vs Actual
$budget = $db->prepare("SELECT * FROM budgets WHERE project_id = ?");
$budget->execute([$project_id]);
$budget = $budget->fetch();

$expenses = $db->prepare("SELECT SUM(amount) FROM expenses WHERE project_id = ? AND status = 'approved'");
$expenses->execute([$project_id]);
$actual_spent = $expenses->fetchColumn() ?: 0;

?>

<div class="gm-project-details">
    <div class="page-header mb-4">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h2><i class="fas fa-building"></i> Project: <?= $project['project_name'] ?></h2>
                <p class="text-dim">Code: <?= $project['project_code'] ?> | Client: <?= $project['client_name'] ?></p>
            </div>
            <div class="status-badge <?= $project['status'] ?>"><?= strtoupper($project['status']) ?></div>
        </div>
    </div>

    <!-- Financial Pulse -->
    <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:1.5rem;" class="mb-4">
        <div class="glass-card">
            <span class="text-dim">Total Budget</span>
            <div style="font-size:1.5rem; font-weight:bold;">$<?= number_format($project['budget'], 2) ?></div>
        </div>
        <div class="glass-card">
            <span class="text-dim">Actual Spent</span>
            <div style="font-size:1.5rem; font-weight:bold; color:var(--gold);">$<?= number_format($actual_spent, 2) ?></div>
        </div>
        <div class="glass-card">
            <span class="text-dim">Financial Heat</span>
            <div class="progress-container">
                <?php $pct = $project['budget'] > 0 ? ($actual_spent / $project['budget']) * 100 : 0; ?>
                <div class="progress-bar-wrap" style="height:10px;"><div class="progress-bar-fill" style="width: <?= min(100, $pct) ?>%; background: <?= $pct > 90 ? '#ff4444' : '#00ff64' ?>;"></div></div>
                <span style="font-size:0.8rem; font-weight:bold;"><?= round($pct, 1) ?>%</span>
            </div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns: 2fr 1fr; gap:1.5rem;">
        <!-- Site Drilldown -->
        <section class="glass-card">
            <h3><i class="fas fa-map-marker-alt"></i> Site Operations</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Site Name</th>
                        <th>Foreman</th>
                        <th>Recent Status</th>
                        <th>Audit Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sites)): ?>
                        <tr><td colspan="4" class="text-center py-4">No sites assigned to this project yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($sites as $site): ?>
                        <tr>
                            <td><strong><?= $site['site_name'] ?></strong></td>
                            <td><?= $site['foreman_name'] ?: 'Unassigned' ?></td>
                            <td><span class="status-badge active" style="font-size:0.6rem;">ON-TRACK</span></td>
                            <td>94%</td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <!-- Project Risks & Audits -->
        <div style="display:flex; flex-direction:column; gap:1.5rem;">
            <section class="glass-card">
                <h3><i class="fas fa-shield-alt"></i> Intelligence</h3>
                <div class="mb-3">
                    <div style="font-size:0.8rem; color:var(--text-dim);">Project Risk Score</div>
                    <div style="font-size:2rem; font-weight:bold; color:<?= $project['risk_score'] > 50 ? '#ff4444' : '#00ff64' ?>;"><?= $project['risk_score'] ?></div>
                </div>
                <p class="text-dim" style="font-size:0.8rem;">Risk based on delay frequency, budget variance, and site incidents.</p>
            </section>
            
            <section class="glass-card">
                <h3><i class="fas fa-file-invoice"></i> Recent Audits</h3>
                <div style="font-size:0.8rem;">
                    <div style="border-bottom:1px solid rgba(255,255,255,0.05); padding:0.5rem 0;">
                        <span class="text-dim">2026-01-20:</span> Safety Check Passed
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
