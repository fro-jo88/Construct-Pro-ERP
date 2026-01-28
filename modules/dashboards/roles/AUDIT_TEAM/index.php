<?php
// modules/dashboards/roles/AUDIT_TEAM/index.php
require_once __DIR__ . '/../../../../includes/AuthManager.php';
require_once __DIR__ . '/../../engine/DashboardEngine.php';

AuthManager::requireRole('AUDIT_TEAM');

$engine = new DashboardEngine('AUDIT_TEAM', $_SESSION['user_id']);
$engine->render();
