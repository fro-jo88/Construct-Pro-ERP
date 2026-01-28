<?php
// modules/dashboards/roles/PLANNING_ENGINEER/index.php
require_once __DIR__ . '/../../../../includes/AuthManager.php';
require_once __DIR__ . '/../../engine/DashboardEngine.php';

AuthManager::requireRole('PLANNING_ENGINEER');

$engine = new DashboardEngine('PLANNING_ENGINEER', $_SESSION['user_id']);
$engine->render();
