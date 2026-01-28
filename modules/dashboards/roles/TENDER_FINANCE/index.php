<?php
// modules/dashboards/roles/TENDER_FINANCE/index.php
require_once __DIR__ . '/../../../../includes/AuthManager.php';
require_once __DIR__ . '/../../engine/DashboardEngine.php';

AuthManager::requireRole('TENDER_FINANCE');

$engine = new DashboardEngine('TENDER_FINANCE', $_SESSION['user_id']);
$engine->render();
