<?php
// modules/dashboards/roles/DRIVER_MANAGER/index.php
require_once __DIR__ . '/../../../../includes/AuthManager.php';
require_once __DIR__ . '/../../engine/DashboardEngine.php';

AuthManager::requireRole('DRIVER_MANAGER');

$engine = new DashboardEngine('DRIVER_MANAGER', $_SESSION['user_id']);
$engine->render();
