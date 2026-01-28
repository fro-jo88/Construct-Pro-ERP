<?php
// includes/HRManager.php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/AuthManager.php';

class HRManager {

    /* --- 1. TENDER MANAGEMENT (Bid Origin) --- */
    
    public static function createTender($data, $user_id) {
        require_once __DIR__ . '/BidManager.php';
        return BidManager::createBid($data, $user_id);
    }

    public static function getTenders() {
        require_once __DIR__ . '/BidManager.php';
        return BidManager::getAllBids();
    }

    public static function submitTenderToGM($tender_id, $user_id) {
        require_once __DIR__ . '/BidManager.php';
        return BidManager::completeTechnical($tender_id, $user_id);
    }

    /* --- 2. PROJECT & SITE INITIALIZATION --- */

    public static function convertTenderToProject($tender_id, $user_id) {
        $db = Database::getInstance();
        $inTransaction = $db->inTransaction();
        if (!$inTransaction) $db->beginTransaction();
        
        try {
            // Check if won
            $stmt = $db->prepare("SELECT * FROM bids WHERE id = ? AND status = 'WON'");
            $stmt->execute([$tender_id]);
            $tender = $stmt->fetch();
            if (!$tender) throw new Exception("Tender must be in 'WON' status to create project.");

            // Create project
            $project_code = "PRJ-" . strtoupper(substr(uniqid(), 7));
            $stmt = $db->prepare("INSERT INTO projects (tender_id, project_code, project_name, status) VALUES (?, ?, ?, 'planned')");
            $stmt->execute([$tender_id, $project_code, $tender['title']]);
            $project_id = $db->lastInsertId();

            self::logAction($user_id, 'initialize_project', "Initialized project $project_code from tender {$tender['tender_no']}");
            
            if (!$inTransaction) $db->commit();
            return $project_id;
        } catch (Exception $e) {
            if (!$inTransaction) $db->rollBack();
            throw $e;
        }
    }

    public static function createSite($data, $user_id) {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO sites (project_id, site_name, location) VALUES (?, ?, ?)");
        $stmt->execute([$data['project_id'], $data['site_name'], $data['location']]);
        $site_id = $db->lastInsertId();
        
        self::logAction($user_id, 'create_site', "Created site {$data['site_name']} for project ID {$data['project_id']}");
        return $site_id;
    }

    /* --- 3. ROLE ASSIGNMENT --- */

    public static function assignSiteStaff($site_id, $assignee_user_id, $role_type, $hr_user_id) {
        $db = Database::getInstance();
        $inTransaction = $db->inTransaction();
        if (!$inTransaction) $db->beginTransaction();
        
        try {
            // Deactivate previous active of same role if exists
            $stmt = $db->prepare("UPDATE site_staff_assignments SET status = 'removed/transferred' WHERE site_id = ? AND role_type = ? AND status = 'active'");
            $stmt->execute([$site_id, $role_type]);

            // Assign new
            $stmt = $db->prepare("INSERT INTO site_staff_assignments (site_id, user_id, role_type, assigned_by) VALUES (?, ?, ?, ?)");
            $stmt->execute([$site_id, $assignee_user_id, $role_type, $hr_user_id]);

            // Update sites table shortcut if needed (foreman_id)
            if ($role_type === 'foreman') {
                $db->prepare("UPDATE sites SET foreman_id = ? WHERE id = ?")->execute([$assignee_user_id, $site_id]);
            }

            self::logAction($hr_user_id, 'assign_site_role', "Assigned $role_type to site ID $site_id");
            
            if (!$inTransaction) $db->commit();
            return true;
        } catch (Exception $e) {
            if (!$inTransaction) $db->rollBack();
            throw $e;
        }
    }

    /* --- 4. EMPLOYEE MANAGEMENT --- */

    public static function createEmployee($data, $hr_user_id) {
        $db = Database::getInstance();
        $inTransaction = $db->inTransaction();
        if (!$inTransaction) $db->beginTransaction();
        
        try {
            // 1. Generate Corporate ID
            $emp_code = self::generateEnterpriseID($db);

            // 2. Create user first (status = pending)
            $hashed = password_hash($data['password'], PASSWORD_BCRYPT);
            $stmt = $db->prepare("INSERT INTO users (username, password, role_id, status) VALUES (?, ?, ?, 'pending')");
            $stmt->execute([$data['username'], $hashed, $data['role_id']]);
            $user_id = $db->lastInsertId();

            // 3. Create employee profile
            $stmt = $db->prepare("INSERT INTO employees (
                user_id, employee_code, full_name, email, phone,
                emergency_name, emergency_phone, emergency_relationship,
                education_level, education_field, education_pdf,
                work_experience_years, employment_type,
                department, position, base_salary, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");

            $stmt->execute([
                $user_id, $emp_code, $data['full_name'], $data['email'], $data['phone'],
                $data['emergency_name'], $data['emergency_phone'], $data['emergency_relationship'],
                $data['education_level'], $data['education_field'], $data['education_pdf'] ?? null,
                $data['work_experience_years'] ?? 0, $data['employment_type'] ?? 'Permanent',
                $data['department'], $data['position'], $data['base_salary'] ?? 0
            ]);
            $employee_id = $db->lastInsertId();

            self::logAction($hr_user_id, 'create_employee', "Created profile for $emp_code ($data[full_name]), awaiting GM approval.");
            
            if (!$inTransaction) $db->commit();
            return $employee_id;
        } catch (Exception $e) {
            if (!$inTransaction) $db->rollBack();
            throw $e;
        }
    }

    private static function generateEnterpriseID($db) {
        $year = date('Y');
        $stmt = $db->query("SELECT COUNT(*) FROM employees WHERE employee_code LIKE 'CP-EMP-$year-%'");
        $count = $stmt->fetchColumn() + 1;
        return "CP-EMP-$year-" . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    public static function getAllEmployees() {
        $db = Database::getInstance();
        return $db->query("SELECT e.*, u.username, u.status as user_status FROM employees e JOIN users u ON e.user_id = u.id ORDER BY e.id DESC")->fetchAll();
    }

    public static function getEmployeeById($id) {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT e.*, u.username, u.role_id, r.role_name 
                            FROM employees e 
                            JOIN users u ON e.user_id = u.id 
                            JOIN roles r ON u.role_id = r.id
                            WHERE e.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function getEmployeeDocuments($emp_id) {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM employee_documents WHERE employee_id = ?");
        $stmt->execute([$emp_id]);
        return $stmt->fetchAll();
    }

    public static function approveEmployee($emp_id, $gm_user_id) {
        $db = Database::getInstance();
        $inTransaction = $db->inTransaction();
        if (!$inTransaction) $db->beginTransaction();
        
        try {
            // 1. Get user and code
            $stmt = $db->prepare("SELECT user_id, employee_code FROM employees WHERE id = ?");
            $stmt->execute([$emp_id]);
            $emp = $stmt->fetch();

            // 2. Approve & Activate profile
            $stmt = $db->prepare("UPDATE employees SET status = 'active', gm_approval_status = 'approved', approved_by = ?, approved_at = NOW() WHERE id = ?");
            $stmt->execute([$gm_user_id, $emp_id]);

            // 3. Activate user account (Allow Login)
            $stmt = $db->prepare("UPDATE users SET status = 'active' WHERE id = ?");
            $stmt->execute([$emp['user_id']]);

            self::logAction($gm_user_id, 'approve_employee', "GM activated profile for $emp[employee_code]. Login enabled.");
            
            if (!$inTransaction) $db->commit();
            return true;
        } catch (Exception $e) {
            if (!$inTransaction) $db->rollBack();
            throw $e;
        }
    }

    public static function rejectEmployee($emp_id, $gm_user_id, $reason) {
        $db = Database::getInstance();
        $inTransaction = $db->inTransaction();
        if (!$inTransaction) $db->beginTransaction();
        
        try {
            $stmt = $db->prepare("SELECT employee_code FROM employees WHERE id = ?");
            $stmt->execute([$emp_id]);
            $code = $stmt->fetchColumn();

            $stmt = $db->prepare("UPDATE employees SET status = 'rejected', gm_approval_status = 'rejected' WHERE id = ?");
            $stmt->execute([$emp_id]);

            self::logAction($gm_user_id, 'reject_employee', "GM rejected profile for $code. Reason: $reason");
            
            if (!$inTransaction) $db->commit();
            return true;
        } catch (Exception $e) {
            if (!$inTransaction) $db->rollBack();
            throw $e;
        }
    }

    /* --- 5. ATTENDANCE MANAGEMENT --- */

    public static function getSiteAttendance($site_id, $date) {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT sa.*, e.first_name, e.last_name FROM site_attendance sa JOIN employees e ON sa.employee_id = e.id WHERE sa.site_id = ? AND sa.work_date = ?");
        $stmt->execute([$site_id, $date]);
        return $stmt->fetchAll();
    }

    public static function reviewAttendance($attendance_id, $hr_user_id) {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE site_attendance SET hr_reviewed_by = ? WHERE id = ?");
        return $stmt->execute([$hr_user_id, $attendance_id]);
    }

    /* --- 6. RECRUITMENT --- */

    public static function createJobRequest($data, $user_id) {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO job_requests (title, department, description, requirements, created_by) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$data['title'], $data['department'], $data['description'], $data['requirements'], $user_id]);
    }

    public static function getJobRequests() {
        $db = Database::getInstance();
        return $db->query("SELECT * FROM job_requests ORDER BY created_at DESC")->fetchAll();
    }

    public static function getApplicants($job_id = null) {
        $db = Database::getInstance();
        if ($job_id) {
            $stmt = $db->prepare("SELECT a.*, jr.title as job_title FROM applicants a JOIN job_requests jr ON a.job_id = jr.id WHERE a.job_id = ? ORDER BY a.applied_at DESC");
            $stmt->execute([$job_id]);
            return $stmt->fetchAll();
        }
        return $db->query("SELECT a.*, jr.title as job_title FROM applicants a LEFT JOIN job_requests jr ON a.job_id = jr.id ORDER BY a.applied_at DESC")->fetchAll();
    }

    public static function addApplicant($data) {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO applicants (job_id, first_name, last_name, email, phone, status) VALUES (?, ?, ?, ?, ?, 'applied')");
        return $stmt->execute([$data['job_id'], $data['first_name'], $data['last_name'], $data['email'], $data['phone']]);
    }

    public static function hireApplicant($applicant_id, $hr_user_id) {
        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            // Get applicant details
            $stmt = $db->prepare("SELECT * FROM applicants WHERE id = ?");
            $stmt->execute([$applicant_id]);
            $applicant = $stmt->fetch();

            if (!$applicant) throw new Exception("Applicant not found");

            // Update status
            $stmt = $db->prepare("UPDATE applicants SET status = 'hired' WHERE id = ?");
            $stmt->execute([$applicant_id]);

            // Create temporary draft employee (can be refined later with full details)
            $username = strtolower($applicant['first_name'] . '.' . $applicant['last_name'] . rand(10, 99));
            $password = "Welcome123!"; // Default
            
            self::createEmployee([
                'username' => $username,
                'password' => $password,
                'role_id' => 2, // Default role
                'first_name' => $applicant['first_name'],
                'last_name' => $applicant['last_name'],
                'department' => 'Operations',
                'position' => 'Staff',
                'salary_type' => 'monthly',
                'base_salary' => 0
            ], $hr_user_id);

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /* --- 7. PAYROLL --- */

    public static function generatePayrollBatch($month_year, $hr_user_id) {
        $db = Database::getInstance();
        $inTransaction = $db->inTransaction();
        if (!$inTransaction) $db->beginTransaction();
        
        try {
            // Get all active employees.
            $employees = $db->query("SELECT * FROM employees WHERE status = 'active'")->fetchAll();
            foreach ($employees as $emp) {
                // Check if already generated
                $check = $db->prepare("SELECT id FROM payroll WHERE employee_id = ? AND month_year = ?");
                $check->execute([$emp['id'], $month_year]);
                if ($check->fetch()) continue;

                // Simple logic: Calculate attendance-based net pay
                // For demo: count total site attendance entries for this month
                $stmtAtt = $db->prepare("SELECT COUNT(*) FROM site_attendance WHERE employee_id = ? AND status = 'present' AND DATE_FORMAT(work_date, '%Y-%m') = ?");
                $stmtAtt->execute([$emp['id'], $month_year]);
                $presence_days = $stmtAtt->fetchColumn();
                
                $net = $emp['base_salary'];
                // Example: If daily wage, multiply base by presence
                if ($emp['salary_type'] === 'daily') {
                    $net = $emp['base_salary'] * $presence_days;
                }

                $stmt = $db->prepare("INSERT INTO payroll (employee_id, month_year, base_salary, net_pay, status, generated_by) VALUES (?, ?, ?, ?, 'draft', ?)");
                $stmt->execute([$emp['id'], $month_year, $emp['base_salary'], $net, $hr_user_id]);
            }
            
            if (!$inTransaction) $db->commit();
            return true;
        } catch (Exception $e) {
            if (!$inTransaction) $db->rollBack();
            throw $e;
        }
    }

    /* --- 8. MESSAGING --- */

    public static function postAnnouncement($title, $content, $target, $user_id) {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO hr_announcements (title, content, target_group, created_by) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$title, $content, $target, $user_id]);
    }

    public static function getAnnouncements() {
        $db = Database::getInstance();
        return $db->query("SELECT a.*, u.username FROM hr_announcements a JOIN users u ON a.created_by = u.id ORDER BY a.created_at DESC LIMIT 10")->fetchAll();
    }

    /* --- 9. LEAVE MANAGEMENT --- */

    public static function getPendingLeaveRequests() {
        $db = Database::getInstance();
        return $db->query("SELECT l.*, e.first_name, e.last_name FROM leave_requests l JOIN employees e ON l.employee_id = e.id WHERE l.status = 'pending'")->fetchAll();
    }

    public static function approveLeave($leave_id, $hr_user_id) {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE leave_requests SET status = 'approved', approved_by = ? WHERE id = ?");
        return $stmt->execute([$hr_user_id, $leave_id]);
    }

    /* --- 10. MATERIAL FORWARDING --- */

    public static function getMaterialRequestsToReview() {
        $db = Database::getInstance();
        return $db->query("SELECT mr.*, s.site_name, u.username as requester 
                           FROM material_requests mr 
                           JOIN sites s ON mr.site_id = s.id 
                           JOIN users u ON mr.requested_by = u.id 
                           WHERE mr.hr_review_status = 'pending'")->fetchAll();
    }

    public static function forwardMaterialRequest($request_id, $hr_user_id) {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE material_requests SET hr_review_status = 'validated', hr_reviewed_by = ?, store_forwarded_at = NOW() WHERE id = ?");
        return $stmt->execute([$hr_user_id, $request_id]);
    }

    /* --- UTILS --- */

    public static function getDashboardStats() {
        $db = Database::getInstance();
        return [
            'total_employees' => $db->query("SELECT COUNT(*) FROM employees WHERE status='active'")->fetchColumn(),
            'active_tenders' => $db->query("SELECT COUNT(*) FROM bids WHERE status NOT IN ('WON', 'LOSS')")->fetchColumn(),
            'pending_leaves' => $db->query("SELECT COUNT(*) FROM leave_requests WHERE status='pending'")->fetchColumn(),
            'pending_materials' => $db->query("SELECT COUNT(*) FROM material_requests WHERE hr_review_status='pending'")->fetchColumn(),
            'attendance_today' => $db->query("SELECT COUNT(*) FROM site_attendance WHERE work_date = CURDATE()")->fetchColumn()
        ];
    }

    public static function getSites() {
        $db = Database::getInstance();
        return $db->query("SELECT s.*, p.project_name FROM sites s JOIN projects p ON s.project_id = p.id")->fetchAll();
    }

    private static function logAction($user_id, $action, $details) {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO hr_activity_logs (user_id, action_type, details) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $action, $details]);
    }
}
