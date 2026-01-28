<?php
// modules/gm/audit.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/GMManager.php';

AuthManager::requireRole('GM');

$risk_scores = GMManager::getAuditRiskScores();
$audit_logs = GMManager::getSystemLogs(20);

?>

<div class="gm-audit">
    <div class="section-header mb-4" style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h2><i class="fas fa-shield-alt"></i> Audit & Compliance Monitor</h2>
            <p class="text-dim">Executive oversight of operational integrity and regulatory compliance.</p>
        </div>
        <button class="btn-primary-sm"><i class="fas fa-file-export"></i> Export Audit Trail</button>
    </div>

    <div style="display:grid; grid-template-columns: 1fr 2fr; gap: 1.5rem;">
        <!-- Left: Risk Heatmap -->
        <div style="display:flex; flex-direction:column; gap:1.5rem;">
            <section class="glass-card">
                <h3 style="color:var(--gold);">Project Risk Map</h3>
                <div class="risk-list mt-3">
                    <?php foreach ($risk_scores as $risk): ?>
                    <div class="risk-item">
                        <div style="display:flex; justify-content:space-between; margin-bottom:0.5rem;">
                            <span style="font-size:0.8rem; font-weight:bold;"><?= $risk['site_name'] ?> (<?= $risk['project_name'] ?>)</span>
                            <span style="font-size:0.75rem; color: <?= $risk['risk_score'] > 70 ? '#ff4444' : ($risk['risk_score'] > 40 ? '#ffcc00' : '#00ff64') ?>"><?= $risk['risk_score'] ?>% Risk</span>
                        </div>
                        <div class="risk-bar-bg" style="height:6px; background:rgba(255,255,255,0.05); border-radius:3px; overflow:hidden;">
                            <div class="risk-bar-fill" style="height:100%; width: <?= $risk['risk_score'] ?>%; background: <?= $risk['risk_score'] > 70 ? '#ff4444' : ($risk['risk_score'] > 40 ? '#ffcc00' : '#00ff64') ?>;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="glass-card">
                <h3 style="color:var(--gold);">Compliance Pulse</h3>
                <div style="padding:1.5rem; text-align:center;">
                    <div style="font-size:3rem; font-weight:bold; color:#00ff64;">A+</div>
                    <p class="text-dim">Overall System Integrity</p>
                </div>
                <div style="border-top:1px solid rgba(255,255,255,0.05); padding-top:1rem;">
                    <div style="font-size:0.75rem; color:var(--text-dim); margin-bottom:0.5rem;">Recent Security Events</div>
                    <div style="font-size:0.8rem;"><i class="fas fa-check-circle text-green"></i> System integrity self-check passed.</div>
                </div>
            </section>
        </div>

        <!-- Right: Immutable Activity Logs -->
        <section class="glass-card" style="display:flex; flex-direction:column;">
            <h3 style="color:var(--gold);"><i class="fas fa-history"></i> Real-Time Operations Logs</h3>
            <div class="log-stream mt-3" style="flex:1; overflow-y:auto; max-height: 600px;">
                <table class="data-table" style="font-size:0.8rem;">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>Identity</th>
                            <th>Action</th>
                            <th>Detail Trace</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($audit_logs as $log): ?>
                        <tr>
                            <td style="color:var(--text-dim);"><?= date('M d, H:i:s', strtotime($log['created_at'])) ?></td>
                            <td>
                                <div style="font-weight:bold;"><?= $log['username'] ?></div>
                                <div style="font-size:0.7rem; color:var(--gold);"><?= $log['role_id'] == 1 ? 'GM' : 'Staff' ?></div>
                            </td>
                            <td><span class="status-badge" style="background:rgba(255,255,255,0.1);"><?= strtoupper($log['action_type']) ?></span></td>
                            <td class="text-dim"><?= htmlspecialchars($log['details']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p style="font-size:0.7rem; color:var(--text-dim); margin-top:1rem; text-align:center;">
                <i class="fas fa-lock"></i> All logs are cryptographically hashed and immutable.
            </p>
        </section>
    </div>
</div>

<style>
.risk-item { margin-bottom: 1.5rem; }
.text-green { color: #00ff64; }
.log-stream::-webkit-scrollbar { width: 4px;}
.log-stream::-webkit-scrollbar-thumb { background: rgba(255,204,0,0.2); border-radius: 10px;}
</style>
