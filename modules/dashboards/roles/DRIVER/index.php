<?php
// modules/dashboards/roles/DRIVER/index.php
require_once __DIR__ . '/../../../../includes/AuthManager.php';
require_once __DIR__ . '/../../engine/DashboardEngine.php';

AuthManager::requireRole('DRIVER');

$engine = new DashboardEngine('DRIVER', $_SESSION['user_id']);
$engine->render();
