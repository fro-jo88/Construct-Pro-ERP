<?php
// modules/messages/send_to_hr.php

require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/Database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!AuthManager::isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}



if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$db = Database::getInstance();
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Get form data
$subject = $_POST['subject'] ?? '';
$priority = $_POST['priority'] ?? 'normal';
$message = $_POST['message'] ?? '';

// Validate required fields
if (empty($subject) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Subject and message are required']);
    exit();
}

// STRICT BUSINESS RULE: Leave requests must use the Leave Module
if (stripos($subject, 'leave') !== false || stripos($message, 'leave request') !== false) {
    echo json_encode(['success' => false, 'message' => 'Leave requests must be submitted through the dedicated Leave Request module.']);
    exit();
}

try {
    // Get HR Manager user ID
    $hrManager = $db->query("SELECT u.id FROM users u 
                            JOIN roles r ON u.role_id = r.id 
                            WHERE r.role_code = 'HR_MANAGER' 
                            LIMIT 1")->fetch();
    
    if (!$hrManager) {
        echo json_encode(['success' => false, 'message' => 'HR Manager not found in system']);
        exit();
    }
    
    // Prepare final message with priority prefix if needed
    $final_message = "[Priority: " . ucfirst($priority) . "] " . $message;

    // Insert message into database
    // Schema: id, sender_id, receiver_id, subject, message, is_read, sent_at
    $stmt = $db->prepare("INSERT INTO hr_messages 
                         (sender_id, receiver_id, subject, message, is_read, sent_at) 
                         VALUES (?, ?, ?, ?, 0, NOW())");
    
    $stmt->execute([
        $user_id,
        $hrManager['id'],
        $subject,
        $final_message
    ]);
    
    // NOTIFICATIONS
    require_once __DIR__ . '/../../core/NotificationManager.php';
    NotificationManager::notifyRole('HR', 'New Direct Message', "From $username: $subject", "main.php?module=hr/messages");

    $message_id = $db->lastInsertId();
    
    // Log the action
    try {
        $logStmt = $db->prepare("INSERT INTO system_logs 
                                (user_id, action_type, module, details, created_at) 
                                VALUES (?, 'MESSAGE_SENT', 'HR_COMMUNICATION', ?, NOW())");
        $logStmt->execute([
            $user_id,
            "Driver Manager sent message to HR: $subject"
        ]);
    } catch (Exception $e) {
        // Log table might not exist, continue anyway
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Message sent successfully to HR department',
        'message_id' => $message_id
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
