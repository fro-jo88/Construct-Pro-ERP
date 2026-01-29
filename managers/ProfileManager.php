<?php
// managers/ProfileManager.php
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../core/NotificationManager.php';

class ProfileManager {

    /**
     * Get user profile details
     */
    public static function getProfile($userId) {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT u.*, r.role_name, r.role_code 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            WHERE u.id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    /**
     * Update basic profile information
     */
    public static function updateProfile($userId, $data) {
        $db = Database::getInstance();
        
        // Validation
        if (empty($data['full_name'])) throw new Exception("Full name is required.");
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }

        // Get current data for logging comparison
        $current = self::getProfile($userId);
        $changes = [];
        if ($current['full_name'] !== $data['full_name']) $changes[] = "Name changed";
        if ($current['email'] !== $data['email']) $changes[] = "Email changed";
        if ($current['phone'] !== ($data['phone'] ?? '')) $changes[] = "Phone changed";

        $stmt = $db->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, updated_at = NOW() WHERE id = ?");
        $result = $stmt->execute([
            $data['full_name'],
            $data['email'],
            $data['phone'] ?? null,
            $userId
        ]);

        if ($result && !empty($changes)) {
            self::logToSystem($userId, "Profile updated: " . implode(", ", $changes));
            NotificationManager::notifyUser($userId, "Profile Updated", "Your profile information has been updated successfully.");
            
            // Optional: Notify HR if email/phone changed
            if ($current['email'] !== $data['email'] || $current['phone'] !== ($data['phone'] ?? '')) {
                NotificationManager::notifyRole('HR', 'User Profile Change', "User " . $current['username'] . " has updated their contact information.");
            }
        }

        return $result;
    }

    /**
     * Change user password securely
     */
    public static function changePassword($userId, $oldPassword, $newPassword) {
        $db = Database::getInstance();
        
        // Verify old password
        $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($oldPassword, $user['password'])) {
            throw new Exception("Incorrect current password.");
        }

        if (strlen($newPassword) < 6) {
            throw new Exception("New password must be at least 6 characters long.");
        }

        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
        $result = $stmt->execute([$hashed, $userId]);

        if ($result) {
            self::logToSystem($userId, "Password changed successfully.");
            NotificationManager::notifyUser($userId, "Security Alert", "Your password was changed recently. If you didn't do this, please contact IT.");
        }

        return $result;
    }

    /**
     * Handle profile photo upload
     */
    public static function uploadPhoto($userId, $file) {
        $targetDir = __DIR__ . "/../assets/uploads/profiles/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $fileExtension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($fileExtension, $allowedTypes)) {
            throw new Exception("Only JPG, JPEG, PNG & GIF files are allowed.");
        }

        if ($file["size"] > 2000000) { // 2MB
            throw new Exception("File is too large (max 2MB).");
        }

        $fileName = "profile_" . $userId . "_" . time() . "." . $fileExtension;
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($file["tmp_name"], $targetFile)) {
            $db = Database::getInstance();
            
            // Delete old photo if exists
            $current = self::getProfile($userId);
            if (!empty($current['profile_photo']) && file_exists($targetDir . $current['profile_photo'])) {
                unlink($targetDir . $current['profile_photo']);
            }

            $stmt = $db->prepare("UPDATE users SET profile_photo = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$fileName, $userId]);
            
            return $fileName;
        } else {
            throw new Exception("Failed to upload file.");
        }
    }

    private static function logToSystem($userId, $details) {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("INSERT INTO system_logs (user_id, action_type, module, details, created_at) VALUES (?, 'PROFILE_UPDATE', 'USER_PROFILE', ?, NOW())");
            $stmt->execute([$userId, $details]);
        } catch (Exception $e) {
            // Silently fail if log table doesn't exist
        }
    }
}
