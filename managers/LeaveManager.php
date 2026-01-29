<?php
// managers/LeaveManager.php
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../core/NotificationManager.php';

class LeaveManager {
    
    public static function createLeaveRequest($userId, $data) {
        $db = Database::getInstance();
        
        // Find employee_id for this user to satisfy foreign key constraints
        $stmtEmp = $db->prepare("SELECT id FROM employees WHERE user_id = ?");
        $stmtEmp->execute([$userId]);
        $emp = $stmtEmp->fetch();
        $employeeId = $emp ? $emp['id'] : null;

        $stmt = $db->prepare("INSERT INTO leave_requests (user_id, employee_id, leave_type, start_date, end_date, reason, status) VALUES (?, ?, ?, ?, ?, ?, 'pending_hr')");
        $result = $stmt->execute([
            $userId,
            $employeeId,
            $data['leave_type'],
            $data['start_date'],
            $data['end_date'],
            $data['reason']
        ]);

        if ($result) {
            // Log to system_logs
            self::logToSystem($userId, "Created leave request: " . $data['leave_type']);
            
            // Notify HR
            NotificationManager::notifyRole('HR', 'New Leave Request', "A new leave request has been submitted by " . $_SESSION['username'], "main.php?module=hr/leaves");
        }

        return $result;
    }

    public static function getUserLeaves($userId) {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM leave_requests WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function getPendingLeaves() {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT lr.*, u.username FROM leave_requests lr JOIN users u ON lr.user_id = u.id WHERE lr.status = 'pending_hr' ORDER BY lr.created_at ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function approveLeave($leaveId, $hrId) {
        $db = Database::getInstance();
        
        // Get leave info for notification
        $stmt = $db->prepare("SELECT user_id, leave_type FROM leave_requests WHERE id = ?");
        $stmt->execute([$leaveId]);
        $leave = $stmt->fetch();

        if ($leave) {
            $stmt = $db->prepare("UPDATE leave_requests SET status = 'approved' WHERE id = ?");
            $result = $stmt->execute([$leaveId]);

            if ($result) {
                // Log to system_logs
                self::logToSystem($hrId, "Approved leave request #$leaveId");
                
                // Notify User
                NotificationManager::notifyUser($leave['user_id'], 'Leave Approved', "Your " . $leave['leave_type'] . " request has been approved by HR.", "main.php?module=leave/request");
                
                return true;
            }
        }
        return false;
    }

    public static function rejectLeave($leaveId, $hrId) {
        $db = Database::getInstance();
        
        // Get leave info for notification
        $stmt = $db->prepare("SELECT user_id, leave_type FROM leave_requests WHERE id = ?");
        $stmt->execute([$leaveId]);
        $leave = $stmt->fetch();

        if ($leave) {
            $stmt = $db->prepare("UPDATE leave_requests SET status = 'rejected' WHERE id = ?");
            $result = $stmt->execute([$leaveId]);

            if ($result) {
                // Log to system_logs
                self::logToSystem($hrId, "Rejected leave request #$leaveId");
                
                // Notify User
                NotificationManager::notifyUser($leave['user_id'], 'Leave Rejected', "Your " . $leave['leave_type'] . " request has been rejected by HR.", "main.php?module=leave/request");
                
                return true;
            }
        }
        return false;
    }

    public static function getLeaveHistory() {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT lr.*, u.username FROM leave_requests lr JOIN users u ON lr.user_id = u.id WHERE lr.status != 'pending_hr' ORDER BY lr.created_at DESC LIMIT 100");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private static function logToSystem($userId, $details) {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("INSERT INTO system_logs (user_id, action_type, module, details, created_at) VALUES (?, 'LEAVE_ACTION', 'HR_LEAVE', ?, NOW())");
            $stmt->execute([$userId, $details]);
        } catch (Exception $e) {
            // Log table might not be ready
        }
    }
}
