<?php
// modules/dashboards/roles/TENDER_TECHNICAL/index.php
require_once __DIR__ . '/../../../../includes/AuthManager.php';
require_once __DIR__ . '/../../engine/DashboardEngine.php';

AuthManager::requireRole('TENDER_TECHNICAL');

$engine = new DashboardEngine('TENDER_TECHNICAL', $_SESSION['user_id']);
$engine->render();
