<?php
// modules/hr/add_employee.php
require_once __DIR__ . '/../../includes/AuthManager.php';
require_once __DIR__ . '/../../includes/HRManager.php';

AuthManager::requireRole('HR_MANAGER');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = $_POST;
        $data['education_pdf'] = null;

        // Secure PDF Upload Handler
        if (isset($_FILES['education_pdf']) && $_FILES['education_pdf']['error'] === 0) {
            $file = $_FILES['education_pdf'];
            $allowed_types = ['application/pdf'];
            $max_size = 5 * 1024 * 1024; // 5MB Configurable

            if (!in_array($file['type'], $allowed_types)) {
                throw new Exception("Security Violation: Only PDF documents are allowed for educational background.");
            }

            if ($file['size'] > $max_size) {
                throw new Exception("File too large. Maximum size is 5MB.");
            }

            $upload_dir = __DIR__ . '/../../uploads/education/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

            $filename = 'EDU_' . time() . '_' . bin2hex(random_bytes(4)) . '.pdf';
            $target_file = $upload_dir . $filename;

            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                $data['education_pdf'] = 'uploads/education/' . $filename;
            } else {
                throw new Exception("System Error: Failed to upload education document.");
            }
        } else {
            throw new Exception("Educational Background Document (PDF) is mandatory.");
        }

        HRManager::createEmployee($data, $_SESSION['user_id']);
        $success = "Employee profile created. System ID Generated. Awaiting GM Approval.";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$db = Database::getInstance();
$roles = $db->query("SELECT * FROM roles")->fetchAll();
$sites = $db->query("SELECT * FROM sites")->fetchAll();
?>

<div class="add-employee-module">
    <div class="section-header mb-4" style="display:flex; justify-content:space-between; align-items:center;">
        <h2 class="text-white"><i class="fas fa-id-card text-gold"></i> Enterprise Profile Creation</h2>
        <a href="main.php?module=hr/employees" class="btn-secondary-sm" style="text-decoration:none;">Back to Directory</a>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success glass-card mb-4" style="color: #00ff64; border-left: 5px solid #00ff64;">
            <i class="fas fa-check-circle"></i> <?= $success ?>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger glass-card mb-4" style="color: #ff4444; border-left: 5px solid #ff4444;">
            <i class="fas fa-shield-alt"></i> <?= $error ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="glass-card" style="padding: 2.5rem;">
        
        <!-- Part 1: Identity & Security -->
        <h4 class="mb-4 text-gold" style="border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 0.5rem;">
            <i class="fas fa-fingerprint"></i> System Identity & ERP Access
        </h4>
        <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 2.5rem;">
            <div class="form-group">
                <label>ERP Username</label>
                <input type="text" name="username" required placeholder="e.g. john.doe" class="modern-input">
            </div>
            <div class="form-group">
                <label>Access Password</label>
                <input type="password" name="password" required placeholder="••••••••" class="modern-input">
            </div>
            <div class="form-group">
                <label>ERP System Role</label>
                <select name="role_id" required class="modern-input">
                    <option value="">Select Role...</option>
                    <?php foreach ($roles as $r): if($r['role_name'] != 'Admin'): ?>
                        <option value="<?= $r['id'] ?>"><?= $r['role_name'] ?></option>
                    <?php endif; endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Part 2: Personal & Professional Content -->
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 3rem;">
            <!-- Left Side -->
            <div>
                <h4 class="mb-4 text-gold" style="border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 0.5rem;">
                    <i class="fas fa-user-tie"></i> Professional Profile
                </h4>
                <div class="form-group mb-4">
                    <label>Full Name (as per Passport/ID)</label>
                    <input type="text" name="full_name" required placeholder="e.g. John Alexander Smith" class="modern-input">
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 1rem;" class="mb-4">
                    <div class="form-group">
                        <label>Corporate email</label>
                        <input type="email" name="email" required placeholder="j.smith@constructpro.com" class="modern-input">
                    </div>
                    <div class="form-group">
                        <label>Primary Phone</label>
                        <input type="text" name="phone" required placeholder="+251 ..." class="modern-input">
                    </div>
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 1rem;" class="mb-4">
                    <div class="form-group">
                        <label>Department</label>
                        <select name="department" required class="modern-input">
                            <option value="Executive Office">Executive Office</option>
                            <option value="Engineering">Engineering</option>
                            <option value="Operations">Operations</option>
                            <option value="Finance">Finance</option>
                            <option value="Procurement">Procurement</option>
                            <option value="HR">HR</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Job Position</label>
                        <input type="text" name="position" required placeholder="e.g. Senior Project Engineer" class="modern-input">
                    </div>
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 1rem;" class="mb-4">
                    <div class="form-group">
                        <label>Employment Type</label>
                        <select name="employment_type" required class="modern-input">
                            <option value="Permanent">Permanent</option>
                            <option value="Contract">Contract</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Relevant Experience (Years)</label>
                        <input type="number" name="work_experience_years" min="0" required class="modern-input">
                    </div>
                </div>
                <div class="form-group">
                    <label>Assigned Project Site (Optional)</label>
                    <select name="current_site_id" class="modern-input">
                        <option value="">Headquarters / No Site</option>
                        <?php foreach($sites as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= $s['site_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Right Side -->
            <div>
                <h4 class="mb-4 text-gold" style="border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 0.5rem;">
                    <i class="fas fa-graduation-cap"></i> Academic & Emergency Info
                </h4>
                
                <div class="p-3 mb-4" style="background: rgba(255,255,255,0.02); border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 1rem;" class="mb-3">
                        <div class="form-group">
                            <label>Education Level</label>
                            <input type="text" name="education_level" required placeholder="e.g. B.Sc in Civil Eng." class="modern-input">
                        </div>
                        <div class="form-group">
                            <label>Major / Field</label>
                            <input type="text" name="education_field" required placeholder="e.g. Civil Engineering" class="modern-input">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Education Credentials (PDF Only)</label>
                        <div class="file-drop-card" onclick="document.getElementById('edu_pdf').click()">
                            <i class="fas fa-cloud-upload-alt fa-2x mb-2" style="color: var(--gold);"></i>
                            <div id="file_label">Click to upload verified PDF</div>
                            <input type="file" name="education_pdf" id="edu_pdf" accept="application/pdf" style="display:none;" onchange="document.getElementById('file_label').innerText = this.files[0].name" required>
                        </div>
                    </div>
                </div>

                <div class="p-3" style="background: rgba(255,204,0,0.02); border-radius: 12px; border: 1px solid rgba(255,204,0,0.1);">
                    <h5 class="text-gold mb-3"><i class="fas fa-ambulance"></i> Emergency Contact</h5>
                    <div class="form-group mb-3">
                        <label>Contact Person Name</label>
                        <input type="text" name="emergency_name" required class="modern-input">
                    </div>
                    <div style="display:grid; grid-template-columns: 1.5fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>Emergency Phone</label>
                            <input type="text" name="emergency_phone" required class="modern-input">
                        </div>
                        <div class="form-group">
                            <label>Relationship</label>
                            <input type="text" name="emergency_relationship" required placeholder="e.g. Spouse" class="modern-input">
                        </div>
                    </div>
                </div>

                <div class="form-group mt-4">
                    <label>Starting Base Salary Amount</label>
                    <input type="number" name="base_salary" step="0.01" required placeholder="0.00" class="modern-input" style="font-size: 1.25rem; color: var(--gold);">
                </div>
            </div>
        </div>

        <div style="margin-top:3rem; display:flex; justify-content:flex-end; gap:1.5rem; border-top:1px solid rgba(255,255,255,0.05); padding-top:2rem;">
            <button type="reset" class="btn-secondary-sm">Reset Profile</button>
            <button type="submit" class="btn-primary-sm" style="padding:1.2rem 3rem; font-size: 1.1rem; border-radius: 12px; box-shadow: 0 10px 20px rgba(0,0,0,0.3);">
                <i class="fas fa-paper-plane mr-2"></i> Submit for GM Review
            </button>
        </div>
    </form>
</div>

<style>
.modern-input {
    width: 100%;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 10px;
    color: white;
    padding: 0.9rem;
    font-size: 0.95rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.modern-input:focus {
    border-color: var(--gold);
    background: rgba(255,204,0,0.02);
    box-shadow: 0 0 15px rgba(255,204,0,0.1);
    outline: none;
}

.modern-input option {
    background: #1a1a1a; /* Corporate Dark */
    color: white;
}

.file-drop-card {
    border: 2px dashed rgba(255,204,0,0.2);
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    background: rgba(255,255,255,0.01);
}

.file-drop-card:hover {
    border-color: var(--gold);
    background: rgba(255,204,0,0.05);
}

label {
    display: block;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-dim);
    margin-bottom: 0.6rem;
}

.alert i { margin-right: 10px; }
</style>
