<?php
// modules/dashboards/roles/PURCHASE_OFFICER/index.php
require_once __DIR__ . '/../../../../includes/AuthManager.php';
require_once __DIR__ . '/../../engine/DashboardEngine.php';

AuthManager::requireRole('PURCHASE_OFFICER');

$engine = new DashboardEngine('PURCHASE_OFFICER', $_SESSION['user_id']);
$engine->render();
