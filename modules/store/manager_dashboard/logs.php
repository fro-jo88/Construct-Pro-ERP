<?php
// modules/store/manager_dashboard/logs.php

$db = Database::getInstance();

// 1. Issue Logs
$issueLogs = $db->query("SELECT mr.*, s.site_name, p.product_name, u.username as manager_name
                          FROM material_requests mr
                          JOIN sites s ON mr.site_id = s.id
                          JOIN products p ON mr.item_name = p.product_name
                          LEFT JOIN users u ON mr.store_manager_id = u.id
                          WHERE mr.store_manager_approval != 'pending'
                          ORDER BY mr.updated_at DESC LIMIT 50")->fetchAll();

// 2. Transfer Logs
$transferLogs = $db->query("SELECT st.*, p.product_name, s1.store_name as from_store, s2.store_name as to_store
                             FROM stock_transfers st
                             JOIN products p ON st.material_id = p.id
                             JOIN stores s1 ON st.from_store_id = s1.id
                             JOIN stores s2 ON st.to_store_id = s2.id
                             WHERE st.manager_approval != 'pending'
                             ORDER BY st.updated_at DESC LIMIT 50")->fetchAll();

?>

<div class="glass-panel">
    <ul class="nav nav-tabs nav-tabs-custom mb-4" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#issue-audit">Material Issue Audit</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#transfer-audit">Stock Transfer History</button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Issues Audit -->
        <div class="tab-pane fade show active" id="issue-audit">
            <div class="table-responsive">
                <table class="table table-custom text-white">
                    <thead class="text-secondary text-xs fw-bold">
                        <tr>
                            <th>TIMESTAMP</th>
                            <th>REFERENCE</th>
                            <th>SITE</th>
                            <th>MATERIAL</th>
                            <th>MANAGER</th>
                            <th class="text-end">FINAL STATUS</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        <?php foreach ($issueLogs as $il): ?>
                        <tr>
                            <td class="text-secondary"><?= date('M d, H:i', strtotime($il['updated_at'])) ?></td>
                            <td class="font-monospace">#MR-<?= $il['id'] ?></td>
                            <td><?= htmlspecialchars($il['site_name']) ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($il['product_name']) ?></td>
                            <td><span class="text-xs text-info"><?= $il['manager_name'] ?: 'System' ?></span></td>
                            <td class="text-end">
                                <span class="status-badge status-<?= $il['store_manager_approval'] ?>"><?= $il['store_manager_approval'] ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Transfer Audit -->
        <div class="tab-pane fade" id="transfer-audit">
            <div class="table-responsive">
                <table class="table table-custom text-white">
                    <thead class="text-secondary text-xs fw-bold">
                        <tr>
                            <th>TIMESTAMP</th>
                            <th>FROM → TO</th>
                            <th>MATERIAL</th>
                            <th>QTY</th>
                            <th>LOGISTICS STATUS</th>
                            <th class="text-end">DECISION</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        <?php foreach ($transferLogs as $tl): ?>
                        <tr>
                            <td class="text-secondary"><?= date('M d, H:i', strtotime($tl['updated_at'])) ?></td>
                            <td>
                                <div class="text-xs"><?= htmlspecialchars($tl['from_store']) ?></div>
                                <i class="fas fa-arrow-down mx-2 opacity-20"></i>
                                <div class="text-xs text-info"><?= htmlspecialchars($tl['to_store']) ?></div>
                            </td>
                            <td class="fw-bold"><?= htmlspecialchars($tl['product_name']) ?></td>
                            <td><?= $tl['quantity'] ?></td>
                            <td><span class="badge bg-dark border border-secondary"><?= strtoupper($tl['transfer_status']) ?></span></td>
                            <td class="text-end">
                                <span class="status-badge status-<?= $tl['manager_approval'] ?>"><?= $tl['manager_approval'] ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
