<?php
// core/NotificationManager.php
require_once __DIR__ . '/../includes/Database.php';

/**
 * NotificationManager - Centralized Notification System for CONSTRUCT PRO ERP
 * Handles User-specific, Role-specific, and System-wide alerts with individual read tracking.
 */
class NotificationManager {

    /**
     * Notify a specific user.
     */
    public static function notifyUser($userId, $title, $message, $link = null, $role = null) {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO notifications (user_id, role, title, message, link, is_read, created_at) VALUES (?, ?, ?, ?, ?, 0, NOW())");
        $stmt->execute([$userId, $role, $title, $message, $link]);
        
        self::logToSystem("User", $userId, $title);
    }

    /**
     * Notify all users of a specific role.
     * Maps 'HR' to 'HR_MANAGER' to match system roles.
     */
    public static function notifyRole($roleCode, $title, $message, $link = null) {
        $db = Database::getInstance();
        
        // Robust mapping for HR
        $targetRole = ($roleCode === 'HR') ? 'HR_MANAGER' : $roleCode;
        
        // Find all users with this role code
        $stmt = $db->prepare("SELECT u.id FROM users u JOIN roles r ON u.role_id = r.id WHERE r.role_code = ?");
        $stmt->execute([$targetRole]);
        $users = $stmt->fetchAll();

        foreach ($users as $user) {
            // Include the role label in the notification record for categorization
            self::notifyUser($user['id'], $title, $message, $link, $roleCode);
        }
        
        self::logToSystem("Role ($roleCode)", count($users) . " users", $title);
    }

    /**
     * Notify everyone in the system (System-wide Announcement).
     */
    public static function notifyAll($title, $message, $link = null) {
        $db = Database::getInstance();
        $users = $db->query("SELECT id FROM users")->fetchAll();

        foreach ($users as $user) {
            // Null role signifies a system-wide announcement
            self::notifyUser($user['id'], $title, $message, $link, 'SYSTEM');
        }
        
        self::logToSystem("System-wide", "All Users", $title);
    }

    /**
     * Get unread notifications for a user.
     */
    public static function getUnreadByUser($userId) {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 50");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Get count of unread notifications for badge.
     */
    public static function getUnreadCount($userId) {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return (int)($result['count'] ?? 0);
    }

    /**
     * Mark a notification as read.
     */
    public static function markAsRead($notificationId, $userId) {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$notificationId, $userId]);
    }

    /**
     * ERP Guidelines: Log notification event to system_logs.
     */
    private static function logToSystem($targetType, $targetDesc, $title) {
        try {
            $db = Database::getInstance();
            $msg = "Notification Sent: [$title] to $targetType ($targetDesc)";
            $stmt = $db->prepare("INSERT INTO system_logs (user_id, action_type, module, details, created_at) VALUES (?, 'NOTIFICATION_SENT', 'SYSTEM', ?, NOW())");
            $stmt->execute([$_SESSION['user_id'] ?? 0, $msg]);
        } catch (Exception $e) {
            // Silently fail if log table is missing or structural error
        }
    }
}
