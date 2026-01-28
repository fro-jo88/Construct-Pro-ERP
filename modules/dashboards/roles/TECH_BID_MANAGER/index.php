<?php
// modules/dashboards/roles/TECH_BID_MANAGER/index.php
require_once __DIR__ . '/../../../../includes/AuthManager.php';
require_once __DIR__ . '/../../engine/DashboardEngine.php';

AuthManager::requireRole('TECH_BID_MANAGER');

$engine = new DashboardEngine('TECH_BID_MANAGER', $_SESSION['user_id']);
$engine->render();
