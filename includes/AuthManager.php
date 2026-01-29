<?php
// includes/AuthManager.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Database.php';

class AuthManager {
    public static function requireRole($required_role) {
        if (!self::isLoggedIn()) {
            self::safeRedirect("index.php");
        }

        $current_role = $_SESSION['role_code'] ?? '';

        // 1. GM and SYSTEM_ADMIN have global override access
        if ($current_role === 'GM' || $current_role === 'SYSTEM_ADMIN' || $current_role === 'SUPER_ADMIN') {
            return;
        }

        // 2. Check if required_role is an array (multiple allowed roles) or a single string
        $authorized = false;
        if (is_array($required_role)) {
            if (in_array($current_role, $required_role)) {
                $authorized = true;
            }
        } else {
            if ($current_role === strtoupper($required_role)) {
                $authorized = true;
            }
        }

        if (!$authorized) {
            self::safeRedirect("unauthorized.php");
        }
    }

    public static function safeRedirect($url) {
        if (!headers_sent()) {
            header("Location: $url");
        } else {
            echo "<script>window.location.href='$url';</script>";
        }
        exit();
    }

    public static function login($username, $password) {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT u.*, r.role_name, r.role_code FROM users u JOIN roles r ON u.role_id = r.id WHERE u.username = ? AND u.status = 'active'");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role_name'];
            $_SESSION['role_code'] = $user['role_code'];
            $_SESSION['is_logged'] = true;
            
            // Check if it's a demo user (based on username pattern or email)
            $is_demo = strpos($user['username'], 'gm.wendi') === 0 || 
                       strpos($user['username'], 'hr.') === 0 || 
                       strpos($user['username'], 'fin.') === 0 ||
                       strpos($user['username'], 'plan.') === 0 ||
                       strpos($user['username'], 'eng.') === 0 ||
                       strpos($user['username'], 'site.') === 0 ||
                       strpos($user['username'], 'store.') === 0 ||
                       strpos($user['username'], 'proc.') === 0 ||
                       strpos($user['username'], 'trans.') === 0 ||
                       strpos($user['username'], 'drv.') === 0 ||
                       strpos($user['username'], 'audit.') === 0 ||
                       strpos($user['username'], 'sys.') === 0;
            
            $_SESSION['is_demo'] = $is_demo;
            return true;
        }
        return false;
    }

    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public static function logout() {
        session_destroy();
        header("Location: index.php");
        exit();
    }

    public static function checkAccess($permission) {
        // To be implemented: Check against role_permissions table
        return true; 
    }

    public static function restrictDemoAction() {
        // Restriction disabled for development
        return true;
        /*
        if (isset($_SESSION['is_demo']) && $_SESSION['is_demo']) {
            // Log the attempt
            $db = Database::getInstance();
            $stmt = $db->prepare("INSERT INTO audit_logs (user_id, action, table_affected) VALUES (?, 'blocked_demo_action', 'security')");
            $stmt->execute([$_SESSION['user_id']]);
            
            header("Location: " . $_SERVER['HTTP_REFERER'] . "?error=demo_restricted");
            exit();
        }
        */
    }
}
?>
