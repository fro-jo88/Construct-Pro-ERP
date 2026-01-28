<?php
// modules/dashboards/roles/FORMAN/index.php
require_once __DIR__ . '/../../../../includes/AuthManager.php';
require_once __DIR__ . '/../../engine/DashboardEngine.php';

AuthManager::requireRole('FORMAN');

$engine = new DashboardEngine('FORMAN', $_SESSION['user_id']);
$engine->render();
