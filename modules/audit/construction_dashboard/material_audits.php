<?php
// modules/audit/construction_dashboard/material_audits.php

$db = Database::getInstance();

// Get filter parameters
$project_id = $_GET['project_id'] ?? null;
$site_id = $_GET['site_id'] ?? null;
$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to = $_GET['date_to'] ?? date('Y-m-d');

// Fetch projects and sites for filters
$projects = $db->query("SELECT * FROM projects WHERE status = 'active' ORDER BY project_name")->fetchAll();
$sites = $db->query("SELECT * FROM sites ORDER BY site_name")->fetchAll();

// Fetch material usage data (Planned vs Issued vs Used)
$materialData = [];
if ($site_id) {
    // This cross-references planning data, store issues, and Forman usage reports
    // Simplified demonstration with sample data
    $materialData = $db->query("SELECT 
                                    'Cement (50kg bags)' as material_name,
                                    500.00 as planned_qty,
                                    520.00 as issued_qty,
                                    495.00 as used_qty,
                                    25.00 as variance,
                                    'overuse' as flag,
                                    'Bags' as unit
                                UNION ALL
                                SELECT 
                                    'Steel Rebar (12mm)',
                                    2000.00,
                                    1950.00,
                                    1920.00,
                                    30.00,
                                    'normal',
                                    'Kg'
                                UNION ALL
                                SELECT 
                                    'Concrete Blocks',
                                    1500.00,
                                    1600.00,
                                    1400.00,
                                    200.00,
                                    'overuse',
                                    'Pcs'
                                UNION ALL
                                SELECT 
                                    'Sand (Cubic meters)',
                                    50.00,
                                    48.00,
                                    0.00,
                                    48.00,
                                    'missing_record',
                                    'M³'")->fetchAll();
}

?>

<div class="glass-panel">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="fas fa-boxes text-warning me-2"></i> Material Usage Audit</h4>
        <div class="text-secondary text-sm">Planned vs Issued vs Consumed</div>
    </div>

    <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger mb-4">
        <i class="fas fa-shield-alt me-2"></i> <strong>CRITICAL CONTROL POINT:</strong> Material variance directly impacts project profitability. Flag all discrepancies immediately.
    </div>

    <!-- FILTERS -->
    <form method="GET" class="row g-3 mb-4 p-4 bg-dark bg-opacity-20 rounded-3">
        <input type="hidden" name="module" value="audit/construction_dashboard/index">
        <input type="hidden" name="view" value="material_audits">
        
        <div class="col-md-3">
            <label class="form-label text-secondary text-xs fw-bold">PROJECT</label>
            <select name="project_id" class="form-select bg-dark text-white border-secondary">
                <option value="">-- Select Project --</option>
                <?php foreach ($projects as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $project_id == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['project_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="col-md-3">
            <label class="form-label text-secondary text-xs fw-bold">SITE</label>
            <select name="site_id" class="form-select bg-dark text-white border-secondary" required>
                <option value="">-- Select Site --</option>
                <?php foreach ($sites as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= $site_id == $s['id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['site_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="col-md-2">
            <label class="form-label text-secondary text-xs fw-bold">FROM DATE</label>
            <input type="date" name="date_from" class="form-control bg-dark text-white border-secondary" value="<?= $date_from ?>">
        </div>
        
        <div class="col-md-2">
            <label class="form-label text-secondary text-xs fw-bold">TO DATE</label>
            <input type="date" name="date_to" class="form-control bg-dark text-white border-secondary" value="<?= $date_to ?>">
        </div>
        
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-warning w-100">
                <i class="fas fa-search me-2"></i> Audit
            </button>
        </div>
    </form>

    <?php if ($site_id && !empty($materialData)): ?>
        <!-- MATERIAL USAGE COMPARISON -->
        <div class="table-responsive">
            <table class="table table-custom text-white">
                <thead class="text-secondary text-xs text-uppercase fw-bold">
                    <tr>
                        <th>MATERIAL</th>
                        <th class="text-center">PLANNED QTY</th>
                        <th class="text-center">ISSUED QTY</th>
                        <th class="text-center">USED QTY</th>
                        <th class="text-center">VARIANCE</th>
                        <th class="text-center">VARIANCE %</th>
                        <th class="text-center">FLAG</th>
                        <th class="text-end">ACTION</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <?php foreach ($materialData as $item): 
                        $variance_pct = $item['planned_qty'] > 0 ? ($item['variance'] / $item['planned_qty']) * 100 : 0;
                    ?>
                    <tr class="align-middle">
                        <td>
                            <div class="fw-medium"><?= htmlspecialchars($item['material_name']) ?></div>
                            <div class="text-xs text-secondary">Unit: <?= $item['unit'] ?></div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary bg-opacity-20 text-primary"><?= number_format($item['planned_qty'], 2) ?></span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info bg-opacity-20 text-info"><?= number_format($item['issued_qty'], 2) ?></span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success bg-opacity-20 text-success"><?= number_format($item['used_qty'], 2) ?></span>
                        </td>
                        <td class="text-center">
                            <span class="fw-bold <?= $item['variance'] > 0 ? 'text-danger' : 'text-success' ?>">
                                <?= $item['variance'] > 0 ? '+' : '' ?><?= number_format($item['variance'], 2) ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="fw-bold <?= abs($variance_pct) > 10 ? 'text-danger' : 'text-secondary' ?>">
                                <?= number_format($variance_pct, 1) ?>%
                            </span>
                        </td>
                        <td class="text-center">
                            <?php
                            $flagClass = 'flag-normal';
                            $flagIcon = 'check-circle';
                            $flagText = 'NORMAL';
                            
                            if ($item['flag'] === 'overuse') {
                                $flagClass = 'flag-critical';
                                $flagIcon = 'arrow-up';
                                $flagText = 'OVERUSE';
                            } elseif ($item['flag'] === 'underuse') {
                                $flagClass = 'flag-high';
                                $flagIcon = 'arrow-down';
                                $flagText = 'UNDERUSE';
                            } elseif ($item['flag'] === 'missing_record') {
                                $flagClass = 'flag-critical';
                                $flagIcon = 'exclamation-triangle';
                                $flagText = 'MISSING';
                            }
                            ?>
                            <span class="status-badge <?= $flagClass ?>">
                                <i class="fas fa-<?= $flagIcon ?> me-1"></i> <?= $flagText ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="addMaterialFinding('<?= htmlspecialchars($item['material_name']) ?>', <?= $item['variance'] ?>)">
                                <i class="fas fa-flag me-1"></i> Flag
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="border-top border-secondary">
                    <tr class="fw-bold">
                        <td colspan="4" class="text-end text-warning">TOTAL MATERIAL VARIANCE:</td>
                        <td class="text-center text-danger">
                            <?php 
                            $total_variance = array_sum(array_column($materialData, 'variance'));
                            echo number_format($total_variance, 2);
                            ?>
                        </td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- WORK BALANCE VERIFICATION -->
        <div class="mt-5 p-4 bg-dark bg-opacity-30 rounded-3">
            <h6 class="text-warning fw-bold mb-3">
                <i class="fas fa-balance-scale me-2"></i> Work Balance Summary
            </h6>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="p-3 bg-primary bg-opacity-10 rounded-3 border border-primary border-opacity-20">
                        <div class="text-xs text-secondary mb-1">TOTAL BOQ QUANTITY</div>
                        <div class="h4 fw-bold text-primary mb-0">2,500 Units</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-success bg-opacity-10 rounded-3 border border-success border-opacity-20">
                        <div class="text-xs text-secondary mb-1">EXECUTED QUANTITY</div>
                        <div class="h4 fw-bold text-success mb-0">1,850 Units</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-warning bg-opacity-10 rounded-3 border border-warning border-opacity-20">
                        <div class="text-xs text-secondary mb-1">REMAINING BALANCE</div>
                        <div class="h4 fw-bold text-warning mb-0">650 Units</div>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif ($site_id): ?>
        <div class="alert alert-info border-0 bg-info bg-opacity-10 text-info">
            <i class="fas fa-info-circle me-2"></i> No material audit data available for the selected filters.
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-filter fa-3x text-secondary mb-3 opacity-20"></i>
            <h5 class="text-secondary">Select Site and Date Range</h5>
            <p class="text-muted">Use the filters above to begin material usage audit.</p>
        </div>
    <?php endif; ?>
</div>

<script>
function addMaterialFinding(material, variance) {
    alert('Material Variance Detected:\n\nMaterial: ' + material + '\nVariance: ' + variance + '\n\nThis will open a modal to record detailed audit finding.');
}
</script>
