<?php
/**
 * core/Router.php
 * Centralized routing utility for CONSTRUCT PRO ERP
 * 
 * Provides standardized module loading to replace hardcoded includes.
 * Usage: Router::load('hr', 'employees') instead of include 'modules/hr/employees.php'
 */

class Router {
    
    private static $basePath = null;
    
    /**
     * Get the base path for the application
     */
    private static function getBasePath() {
        if (self::$basePath === null) {
            self::$basePath = dirname(__DIR__);
        }
        return self::$basePath;
    }
    
    /**
     * Load a module by role and module name
     * 
     * @param string $role Role code (e.g., 'hr', 'gm', 'finance')
     * @param string $module Module name (e.g., 'employees', 'dashboard')
     * @param array $params Optional parameters to pass to the module
     * @return bool True if module was loaded successfully
     */
    public static function load($role, $module, $params = []) {
        $role = strtolower($role);
        $module = strtolower($module);
        
        // Security: validate role and module patterns
        if (!preg_match('/^[a-z_]+$/', $role) || !preg_match('/^[a-z0-9_\/]+$/', $module)) {
            self::notFound("Invalid module path: $role/$module");
            return false;
        }
        
        // Build possible paths (check multiple locations)
        $paths = [
            self::getBasePath() . "/modules/{$role}/{$module}.php",
            self::getBasePath() . "/modules/{$role}/{$module}/index.php",
            self::getBasePath() . "/modules/{$module}.php"
        ];
        
        foreach ($paths as $path) {
            if (file_exists($path)) {
                // Make params available to the module
                if (!empty($params)) {
                    extract($params, EXTR_PREFIX_ALL, 'route');
                }
                include $path;
                return true;
            }
        }
        
        self::notFound("Module not found: $role/$module");
        return false;
    }
    
    /**
     * Load a dashboard widget
     * 
     * @param string $widget Widget name
     * @param array $params Widget parameters
     * @return bool True if widget was loaded successfully
     */
    public static function widget($widget, $params = []) {
        $widget = strtolower($widget);
        
        if (!preg_match('/^[a-z_]+$/', $widget)) {
            return false;
        }
        
        $path = self::getBasePath() . "/modules/dashboards/widgets/{$widget}.php";
        
        if (file_exists($path)) {
            // Make params available to the widget
            if (!empty($params)) {
                extract($params, EXTR_PREFIX_ALL, 'widget');
            }
            include $path;
            return true;
        }
        
        return false;
    }
    
    /**
     * Load a role-specific dashboard
     * 
     * @param string $role_code Role code (e.g., 'GM', 'HR_MANAGER')
     * @return bool True if dashboard was loaded
     */
    public static function dashboard($role_code) {
        $role_code = strtoupper($role_code);
        
        if (!preg_match('/^[A-Z_]+$/', $role_code)) {
            self::notFound("Invalid role: $role_code");
            return false;
        }
        
        $path = self::getBasePath() . "/modules/dashboards/roles/{$role_code}/index.php";
        
        if (file_exists($path)) {
            include $path;
            return true;
        }
        
        // Fallback to default dashboard
        $default = self::getBasePath() . "/modules/dashboards/roles/default/index.php";
        if (file_exists($default)) {
            include $default;
            return true;
        }
        
        self::notFound("Dashboard not found: $role_code");
        return false;
    }
    
    /**
     * Redirect to a module
     * 
     * @param string $module Module path
     * @param array $params Query parameters
     */
    public static function redirect($module, $params = []) {
        $url = "main.php?module=" . urlencode($module);
        
        foreach ($params as $key => $value) {
            $url .= "&" . urlencode($key) . "=" . urlencode($value);
        }
        
        header("Location: $url");
        exit();
    }
    
    /**
     * Redirect to the role-specific dashboard
     * 
     * @param string|null $role Role code (defaults to session role)
     */
    public static function redirectToDashboard($role = null) {
        if ($role === null) {
            $role = $_SESSION['role_code'] ?? 'default';
        }
        
        header("Location: main.php?module=dashboards/roles/" . urlencode($role));
        exit();
    }
    
    /**
     * Get the current module from URL
     * 
     * @return string|null Current module or null
     */
    public static function getCurrentModule() {
        return $_GET['module'] ?? null;
    }
    
    /**
     * Check if current module matches a pattern
     * 
     * @param string $pattern Module pattern to match
     * @return bool True if matches
     */
    public static function isModule($pattern) {
        $current = self::getCurrentModule();
        if ($current === null) {
            return false;
        }
        
        // Support wildcard matching
        if (strpos($pattern, '*') !== false) {
            $regex = '/^' . str_replace('*', '.*', preg_quote($pattern, '/')) . '$/';
            return preg_match($regex, $current);
        }
        
        return $current === $pattern;
    }
    
    /**
     * Handle 404 not found
     */
    private static function notFound($message = "Page not found") {
        http_response_code(404);
        echo "<div class='glass-card'>";
        echo "<p style='color:#ff4444; font-weight:bold;'>{$message}</p>";
        echo "</div>";
    }
    
    /**
     * Get breadcrumb array for current module
     * 
     * @return array Breadcrumb items
     */
    public static function getBreadcrumbs() {
        $module = self::getCurrentModule();
        if (!$module) {
            return [['label' => 'Dashboard', 'url' => 'main.php']];
        }
        
        $parts = explode('/', $module);
        $breadcrumbs = [['label' => 'Dashboard', 'url' => 'main.php']];
        
        $path = '';
        foreach ($parts as $part) {
            $path .= ($path ? '/' : '') . $part;
            $breadcrumbs[] = [
                'label' => ucwords(str_replace('_', ' ', $part)),
                'url' => "main.php?module=" . urlencode($path)
            ];
        }
        
        return $breadcrumbs;
    }
}
?>
