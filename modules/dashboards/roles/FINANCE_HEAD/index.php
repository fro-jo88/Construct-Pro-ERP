<?php
// modules/dashboards/roles/FINANCE_HEAD/index.php
require_once __DIR__ . '/../../../../includes/AuthManager.php';
require_once __DIR__ . '/../../engine/DashboardEngine.php';

AuthManager::requireRole('FINANCE_HEAD');

$engine = new DashboardEngine('FINANCE_HEAD', $_SESSION['user_id']);
$engine->render();
