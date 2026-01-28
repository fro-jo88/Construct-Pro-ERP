<?php
// modules/dashboards/roles/STORE_MANAGER/index.php
require_once __DIR__ . '/../../../../includes/AuthManager.php';
require_once __DIR__ . '/../../engine/DashboardEngine.php';

AuthManager::requireRole('STORE_MANAGER');

$engine = new DashboardEngine('STORE_MANAGER', $_SESSION['user_id']);
$engine->render();
