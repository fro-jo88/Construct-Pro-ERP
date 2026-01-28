<?php
// config/config.php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'wechecha_con');

// App Constants
define('APP_NAME', 'WECHECHA CONSTRUCTION ERP');
define('APP_URL', 'http://localhost/new');

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session Settings
ini_set('session.gc_maxlifetime', 7200); // 2 hours
session_start();
?>
