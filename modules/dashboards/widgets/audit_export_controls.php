<?php
// modules/dashboards/widgets/audit_export_controls.php
?>
<div class="widget glass-card">
    <div class="widget-header">
        <h3><i class="fas fa-file-export text-gold"></i> Export & Compliance Reports</h3>
    </div>
    <div class="widget-content">
        <div class="p-2 text-center" style="background: rgba(255,255,255,0.02); border-radius: 8px;">
            <p class="small text-dim mb-3">Generate audited financial statements for external review or internal compliance archiving.</p>
            
            <div class="row g-2">
                <div class="col-6">
                    <button class="btn-primary-sm w-100 py-3" onclick="alert('Exporting Budget vs Expense to Excel...')">
                        <i class="fas fa-file-excel fa-2x mb-2 d-block"></i>
                        <span>Excel Report</span>
                    </button>
                </div>
                <div class="col-6">
                    <button class="btn-secondary-sm w-100 py-3" onclick="alert('Exporting Expense Ledger to PDF...')">
                        <i class="fas fa-file-pdf fa-2x mb-2 d-block"></i>
                        <span>PDF Ledger</span>
                    </button>
                </div>
            </div>
            
            <div class="mt-3 p-2 rounded" style="background: rgba(0,255,100,0.05); border: 1px solid rgba(0,255,100,0.1);">
                <span class="small text-success"><i class="fas fa-shield-alt mr-1"></i> Data exported matches system-state at <?= date('H:i') ?></span>
            </div>
        </div>
    </div>
</div>
