<?php
// modules/dashboards/roles/PURCHASE_MANAGER/index.php
require_once __DIR__ . '/../../../../includes/AuthManager.php';
require_once __DIR__ . '/../../engine/DashboardEngine.php';

AuthManager::requireRole('PURCHASE_MANAGER');

$engine = new DashboardEngine('PURCHASE_MANAGER', $_SESSION['user_id']);
$engine->render();
