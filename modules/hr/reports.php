<?php
// modules/hr/reports.php
require_once '../../includes/AuthManager.php';
require_once '../../includes/HRManager.php';

AuthManager::requireRole(['HR_MANAGER', 'GM']);

if (isset($_GET['export'])) {
    $report = $_GET['export'];
    $filename = "report_" . $report . "_" . date('Y-m-d') . ".csv";
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $out = fopen('php://output', 'w');
    
    if ($report === 'employees') {
        fputcsv($out, ['ID', 'Code', 'First Name', 'Last Name', 'Dept', 'Role', 'Status', 'Salary', 'Site']);
        $data = HRManager::getAllEmployees();
        foreach ($data as $row) {
            fputcsv($out, [
                $row['id'], $row['employee_code'], $row['first_name'], $row['last_name'], 
                $row['department'], $row['designation'], $row['status'], 
                $row['base_salary'], $row['current_site_id']
            ]);
        }
    } elseif ($report === 'attendance') {
        // Simple dump of recent attendance
        fputcsv($out, ['Date', 'Employee', 'Status', 'In', 'Out']);
        $db = Database::getInstance();
        $date = date('Y-m-d'); // Today
        $rows = $db->query("SELECT a.*, e.first_name, e.last_name FROM attendance a JOIN employees e ON a.employee_id = e.id ORDER BY work_date DESC LIMIT 100")->fetchAll();
         foreach ($rows as $row) {
            fputcsv($out, [
                $row['work_date'], $row['first_name'] . ' ' . $row['last_name'], 
                $row['status'], $row['clock_in'], $row['clock_out']
            ]);
        }
    }
    
    fclose($out);
    exit;
}
?>

<div class="main-content">
    <div class="page-header">
        <h1>HR Reports</h1>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card glass-panel mb-4">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-users mr-2"></i> Employee Census</h5>
                    <p class="card-text">Full list of all employees, departments, and current status.</p>
                    <a href="index.php?module=hr/reports&export=employees" class="btn btn-outline-info btn-block">Export CSV</a>
                </div>
            </div>
        </div>
         <div class="col-md-6">
            <div class="card glass-panel mb-4">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-clock mr-2"></i> Attendance Log</h5>
                    <p class="card-text">Recent attendance records (Last 100 entries).</p>
                    <a href="index.php?module=hr/reports&export=attendance" class="btn btn-outline-info btn-block">Export CSV</a>
                </div>
            </div>
        </div>
         <div class="col-md-6">
            <div class="card glass-panel mb-4">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-money-check-alt mr-2"></i> Payroll Summary</h5>
                    <p class="card-text">Payroll history and totals.</p>
                    <button class="btn btn-outline-secondary btn-block" disabled>Coming Soon</button>
                </div>
            </div>
        </div>
    </div>
</div>
