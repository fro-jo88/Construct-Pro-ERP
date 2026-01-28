<?php
// modules/dashboards/roles/FINANCE_TEAM/index.php
require_once __DIR__ . '/../../../../includes/AuthManager.php';
require_once __DIR__ . '/../../engine/DashboardEngine.php';

AuthManager::requireRole('FINANCE_TEAM');

$engine = new DashboardEngine('FINANCE_TEAM', $_SESSION['user_id']);
$engine->render();
