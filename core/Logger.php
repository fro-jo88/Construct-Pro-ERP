<?php
/**
 * core/Logger.php
 * Centralized logging utility for CONSTRUCT PRO ERP
 * 
 * Replaces scattered echo/debug statements with structured logging.
 * Logs are stored in the system_logs database table.
 */

require_once __DIR__ . '/../includes/Database.php';

class Logger {
    
    const LEVEL_DEBUG = 'DEBUG';
    const LEVEL_INFO = 'INFO';
    const LEVEL_WARNING = 'WARNING';
    const LEVEL_ERROR = 'ERROR';
    const LEVEL_CRITICAL = 'CRITICAL';
    
    private static $enabled = true;
    private static $minLevel = 'DEBUG';
    
    private static $levelPriority = [
        'DEBUG' => 1,
        'INFO' => 2,
        'WARNING' => 3,
        'ERROR' => 4,
        'CRITICAL' => 5
    ];
    
    /**
     * Log a message to the system_logs table
     * 
     * @param string $level Log level (DEBUG, INFO, WARNING, ERROR, CRITICAL)
     * @param string $module Module name (e.g., 'HR', 'Finance', 'GM')
     * @param string $action Action description
     * @param array $context Additional context data
     * @param int|null $user_id User ID (auto-detected from session if null)
     * @return bool Success status
     */
    public static function log($level, $module, $action, $context = [], $user_id = null) {
        if (!self::$enabled) {
            return false;
        }
        
        // Check if this level should be logged
        if (self::$levelPriority[$level] < self::$levelPriority[self::$minLevel]) {
            return false;
        }
        
        // Auto-detect user from session
        if ($user_id === null) {
            $user_id = $_SESSION['user_id'] ?? null;
        }
        
        try {
            $db = Database::getInstance();
            
            // Prepare context as JSON
            $contextJson = !empty($context) ? json_encode($context) : null;
            
            $stmt = $db->prepare("
                INSERT INTO system_logs (user_id, level, module, action, context, ip_address, user_agent, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            return $stmt->execute([
                $user_id,
                $level,
                $module,
                $action,
                $contextJson,
                $_SERVER['REMOTE_ADDR'] ?? null,
                substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)
            ]);
        } catch (Exception $e) {
            // Fail silently if logging fails - don't break the application
            error_log("Logger Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Convenience methods for different log levels
     */
    public static function debug($module, $action, $context = []) {
        return self::log(self::LEVEL_DEBUG, $module, $action, $context);
    }
    
    public static function info($module, $action, $context = []) {
        return self::log(self::LEVEL_INFO, $module, $action, $context);
    }
    
    public static function warning($module, $action, $context = []) {
        return self::log(self::LEVEL_WARNING, $module, $action, $context);
    }
    
    public static function error($module, $action, $context = []) {
        return self::log(self::LEVEL_ERROR, $module, $action, $context);
    }
    
    public static function critical($module, $action, $context = []) {
        return self::log(self::LEVEL_CRITICAL, $module, $action, $context);
    }
    
    /**
     * Log an audit trail entry for compliance
     * 
     * @param string $action Action performed
     * @param string $table_affected Database table affected
     * @param string|null $record_id Record ID affected
     * @param array $details Additional details
     * @return bool Success status
     */
    public static function audit($action, $table_affected, $record_id = null, $details = []) {
        $user_id = $_SESSION['user_id'] ?? null;
        $username = $_SESSION['username'] ?? 'System';
        
        try {
            $db = Database::getInstance();
            
            $stmt = $db->prepare("
                INSERT INTO audit_trail (user_id, username, action, table_affected, record_id, details, ip_address, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            return $stmt->execute([
                $user_id,
                $username,
                $action,
                $table_affected,
                $record_id,
                !empty($details) ? json_encode($details) : null,
                $_SERVER['REMOTE_ADDR'] ?? null
            ]);
        } catch (Exception $e) {
            error_log("Audit Logger Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Retrieve recent logs for GM oversight
     * 
     * @param int $limit Number of records to retrieve
     * @param string|null $level Filter by level
     * @param string|null $module Filter by module
     * @return array Log entries
     */
    public static function getRecentLogs($limit = 100, $level = null, $module = null) {
        try {
            $db = Database::getInstance();
            
            $sql = "SELECT l.*, u.username 
                    FROM system_logs l 
                    LEFT JOIN users u ON l.user_id = u.id 
                    WHERE 1=1";
            $params = [];
            
            if ($level) {
                $sql .= " AND l.level = ?";
                $params[] = $level;
            }
            
            if ($module) {
                $sql .= " AND l.module = ?";
                $params[] = $module;
            }
            
            $sql .= " ORDER BY l.created_at DESC LIMIT ?";
            $params[] = $limit;
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Enable or disable logging
     */
    public static function setEnabled($enabled) {
        self::$enabled = (bool)$enabled;
    }
    
    /**
     * Set minimum log level
     */
    public static function setMinLevel($level) {
        if (isset(self::$levelPriority[$level])) {
            self::$minLevel = $level;
        }
    }
}
?>
