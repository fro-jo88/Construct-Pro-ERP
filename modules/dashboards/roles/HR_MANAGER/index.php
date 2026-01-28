<?php
// modules/dashboards/roles/HR_MANAGER/index.php
require_once __DIR__ . '/../../../../includes/AuthManager.php';
require_once __DIR__ . '/../../engine/DashboardEngine.php';

AuthManager::requireRole('HR_MANAGER');

$engine = new DashboardEngine('HR_MANAGER', $_SESSION['user_id']);
$engine->render();
