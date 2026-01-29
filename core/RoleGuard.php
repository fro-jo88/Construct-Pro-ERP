<?php
/**
 * core/RoleGuard.php
 * Role-based access control middleware for CONSTRUCT PRO ERP
 * 
 * Provides granular permission checking at both role and action level.
 * Works with AuthManager for authentication, adds authorization layer.
 */

require_once __DIR__ . '/../includes/Database.php';

class RoleGuard {
    
    // Role hierarchy - higher roles can access lower role areas
    private static $roleHierarchy = [
        'SUPER_ADMIN' => 100,
        'SYSTEM_ADMIN' => 90,
        'GM' => 80,
        'FINANCE_HEAD' => 60,
        'HR_MANAGER' => 60,
        'PLANNING_MANAGER' => 50,
        'STORE_MANAGER' => 50,
        'DRIVER_MANAGER' => 50,
        'PURCHASE_MANAGER' => 50,
        'TECH_BID_MANAGER' => 45,
        'FINANCE_BID_MANAGER' => 45,
        'CONSTRUCTION_AUDIT' => 45,
        'AUDIT_TEAM' => 45,
        'FINANCE_TEAM' => 40,
        'PLANNING_ENGINEER' => 40,
        'PURCHASE_OFFICER' => 35,
        'STORE_KEEPER' => 35,
        'FORMAN' => 30,
        'DRIVER' => 25,
        'TENDER_TECHNICAL' => 20,

        'default' => 10
    ];
    
    // Module access map - which roles can access which modules
    private static $moduleAccess = [
        'gm/*' => ['GM', 'SUPER_ADMIN', 'SYSTEM_ADMIN'],
        'hr/*' => ['HR_MANAGER', 'GM', 'SUPER_ADMIN', 'SYSTEM_ADMIN'],
        'finance/*' => ['FINANCE_HEAD', 'FINANCE_TEAM', 'AUDIT_TEAM', 'GM', 'SUPER_ADMIN', 'SYSTEM_ADMIN'],
        'bidding/*' => ['TECH_BID_MANAGER', 'FINANCE_BID_MANAGER', 'HR_MANAGER', 'GM', 'SUPER_ADMIN', 'SYSTEM_ADMIN'],
        'planning/*' => ['PLANNING_MANAGER', 'PLANNING_ENGINEER', 'GM', 'SUPER_ADMIN', 'SYSTEM_ADMIN'],
        'store/*' => ['STORE_MANAGER', 'STORE_KEEPER', 'GM', 'SUPER_ADMIN', 'SYSTEM_ADMIN'],
        'transport/*' => ['DRIVER_MANAGER', 'DRIVER', 'GM', 'SUPER_ADMIN', 'SYSTEM_ADMIN'],
        'audit/*' => ['CONSTRUCTION_AUDIT', 'AUDIT_TEAM', 'GM', 'SUPER_ADMIN', 'SYSTEM_ADMIN'],
        'site/*' => ['FORMAN', 'HR_MANAGER', 'PLANNING_MANAGER', 'GM', 'SUPER_ADMIN', 'SYSTEM_ADMIN'],
        'tender/*' => ['TENDER_TECHNICAL', 'TECH_BID_MANAGER', 'FINANCE_BID_MANAGER', 'GM', 'SUPER_ADMIN', 'SYSTEM_ADMIN'],
        'admin/*' => ['SYSTEM_ADMIN', 'SUPER_ADMIN'],
        'leave/*' => ['*'],
        'profile/*' => ['*'],
        'dashboards/*' => ['*'] // All roles can access their own dashboard
    ];
    
    /**
     * Check if current user can access a module
     * 
     * @param string $module Module path
     * @return bool True if access is allowed
     */
    public static function canAccess($module) {
        $userRole = $_SESSION['role_code'] ?? '';
        
        // Super admins have global access
        if (in_array($userRole, ['SUPER_ADMIN', 'SYSTEM_ADMIN', 'GM'])) {
            return true;
        }
        
        // Check against module access map
        foreach (self::$moduleAccess as $pattern => $allowedRoles) {
            if (self::matchesPattern($module, $pattern)) {
                if (in_array('*', $allowedRoles) || in_array($userRole, $allowedRoles)) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Require access to a module, redirect if denied
     * 
     * @param string $module Module path
     * @param string $redirectUrl URL to redirect on failure
     */
    public static function requireAccess($module, $redirectUrl = 'unauthorized.php') {
        if (!self::canAccess($module)) {
            header("Location: $redirectUrl");
            exit();
        }
    }
    
    /**
     * Check if user has a specific permission/capability
     * 
     * @param string $permission Permission name
     * @return bool True if user has permission
     */
    public static function hasPermission($permission) {
        $userRole = $_SESSION['role_code'] ?? '';
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return false;
        }
        
        // Super roles have all permissions
        if (in_array($userRole, ['SUPER_ADMIN', 'SYSTEM_ADMIN'])) {
            return true;
        }
        
        // Check database for role-specific permissions
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                SELECT COUNT(*) FROM role_permissions rp
                JOIN roles r ON rp.role_id = r.id
                WHERE r.role_code = ? AND rp.permission_name = ? AND rp.granted = 1
            ");
            $stmt->execute([$userRole, $permission]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            // If permissions table doesn't exist, fall back to role hierarchy
            return self::checkRoleHierarchyPermission($userRole, $permission);
        }
    }
    
    /**
     * Check if user is at a specific role level or higher
     * 
     * @param string $requiredRole Role to compare against
     * @return bool True if user's role is equal or higher
     */
    public static function isRoleLevel($requiredRole) {
        $userRole = $_SESSION['role_code'] ?? 'default';
        
        $userLevel = self::$roleHierarchy[$userRole] ?? 10;
        $requiredLevel = self::$roleHierarchy[$requiredRole] ?? 10;
        
        return $userLevel >= $requiredLevel;
    }
    
    /**
     * Check if user has one of the specified roles
     * 
     * @param array $roles Array of allowed role codes
     * @return bool True if user has one of the roles
     */
    public static function hasRole($roles) {
        $userRole = $_SESSION['role_code'] ?? '';
        
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        return in_array($userRole, $roles);
    }
    
    /**
     * Get current user's role
     * 
     * @return string Role code
     */
    public static function getCurrentRole() {
        return $_SESSION['role_code'] ?? 'default';
    }
    
    /**
     * Get current user's role level
     * 
     * @return int Role level (higher = more access)
     */
    public static function getCurrentRoleLevel() {
        $role = self::getCurrentRole();
        return self::$roleHierarchy[$role] ?? 10;
    }
    
    /**
     * Check if user is a manager role
     * 
     * @return bool True if user is a manager
     */
    public static function isManager() {
        $managerRoles = [
            'GM', 'HR_MANAGER', 'FINANCE_HEAD', 'PLANNING_MANAGER',
            'STORE_MANAGER', 'DRIVER_MANAGER', 'PURCHASE_MANAGER',
            'TECH_BID_MANAGER', 'FINANCE_BID_MANAGER', 'SUPER_ADMIN', 'SYSTEM_ADMIN'
        ];
        
        return self::hasRole($managerRoles);
    }
    
    /**
     * Check if user is in a specific department
     * 
     * @param string $department Department name
     * @return bool True if in department
     */
    public static function inDepartment($department) {
        $departmentRoles = [
            'HR' => ['HR_MANAGER'],
            'Finance' => ['FINANCE_HEAD', 'FINANCE_TEAM', 'FINANCE_BID_MANAGER', 'AUDIT_TEAM'],
            'Planning' => ['PLANNING_MANAGER', 'PLANNING_ENGINEER'],
            'Store' => ['STORE_MANAGER', 'STORE_KEEPER'],
            'Transport' => ['DRIVER_MANAGER', 'DRIVER'],
            'Technical' => ['TECH_BID_MANAGER', 'TENDER_TECHNICAL'],
            'Audit' => ['CONSTRUCTION_AUDIT', 'AUDIT_TEAM'],
            'Field' => ['FORMAN']
        ];
        
        $allowedRoles = $departmentRoles[$department] ?? [];
        
        // GM and admins are in all departments
        $allowedRoles = array_merge($allowedRoles, ['GM', 'SUPER_ADMIN', 'SYSTEM_ADMIN']);
        
        return self::hasRole($allowedRoles);
    }
    
    /**
     * Match a module path against a pattern with wildcards
     */
    private static function matchesPattern($module, $pattern) {
        if ($pattern === '*') {
            return true;
        }
        
        // Convert pattern to regex
        $regex = '/^' . str_replace(
            ['/', '*'],
            ['\/', '.*'],
            $pattern
        ) . '$/';
        
        return preg_match($regex, $module);
    }
    
    /**
     * Fallback permission check using role hierarchy
     */
    private static function checkRoleHierarchyPermission($role, $permission) {
        // Define minimum level needed for common permissions
        $permissionLevels = [
            'approve_employee' => 60,      // HR_MANAGER and above
            'approve_expense' => 60,       // FINANCE_HEAD and above
            'view_audit_logs' => 45,       // AUDIT roles and above
            'manage_users' => 90,          // SYSTEM_ADMIN and above
            'view_all_projects' => 50,     // Manager roles and above
            'submit_reports' => 30,        // Field staff and above
            'final_approval' => 80         // GM and above
        ];
        
        $requiredLevel = $permissionLevels[$permission] ?? 50;
        $roleLevel = self::$roleHierarchy[$role] ?? 10;
        
        return $roleLevel >= $requiredLevel;
    }
}
?>
