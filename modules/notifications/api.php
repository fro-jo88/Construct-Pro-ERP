<?php
// modules/notifications/api.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../core/NotificationManager.php';

header('Content-Type: application/json');

if (!AuthManager::isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$action = $_GET['action'] ?? '';
$userId = $_SESSION['user_id'];

try {
    if ($action === 'get_unread') {
        $notifications = NotificationManager::getUnreadByUser($userId);
        $count = NotificationManager::getUnreadCount($userId);
        echo json_encode(['success' => true, 'notifications' => $notifications, 'count' => $count]);

    } elseif ($action === 'mark_read') {
        $id = $_POST['id'] ?? null;
        if ($id) {
            NotificationManager::markAsRead($id, $userId);
            
            // Handle redirect if coming from a direct link click, or JSON if AJAX
            if (isset($_POST['redirect'])) {
                $link = $_POST['redirect'];
                // If it's a local relative link (documented as main.php...), prefix it to go up to root
                if (!preg_match('/^(http|https|\/)/', $link)) {
                    $link = '../../' . $link;
                }
                header("Location: " . $link);
                exit();
            }
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No ID provided']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
