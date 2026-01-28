<?php
// modules/dashboards/roles/SYSTEM_ADMIN/index.php
require_once __DIR__ . '/../../../../includes/AuthManager.php';
require_once __DIR__ . '/../../engine/DashboardEngine.php';

AuthManager::requireRole('SYSTEM_ADMIN');

$engine = new DashboardEngine('SYSTEM_ADMIN', $_SESSION['user_id']);
$engine->render();
