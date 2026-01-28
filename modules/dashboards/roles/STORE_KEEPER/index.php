<?php
// modules/dashboards/roles/STORE_KEEPER/index.php
require_once __DIR__ . '/../../../../includes/AuthManager.php';
require_once __DIR__ . '/../../engine/DashboardEngine.php';

AuthManager::requireRole('STORE_KEEPER');

$engine = new DashboardEngine('STORE_KEEPER', $_SESSION['user_id']);
$engine->render();
