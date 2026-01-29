<?php
// modules/profile/edit.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../managers/ProfileManager.php';

if (!AuthManager::isLoggedIn()) {
    AuthManager::safeRedirect("index.php");
}

$userId = $_SESSION['user_id'];
$successMsg = '';
$errorMsg = '';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'update_info':
                    $data = [
                        'full_name' => $_POST['full_name'],
                        'email'     => $_POST['email'],
                        'phone'     => $_POST['phone']
                    ];
                    ProfileManager::updateProfile($userId, $data);
                    $successMsg = "Profile information updated successfully!";
                    break;

                case 'change_password':
                    ProfileManager::changePassword($userId, $_POST['old_password'], $_POST['new_password']);
                    $successMsg = "Password changed successfully!";
                    break;

                case 'upload_photo':
                    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === 0) {
                        ProfileManager::uploadPhoto($userId, $_FILES['profile_photo']);
                        $successMsg = "Profile photo updated successfully!";
                    } else {
                        throw new Exception("Please select a valid image file.");
                    }
                    break;
            }
        }
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
    }
}

$user = ProfileManager::getProfile($userId);
$photoPath = !empty($user['profile_photo']) ? "assets/uploads/profiles/" . $user['profile_photo'] : "assets/img/default-avatar.png";
?>

<div class="profile-module">
    <div class="section-header mb-4">
        <h2><i class="fas fa-user-circle text-gold"></i> My Profile</h2>
        <p class="text-dim">Manage your personal information and account security.</p>
    </div>

    <?php if ($successMsg): ?>
        <div class="alert alert-success glass-card mb-4" style="border-left: 5px solid #00ff64; background: rgba(0, 255, 100, 0.1); color: #00ff64;">
            <i class="fas fa-check-circle me-2"></i> <?= $successMsg ?>
        </div>
    <?php endif; ?>

    <?php if ($errorMsg): ?>
        <div class="alert alert-danger glass-card mb-4" style="border-left: 5px solid #ff4444; background: rgba(255, 68, 68, 0.1); color: #ff4444;">
            <i class="fas fa-exclamation-triangle me-2"></i> <?= $errorMsg ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Profile Sidebar -->
        <div class="col-md-4 mb-4">
            <div class="glass-card text-center h-100">
                <div class="avatar-upload mb-4">
                    <div class="avatar-preview mb-3" style="position: relative; display: inline-block;">
                        <img id="imagePreview" src="<?= $photoPath ?>" alt="Profile" 
                             style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid var(--gold); box-shadow: 0 0 20px rgba(255, 204, 0, 0.2);">
                    </div>
                    <form method="POST" enctype="multipart/form-data" id="photoForm">
                        <input type="hidden" name="action" value="upload_photo">
                        <label for="profile_photo" class="btn-secondary-sm" style="cursor: pointer;">
                            <i class="fas fa-camera me-1"></i> Change Photo
                        </label>
                        <input type="file" name="profile_photo" id="profile_photo" style="display: none;" onchange="previewImage(this)">
                        <button type="submit" id="savePhotoBtn" class="btn-primary-sm mt-2" style="display: none;">Save Photo</button>
                    </form>
                </div>
                
                <h3 class="mb-1"><?= htmlspecialchars($user['full_name'] ?: $user['username']) ?></h3>
                <span class="badge badge-gold mb-3"><?= $user['role_name'] ?></span>
                
                <div class="text-start mt-4">
                    <div class="info-row mb-2">
                        <span class="text-dim"><i class="fas fa-id-badge me-2"></i> Username:</span>
                        <span class="float-end"><?= htmlspecialchars($user['username']) ?></span>
                    </div>
                    <div class="info-row mb-2">
                        <span class="text-dim"><i class="fas fa-shield-alt me-2"></i> Status:</span>
                        <span class="float-end status-badge active"><?= strtoupper($user['status']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="text-dim"><i class="fas fa-calendar-check me-2"></i> Member Since:</span>
                        <span class="float-end"><?= date('M Y', strtotime($user['created_at'])) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Profile Column -->
        <div class="col-md-8">
            <div class="glass-card mb-4">
                <h3 class="card-title mb-4"><i class="fas fa-user-edit me-2"></i> Edit Personal Information</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="update_info">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dim">Full Name</label>
                            <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required
                                   style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 12px;">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dim">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>"
                                   style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 12px;">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-dim">Email Address</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required
                               style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 12px;">
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn-primary-sm px-4 py-2">Save Changes</button>
                    </div>
                </form>
            </div>

            <div class="glass-card">
                <h3 class="card-title mb-4"><i class="fas fa-key me-2"></i> Change Password</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="change_password">
                    <div class="mb-3">
                        <label class="form-label text-dim">Current Password</label>
                        <input type="password" name="old_password" class="form-control" required
                               style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 12px;">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dim">New Password</label>
                            <input type="password" name="new_password" class="form-control" required minlength="6"
                                   style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 12px;">
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label text-dim">Confirm New Password</label>
                            <input type="password" id="confirm_password" class="form-control" required
                                   style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 12px;">
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn-primary-sm px-4 py-2" onclick="return validatePasswords()">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagePreview').src = e.target.result;
            document.getElementById('savePhotoBtn').style.display = 'inline-block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function validatePasswords() {
    var newPass = document.getElementsByName('new_password')[0].value;
    var confirmPass = document.getElementById('confirm_password').value;
    
    if (newPass !== confirmPass) {
        alert("New passwords do not match!");
        return false;
    }
    return true;
}
</script>

<style>
.info-row {
    font-size: 0.9rem;
    padding: 8px 0;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}
.info-row:last-child {
    border-bottom: none;
}
.profile-module .glass-card {
    transition: transform 0.3s ease;
}
.profile-module .glass-card:hover {
    transform: translateY(-5px);
}
</style>
