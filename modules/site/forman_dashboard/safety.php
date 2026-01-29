<?php
// modules/site/forman_dashboard/safety.php

$db = Database::getInstance();
$site_id = $site['id'];
$user_id = $_SESSION['user_id'];

// Fetch non-resolved incidents
$incidents = $db->query("SELECT * FROM site_incidents WHERE site_id = ? ORDER BY incident_date DESC", [$site_id])->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">Safety & Delay Log</h3>
    <button class="btn btn-danger fw-bold shadow-lg" data-bs-toggle="modal" data-bs-target="#logIncidentModal">
        <i class="fas fa-exclamation-triangle me-2"></i> Report Incident
    </button>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="glass-panel p-4 border-bottom border-4 border-danger">
            <h6 class="fw-bold mb-3">Live Site Risks</h6>
            <div class="bg-dark p-3 rounded-3 mb-3 text-center">
                <i class="fas fa-shield-alt fa-3x text-danger opacity-20 mb-3"></i>
                <p class="text-secondary text-sm mb-0">No primary fatalities or critical safety blockers reported this week.</p>
            </div>
            <button class="btn btn-outline-secondary btn-sm w-100">View Safety Manual</button>
        </div>
    </div>

    <div class="col-md-8">
        <div class="glass-panel">
            <h6 class="fw-bold mb-4">Reported Issues</h6>
            <div class="timeline">
                <?php foreach ($incidents as $inc): ?>
                    <div class="d-flex gap-4 mb-4 border-bottom border-secondary pb-3">
                        <div class="text-center" style="width: 60px;">
                            <div class="h4 fw-bold mb-0"><?= date('d', strtotime($inc['incident_date'])) ?></div>
                            <div class="text-xs text-secondary text-uppercase"><?= date('M', strtotime($inc['incident_date'])) ?></div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h6 class="fw-bold text-white mb-0"><?= htmlspecialchars($inc['title']) ?></h6>
                                <span class="badge bg-<?= $inc['severity'] == 'high' ? 'danger' : ($inc['severity'] == 'medium' ? 'warning' : 'info') ?> text-uppercase">
                                    <?= $inc['severity'] ?>
                                </span>
                            </div>
                            <p class="text-sm text-secondary mb-2"><?= nl2br(htmlspecialchars($inc['description'])) ?></p>
                            <div class="d-flex align-items-center gap-3">
                                <span class="text-xs font-monospace status-badge <?= $inc['status'] == 'resolved' ? 'status-approved' : 'status-revision' ?>">
                                    <?= strtoupper($inc['status']) ?>
                                </span>
                                <?php if ($inc['gm_acknowledged'] ?? false): ?>
                                    <span class="text-xs text-success"><i class="fas fa-check-double me-1"></i> Acknowledged by GM</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($incidents)): ?>
                    <div class="text-center py-5 text-secondary">
                        <i class="fas fa-clipboard-list fa-3x mb-3 opacity-20"></i>
                        <p>No site incidents or significant delays logged.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="logIncidentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark-eval border-danger">
            <form method="POST" action="modules/site/forman_dashboard/save_incident.php">
                <input type="hidden" name="site_id" value="<?= $site_id ?>">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-danger">New Site Report</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary">Issue Title</label>
                        <input type="text" class="form-control bg-dark text-white border-secondary" name="title" placeholder="e.g. Scaffolding Failure / Rain Delay" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary">Severity</label>
                        <select class="form-select bg-dark text-white border-secondary" name="severity">
                            <option value="low">Low (Minor Delay)</option>
                            <option value="medium">Medium (Requires attention)</option>
                            <option value="high">High (Stop work / Accident)</option>
                            <option value="critical">Critical (Immediate Escalation)</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label text-secondary">Detailed Description</label>
                        <textarea class="form-control bg-dark text-white border-secondary" name="description" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger fw-bold">Report to GM & Audit</button>
                </div>
            </form>
        </div>
    </div>
</div>
