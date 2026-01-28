<?php
// modules/dashboards/widgets/gm_reporting_panel.php
require_once __DIR__ . '/../../../includes/Database.php';

$db = Database::getInstance();

?>
<div class="widget glass-card">
    <div class="widget-header">
        <h3><i class="fas fa-paper-plane text-gold"></i> Executive Reports (to GM)</h3>
    </div>
    <div class="widget-content">
        <div class="report-status mb-3 p-2 rounded" style="background: rgba(0, 255, 100, 0.05); border: 1px solid rgba(0, 255, 100, 0.2);">
            <div class="d-flex justify-content-between align-items-center">
                <span class="small"><i class="fas fa-check-circle"></i> Weekly Summary Sent</span>
                <span class="text-dim small"><?= date('M d, H:i') ?></span>
            </div>
        </div>

        <div class="list-group">
            <div class="list-item d-flex justify-content-between mb-2">
                <span class="small">Approved Bids (Monthly)</span>
                <span class="small font-weight-bold">12</span>
            </div>
            <div class="list-item d-flex justify-content-between mb-2">
                <span class="small">Pending GM Action</span>
                <span class="small font-weight-bold text-gold">4</span>
            </div>
            <div class="list-item d-flex justify-content-between mb-2">
                <span class="small">Budget Risk Flags</span>
                <span class="small font-weight-bold text-danger">2</span>
            </div>
        </div>

        <div class="mt-3">
            <button class="btn-primary-sm w-100" style="padding: 10px;">
                <i class="fas fa-file-pdf mr-1"></i> Generate Monthly Financial Report
            </button>
        </div>
    </div>
</div>
