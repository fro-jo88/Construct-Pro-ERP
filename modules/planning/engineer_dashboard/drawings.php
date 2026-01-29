<?php
// modules/planning/engineer_dashboard/drawings.php

$db = Database::getInstance();
$user_id = $_SESSION['user_id'];

// Fetch documents for assigned sites
$docs = $db->query("SELECT td.*, t.title as project_title, t.tender_no 
                    FROM tender_documents td
                    JOIN tenders t ON td.tender_id = t.id
                    JOIN sites s ON s.project_id = (SELECT id FROM projects WHERE tender_id = t.id)
                    JOIN site_staff_assignments ssa ON s.id = ssa.site_id
                    WHERE ssa.user_id = ? AND ssa.status = 'active'", [$user_id])->fetchAll();

$categories = [
    'technical' => 'Technical Specs',
    'financial' => 'BOQ / Commercial',
    'legal' => 'Contracts & Legal',
    'other' => 'Miscellaneous'
];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">Project Repository</h3>
    <div class="input-group" style="max-width:300px;">
        <span class="input-group-text bg-dark border-secondary text-secondary"><i class="fas fa-search"></i></span>
        <input type="text" class="form-control bg-dark text-white border-secondary text-sm" placeholder="Search drawings...">
    </div>
</div>

<div class="row g-4">
    <div class="col-md-3">
        <div class="glass-panel p-3">
            <h6 class="text-xs text-secondary text-uppercase mb-3 px-2">Categories</h6>
            <div class="list-group list-group-flush bg-transparent">
                <button class="list-group-item list-group-item-action bg-transparent text-white active border-0 rounded-3 mb-1">
                    <i class="fas fa-folder-open me-2 text-primary"></i> All Documents
                </button>
                <button class="list-group-item list-group-item-action bg-transparent text-secondary border-0 rounded-3 mb-1">
                    <i class="fas fa-drafting-compass me-2"></i> Shop Drawings
                </button>
                <button class="list-group-item list-group-item-action bg-transparent text-secondary border-0 rounded-3 mb-1">
                    <i class="fas fa-file-contract me-2"></i> Specifications
                </button>
                <button class="list-group-item list-group-item-action bg-transparent text-secondary border-0 rounded-3 mb-1">
                    <i class="fas fa-calculator me-2"></i> BOQs
                </button>
            </div>
        </div>
    </div>
    
    <div class="col-md-9">
        <div class="glass-panel">
            <table class="table table-hover align-middle custom-dark-table">
                <thead class="text-secondary text-xs">
                    <tr>
                        <th>DOCUMENT NAME</th>
                        <th>PROJECT SITE</th>
                        <th>TYPE</th>
                        <th>VERSION</th>
                        <th class="text-end">ACTION</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <?php foreach ($docs as $d): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <?php 
                                $icon = 'fa-file-pdf';
                                $ext = pathinfo($d['file_path'], PATHINFO_EXTENSION);
                                if ($ext == 'dwg') $icon = 'fa-pencil-ruler';
                                if ($ext == 'xlsx') $icon = 'fa-file-excel';
                                ?>
                                <i class="fas <?= $icon ?> fa-lg text-danger"></i>
                                <div>
                                    <div class="fw-bold"><?= basename($d['file_path']) ?></div>
                                    <span class="text-xs text-secondary">Added <?= date('M d, Y', strtotime($d['created_at'])) ?></span>
                                </div>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($d['project_title']) ?></td>
                        <td><span class="badge bg-dark border border-secondary"><?= $categories[$d['doc_type']] ?? $d['doc_type'] ?></span></td>
                        <td>v<?= $d['version'] ?></td>
                        <td class="text-end">
                            <a href="<?= htmlspecialchars($d['file_path']) ?>" class="btn btn-icon" download title="Download">
                                <i class="fas fa-download"></i>
                            </a>
                            <button class="btn btn-icon" title="View Details"><i class="fas fa-info-circle"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($docs)): ?>
                        <tr><td colspan="5" class="text-center py-5 text-secondary">No drawings or specifications assigned yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
