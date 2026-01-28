<?php
// modules/dashboards/roles/CONSTRUCTION_AUDIT/index.php
require_once __DIR__ . '/../../../../includes/AuthManager.php';
require_once __DIR__ . '/../../engine/DashboardEngine.php';

AuthManager::requireRole('CONSTRUCTION_AUDIT');

$engine = new DashboardEngine('CONSTRUCTION_AUDIT', $_SESSION['user_id']);
$engine->render();
