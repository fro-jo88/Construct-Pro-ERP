<?php
// modules/gm/dashboard.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/GMManager.php';

AuthManager::requireRole('GM');

$db = Database::getInstance();

// Handle Incident Acknowledgment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'acknowledge_incident') {
    $incident_id = $_POST['incident_id'];
    $stmt = $db->prepare("UPDATE site_incidents SET gm_acknowledged = 1, gm_acknowledgment_at = NOW() WHERE id = ?");
    $stmt->execute([$incident_id]);
}

$kpis = GMManager::getExecutiveKPIs();
$alerts = GMManager::getCriticalAlerts();
$projects = GMManager::getProjectOversight();
?>

<div class="gm-dashboard">
    <!-- 1. Executive KPIs -->
    <div class="kpi-grid mb-4">
        <div class="glass-card kpi-card">
            <span class="kpi-label">Active Projects</span>
            <div class="kpi-value text-gold"><?= $kpis['active_projects'] ?></div>
            <div class="kpi-sub"><?= $kpis['workforce_count'] ?> Total Personnel</div>
        </div>
        <div class="glass-card kpi-card">
            <span class="kpi-label">Budget Utilization</span>
            <div class="kpi-value"><?= $kpis['budget_utilization'] ?>%</div>
            <div class="progress-mini"><div class="progress-bar" style="width: <?= $kpis['budget_utilization'] ?>%"></div></div>
        </div>
        <div class="glass-card kpi-card">
            <span class="kpi-label">Pending Approvals</span>
            <div class="kpi-value <?= $kpis['pending_approvals'] > 0 ? 'text-warn' : '' ?>"><?= $kpis['pending_approvals'] ?></div>
            <a href="main.php?module=gm/approvals" class="kpi-link">View Queue <i class="fas fa-chevron-right"></i></a>
        </div>
        <div class="glass-card kpi-card">
            <span class="kpi-label">Cash Exposure</span>
            <div class="kpi-value">$<?= $kpis['cash_exposure'] ?></div>
            <span class="kpi-sub">Total Committed</span>
        </div>
    </div>

    <div style="display:grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
        <!-- Left Column -->
        <div style="display:flex; flex-direction:column; gap:1.5rem;">
            <!-- Emergency / Alerts -->
            <?php if (!empty($alerts)): ?>
            <section class="glass-card risk-section">
                <h3 class="text-warn"><i class="fas fa-exclamation-triangle"></i> Critical Operational Alerts</h3>
                <div class="alert-stack">
                    <?php foreach ($alerts as $alert): ?>
                    <div class="alert-item risk-<?= strtolower($alert['severity']) ?>">
                        <div class="alert-meta">
                            <span class="alert-badge"><?= $alert['type'] ?></span>
                            <span class="alert-time"><?= date('H:i', strtotime($alert['created_at'])) ?></span>
                        </div>
                        <div class="alert-body"><?= htmlspecialchars($alert['message']) ?></div>
                        <div class="alert-actions">
                            <form method="POST" style="margin:0;">
                                <input type="hidden" name="action" value="acknowledge_incident">
                                <input type="hidden" name="incident_id" value="<?= $alert['id'] ?>">
                                <button type="submit" class="btn-primary-sm btn-gold">Acknowledge</button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- Project Oversight -->
            <section class="glass-card">
                <div class="section-header">
                    <h3><i class="fas fa-project-diagram"></i> Project Portfolio Oversight</h3>
                    <button class="btn-secondary-sm">Full Report</button>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Client</th>
                            <th>Sites</th>
                            <th>Progress</th>
                            <th>Risk Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $p): ?>
                        <tr>
                            <td><strong><?= $p['project_name'] ?></strong><br><small><?= $p['project_code'] ?></small></td>
                            <td><?= $p['client_name'] ?></td>
                            <td><?= $p['site_count'] ?> Active</td>
                            <td>
                                <div class="progress-container">
                                    <span class="progress-val"><?= $p['progress_percent'] ?>%</span>
                                    <div class="progress-bar-wrap"><div class="progress-bar-fill" style="width: <?= $p['progress_percent'] ?>%"></div></div>
                                </div>
                            </td>
                            <td>
                                <span class="risk-indicator" style="background: <?= $p['risk_score'] > 50 ? '#ff4444' : '#00ff64' ?>"></span>
                                <?= $p['risk_score'] ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </div>

        <!-- Right Column -->
        <div style="display:flex; flex-direction:column; gap:1.5rem;">
            <!-- Fast Approval Summary -->
            <section class="glass-card">
                <h3><i class="fas fa-stamp"></i> Approval Pulse</h3>
                <div class="pulse-list">
                    <div class="pulse-item">
                        <span>Personnel Onboarding</span>
                        <span class="badge"><?= $kpis['pending_approvals'] ?></span>
                    </div>
                    <div class="pulse-item">
                        <span>Budget Increments</span>
                        <span class="badge">0</span>
                    </div>
                    <div class="pulse-item">
                        <span>Tender Finalizations</span>
                        <span class="badge">0</span>
                    </div>
                </div>
                <a href="main.php?module=gm/approvals" class="btn-primary-sm w-100 text-center mt-3" style="display:block; text-decoration:none;">Go to Command Center</a>
            </section>

            <!-- Audit Snapshot -->
            <section class="glass-card">
                <h3><i class="fas fa-shield-alt"></i> Compliance Score</h3>
                <div class="compliance-viz">
                    <div class="score-circle">
                        <span class="score-num">92</span>
                        <span class="score-text">System Health</span>
                    </div>
                </div>
                <div class="audit-alerts mt-3">
                    <div class="audit-mini-alert">
                        <i class="fas fa-check-circle text-green"></i> No major non-compliance
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<style>
.gm-dashboard .kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
}
.kpi-card { padding: 1.5rem; position: relative; }
.kpi-label { font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; }
.kpi-value { font-size: 2rem; font-weight: bold; margin: 0.5rem 0; }
.kpi-sub { font-size: 0.7rem; color: var(--text-dim); }
.text-gold { color: var(--gold); }
.text-warn { color: #ffcc00; }
.kpi-link { font-size: 0.7rem; color: var(--gold); text-decoration: none; }

.progress-mini { height: 4px; background: rgba(255,255,255,0.1); border-radius: 2px; margin-top: 1rem; }
.progress-bar { height: 100%; background: var(--gold); border-radius: 2px; }

.alert-stack { display: flex; flex-direction: column; gap: 1rem; margin-top: 1rem; }
.alert-item { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-left: 4px solid #ff4444; padding: 1rem; border-radius: 8px; }
.alert-meta { display: flex; justify-content: space-between; margin-bottom: 0.5rem; }
.alert-badge { font-size: 0.6rem; background: #ff4444; color: white; padding: 2px 6px; border-radius: 4px; font-weight: bold; }
.alert-body { font-size: 0.9rem; }
.alert-actions { margin-top: 0.8rem; display: flex; justify-content: flex-end; }

.progress-container { display: flex; align-items: center; gap: 0.5rem; }
.progress-bar-wrap { flex: 1; height: 6px; background: rgba(255,255,255,0.05); border-radius: 3px; }
.progress-bar-fill { height: 100%; background: #00ff64; border-radius: 3px; }
.progress-val { font-size: 0.75rem; font-weight: bold; min-width: 30px; }

.risk-indicator { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: 5px; }

.pulse-item { display: flex; justify-content: space-between; padding: 0.8rem 0; border-bottom: 1px solid rgba(255,255,255,0.05); }
.pulse-item .badge { background: var(--gold); color: black; font-weight: bold; padding: 2px 8px; border-radius: 50%; font-size: 0.7rem; }

.compliance-viz { display: flex; justify-content: center; padding: 2rem 0; }
.score-circle { width: 120px; height: 120px; border: 8px solid var(--gold); border-radius: 50%; display: flex; flex-direction: column; justify-content: center; align-items: center; box-shadow: 0 0 20px rgba(255,204,0,0.2); }
.score-num { font-size: 2.5rem; font-weight: bold; color: var(--gold); }
.score-text { font-size: 0.6rem; color: var(--text-dim); text-transform: uppercase; }

.audit-mini-alert { background: rgba(0,255,100,0.05); color: #00ff64; font-size: 0.75rem; padding: 0.5rem; border-radius: 6px; text-align: center; border: 1px solid rgba(0,255,100,0.1); }
</style>
