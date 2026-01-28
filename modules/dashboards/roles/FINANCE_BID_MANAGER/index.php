<?php
// modules/dashboards/roles/FINANCE_BID_MANAGER/index.php
require_once __DIR__ . '/../../../../includes/AuthManager.php';
require_once __DIR__ . '/../../engine/DashboardEngine.php';

AuthManager::requireRole('FINANCE_BID_MANAGER');

$engine = new DashboardEngine('FINANCE_BID_MANAGER', $_SESSION['user_id']);
$engine->render();
