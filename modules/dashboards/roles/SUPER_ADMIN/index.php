<?php
// modules/dashboards/roles/SUPER_ADMIN/index.php
require_once __DIR__ . '/../../../../includes/AuthManager.php';
require_once __DIR__ . '/../../engine/DashboardEngine.php';

AuthManager::requireRole('SUPER_ADMIN');

$engine = new DashboardEngine('SUPER_ADMIN', $_SESSION['user_id']);
$engine->render();
