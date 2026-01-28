<?php
// modules/dashboards/roles/PLANNING_MANAGER/index.php
require_once __DIR__ . '/../../../../includes/AuthManager.php';
require_once __DIR__ . '/../../engine/DashboardEngine.php';

AuthManager::requireRole('PLANNING_MANAGER');

$engine = new DashboardEngine('PLANNING_MANAGER', $_SESSION['user_id']);
$engine->render();
