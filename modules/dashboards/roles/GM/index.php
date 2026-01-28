<?php
// modules/dashboards/roles/GM/index.php
require_once __DIR__ . '/../../../../includes/AuthManager.php';
require_once __DIR__ . '/../../engine/DashboardEngine.php';

AuthManager::requireRole('GM');

$engine = new DashboardEngine('GM', $_SESSION['user_id']);
$engine->render();
